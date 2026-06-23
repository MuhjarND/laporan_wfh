<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsoLoginController extends Controller
{
    public function redirect()
    {
        $this->ensureConfigured();

        $state = Str::random(40);
        session(['sso_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect_uri'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect()->away($this->ssoUrl('/oauth/authorize') . '?' . $query);
    }

    public function callback(Request $request)
    {
        if (!$request->filled('code')) {
            abort(401, 'Login SSO gagal: parameter code tidak tersedia.');
        }

        $expectedState = $request->session()->pull('sso_oauth_state');

        if (!$expectedState || !$request->filled('state') || !hash_equals($expectedState, $request->input('state'))) {
            abort(401, 'Login SSO gagal: state OAuth tidak valid.');
        }

        $token = $this->requestAccessToken($request->input('code'));
        $ssoUser = $this->requestSsoUser($token);
        $user = $this->syncLocalUser($ssoUser);

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put([
            'sso_access_token' => $token,
            'sso_roles' => $this->arrayValue($ssoUser, 'roles'),
            'sso_permissions' => $this->arrayValue($ssoUser, 'permissions'),
        ]);

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        $token = $request->session()->get('sso_access_token');

        if ($token) {
            try {
                Http::withToken($token)->post($this->ssoUrl('/api/logout'));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Auth::logout();

        $request->session()->forget(['sso_access_token', 'sso_roles', 'sso_permissions', 'sso_oauth_state']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Berhasil logout dari aplikasi.');
    }

    private function requestAccessToken($code)
    {
        $this->ensureConfigured();

        try {
            $response = Http::asForm()->post($this->ssoUrl('/oauth/token'), [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.sso.client_id'),
                'client_secret' => config('services.sso.client_secret'),
                'redirect_uri' => config('services.sso.redirect_uri'),
                'code' => $code,
            ]);
        } catch (\Throwable $e) {
            report($e);
            abort(401, 'Login SSO gagal: tidak dapat menghubungi server token SSO.');
        }

        if (!$response->successful()) {
            abort(401, 'Login SSO gagal: server SSO menolak permintaan token.');
        }

        $accessToken = $response->json('access_token');

        if (!$accessToken) {
            abort(401, 'Login SSO gagal: response token SSO tidak memiliki access_token.');
        }

        return $accessToken;
    }

    private function requestSsoUser($token)
    {
        try {
            $response = Http::withToken($token)->get($this->ssoUrl('/api/user'));
        } catch (\Throwable $e) {
            report($e);
            abort(401, 'Login SSO gagal: tidak dapat mengambil data user SSO.');
        }

        if (!$response->successful()) {
            abort(401, 'Login SSO gagal: server SSO gagal mengembalikan data user.');
        }

        $user = $response->json();

        if (!is_array($user)) {
            abort(401, 'Login SSO gagal: format data user SSO tidak valid.');
        }

        if (empty($user['email'])) {
            abort(401, 'Login SSO gagal: akun SSO tidak memiliki email.');
        }

        return $user;
    }

    private function syncLocalUser(array $ssoUser)
    {
        $email = $ssoUser['email'];
        $ssoId = isset($ssoUser['id']) && is_numeric($ssoUser['id']) ? (int) $ssoUser['id'] : null;

        $userBySsoId = $ssoId ? User::where('sso_id', $ssoId)->first() : null;
        $userByEmail = User::where('email', $email)->first();

        if ($userBySsoId && $userByEmail && $userBySsoId->id !== $userByEmail->id) {
            abort(401, 'Login SSO gagal: email SSO sudah terhubung ke akun lokal lain.');
        }

        $user = $userBySsoId ?: $userByEmail ?: new User();
        $isNewUser = !$user->exists;

        $user->fill([
            'name' => $ssoUser['name'] ?? $email,
            'email' => $email,
            'sso_id' => $ssoId,
        ]);

        if ($isNewUser) {
            $user->nip = $this->makeUniqueSsoNip($ssoId, $email);
            $user->password = Hash::make(Str::random(40));
            $user->role = $this->localRoleFromSso($this->arrayValue($ssoUser, 'roles'));
            $user->is_active = true;
        }

        $user->save();

        return $user;
    }

    private function localRoleFromSso(array $roles)
    {
        $allowedRoles = ['super_admin', 'atasan', 'pegawai'];

        foreach ($roles as $role) {
            if (in_array($role, $allowedRoles, true)) {
                return $role;
            }
        }

        return 'pegawai';
    }

    private function makeUniqueSsoNip($ssoId, $email)
    {
        $base = $ssoId ? 'SSO-' . $ssoId : 'SSO-' . Str::upper(Str::before($email, '@'));
        $candidate = $base;
        $suffix = 1;

        while (User::where('nip', $candidate)->exists()) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function arrayValue(array $data, $key)
    {
        return isset($data[$key]) && is_array($data[$key]) ? $data[$key] : [];
    }

    private function ssoUrl($path)
    {
        $baseUrl = rtrim((string) config('services.sso.base_url'), '/');

        if (!Str::startsWith($baseUrl, 'https://')) {
            abort(500, 'Konfigurasi SSO_BASE_URL harus menggunakan HTTPS.');
        }

        return $baseUrl . $path;
    }

    private function ensureConfigured()
    {
        $requiredKeys = ['base_url', 'client_id', 'client_secret', 'redirect_uri'];

        foreach ($requiredKeys as $key) {
            if (!config('services.sso.' . $key)) {
                abort(500, 'Konfigurasi SSO belum lengkap: services.sso.' . $key);
            }
        }
    }
}
