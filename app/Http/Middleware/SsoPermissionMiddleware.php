<?php

namespace App\Http\Middleware;

use Closure;

class SsoPermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (!$request->session()->has('sso_access_token')) {
            return $next($request);
        }

        $permissions = $request->session()->get('sso_permissions', []);

        if (!is_array($permissions) || !in_array($permission, $permissions, true)) {
            abort(403, 'Akses ditolak. Permission SSO tidak tersedia: ' . $permission);
        }

        return $next($request);
    }
}
