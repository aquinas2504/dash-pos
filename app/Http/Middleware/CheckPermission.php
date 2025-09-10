<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->main_role === 'supermanager') {
            return $next($request);
        }

        if (!$user->hasPermission($permission)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
