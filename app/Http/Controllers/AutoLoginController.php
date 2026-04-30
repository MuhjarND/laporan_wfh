<?php

namespace App\Http\Controllers;

use App\Services\ChatbotGatewayService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoLoginController extends Controller
{
    public function __invoke(Request $request, ChatbotGatewayService $gateway)
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return $this->errorResponse();
        }

        $validation = $gateway->validateMagicToken($token);

        if (empty($validation['valid']) || empty($validation['app_user_id'])) {
            return $this->errorResponse();
        }

        $user = User::where('app_user_id', (string) $validation['app_user_id'])->first();

        if (!$user || !$user->is_active) {
            return $this->errorResponse();
        }

        $guard = config('auth.defaults.guard', 'web');
        Auth::guard($guard)->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    private function errorResponse()
    {
        return response()
            ->view('auth.autologin-error', [
                'message' => config('chatbot.autologin_error_message'),
            ], 401);
    }
}
