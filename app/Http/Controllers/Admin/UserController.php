<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(15);
        $atasanList = User::where('role', 'atasan')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'atasanList'));
    }

    public function create()
    {
        $atasanList = User::where('role', 'atasan')->where('is_active', true)->orderBy('name')->get();
        return view('admin.users.create', compact('atasanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:super_admin,atasan,pegawai',
            'pangkat' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'satuan_kerja' => 'nullable|string|max:255',
            'atasan_id' => 'nullable|exists:users,id',
        ]);

        User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'pangkat' => $request->pangkat,
            'jabatan' => $request->jabatan,
            'satuan_kerja' => $request->satuan_kerja,
            'atasan_id' => $request->atasan_id,
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $atasanList = User::where('role', 'atasan')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();
        return view('admin.users.edit', compact('user', 'atasanList'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:super_admin,atasan,pegawai',
            'pangkat' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'satuan_kerja' => 'nullable|string|max:255',
            'atasan_id' => 'nullable|exists:users,id',
        ]);

        $data = $request->only(['name', 'nip', 'email', 'phone', 'role', 'pangkat', 'jabatan', 'satuan_kerja', 'atasan_id']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Status user berhasil diperbarui.');
    }

    public function sendCredential(User $user, WhatsAppNotificationService $whatsApp)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Akun super admin tidak dapat dikirim melalui fitur ini.');
        }

        $plainPassword = $user->nip;
        $user->update([
            'password' => Hash::make($plainPassword),
        ]);

        if (!$user->phone) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User belum memiliki nomor WhatsApp.');
        }

        if (!$whatsApp->sendAccountCredential($user, $plainPassword)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal mengirim akun ke WhatsApp user.');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun berhasil dikirim ke ' . $user->name . '. Password disetel menjadi NIP user.');
    }

    public function sendCredentials(WhatsAppNotificationService $whatsApp)
    {
        $users = User::where('role', '!=', 'super_admin')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;

        foreach ($users as $user) {
            $plainPassword = $user->nip;
            $user->update([
                'password' => Hash::make($plainPassword),
            ]);

            if (!$user->phone) {
                $withoutPhone++;
                continue;
            }

            if ($whatsApp->sendAccountCredential($user, $plainPassword)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        $message = 'Akun berhasil diproses. Terkirim: ' . $sent . ', tanpa nomor WA: ' . $withoutPhone . ', gagal kirim: ' . $failed . '. Password user non-superadmin telah disetel menjadi NIP masing-masing.';

        return redirect()->route('admin.users.index')->with('success', $message);
    }
}
