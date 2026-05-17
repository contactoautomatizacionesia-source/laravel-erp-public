<?php

namespace Modules\Affiliate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AffiliatePanelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if(auth()->check()){
            if(auth()->user()->role->type == 'superadmin'){
                return $next($request);
            }
            if(auth()->user()->affiliate_request ==1 && auth()->user()->accept_affiliate_request ==1){
                return $next($request);
            }
            abort(404);
        }
        else{
            abort(404);
        }

    }
}
