<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class BidBySupervisorMiddleware
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

        //If user has active bidder role, proceed
        if (Auth::user()->hasRole('supervisor')){
            return $next($request);
        } else {
        //If user does not have this role, abort
        abort('401');
        }
    }
}
