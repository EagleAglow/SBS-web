<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class BidderMiddleware
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

        //If user is a bidder (has permission because bidding role has permission), proceed
        if (Auth::user()->hasPermissionTo('bid-self')){
            return $next($request);
        } else {
        //If user does not have this role, abort
        abort('401');
        }
/* 
        //If user has a bidder role, proceed
        if (Auth::user()->hasAnyRole('bid-for-demo','bid-for-irpa','bid-for-tsu','bid-for-oidp','bid-for-tcom','bid-for-tnon')){
            return $next($request);
        } else {
        //If user does not have this role, abort
        abort('401');
        }
 */
    }
}
