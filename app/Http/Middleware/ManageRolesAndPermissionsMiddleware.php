<?php
// From: https://scotch.io/tutorials/user-authorization-in-laravel-54-with-spatie-laravel-permission

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class ManageRolesAndPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $user = User::all()->count();
        // if there is only one user in system, this check is bypassed
        if (!($user == 1)) {
            //If user does not have this permission, abort
            if (!Auth::user()->hasPermissionTo('role-permission-manage')) 
            {
                abort('401');
            }
        }

        return $next($request);
    }
}
    