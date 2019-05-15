<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class Request10times
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
        $key='request10times:ip:'.$_SERVER['REMOTE_ADDR'].':token:'.$request->input('token');
        $num=Redis::get($key);
        if($num>10){
            die('访问频繁');
        }
        Redis::incr($key);
        Redis::expire($key,2);
        return $next($request);
    }
}
