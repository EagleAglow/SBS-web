<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class ScheduleEditMiddleware
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
        //If user does not have this permission, abort
        if (!Auth::user()->hasPermissionTo('schedule-edit')) 
        {
            abort('401');
        }

        return $next($request);
    }
}
    