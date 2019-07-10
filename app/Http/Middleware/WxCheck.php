<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cookie;
/*
200 正常
201 skey过期
202 第一次登录
300 参数错误
301 答题次数满
302 诗体切割出错 重新请求
500 错误
*/
class WxCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
		if($role=='login')
		{
			return $next($request);
		}
		else if($role=='keep')
		{
		   $skey=Cookie::get('skey');
		   if(!Redis::get($skey)||!$skey)
		   {
			   $dan["statusCode"]=201;
			   return response()->json($dan);
			   return $next($request);
		   }
		   else
		   {
			   return $next($request);
		   }
		}
    }
}
