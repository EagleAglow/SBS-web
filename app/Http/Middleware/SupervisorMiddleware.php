<?php
// From: https://scotch.io/tutorials/user-authorization-in-laravel-54-with-spatie-laravel-permission

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class SupervisorMiddleware
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
        //If user does not have this role, abort
        if (!Auth::user()->hasRole('supervisor')){
            abort('401');
        }
        return $next($request);
    }
}
