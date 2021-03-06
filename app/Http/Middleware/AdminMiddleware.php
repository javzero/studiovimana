<?php

namespace App\Http\Middleware;
use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'user')
    {
        if(!auth()->guard($guard)->check()){
            return redirect('/vadmin/login');
        }
        if(auth()->guard($guard)->user()->status == 0){
            return redirect('/vadmin/login')->with('message','Su usuario está inactivo');
        };
        return $next($request);
    }
}
