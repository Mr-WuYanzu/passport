<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CheckLogin
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
        $token=$request->input('token');
        $id=$request->input('id');
        if(empty($token) || empty($id)){
            $response=[
                'errno'=>'20001',
                'msg'=>'参数不完整'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        $key='user:token'.$id;
        $local_token=Redis::get($key);
        if($local_token){
            if($token==$local_token){
                //TODO 记录日志
                $data=date('Y-m-d h:i:s').'id为'.$id.'的用户登录成功'."\n";
                file_put_contents('logs/login.log',$data,FILE_APPEND);
            }else{
                //用户token错误
                $response=[
                    'errno'=>'20003',
                    'msg'=>'用户token错误'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'errno'=>'20002',
                'msg'=>'请先登录'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        return $next($request);
    }
}
