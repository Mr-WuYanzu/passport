<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    //用户注册
    public function reg(){
            $str=file_get_contents('php://input');
            $data=json_decode($str,true);
            $data['password']=encrypt($data['password']);
            $res=DB::table('user')->where('email',$data['email'])->first();
            if($res){
                $response=[
                    'errno'=>'41001',
                    'msg'=>'邮箱 已经注册',
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);die;
            }else{
                $data=DB::table('user')->insertGetId($data);
                if($data){
                    $response=[
                        'errno'=>'0',
                        'msg'=>'注册成功',
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                    die;
                }else{
                    $response=[
                        'errno'=>'40010',
                        'msg'=>'注册失败',
                    ];
                    return json_encode($response,JSON_UNESCAPED_UNICODE);die;
                }
            }
    }
    //用户登录
    public function login(){
        $str=file_get_contents('php://input');
        $data=json_decode($str,true);
        $res=DB::table('user')->where('email',$data['email'])->first();
        if($res){
            if(decrypt($res->password)!=$data['password']){
                $response=[
                    'errno'=>'42002',
                    'msg'=>'密码错误'
                ];
                return json_encode($response,JSON_UNESCAPED_UNICODE);die;
            }else{
                $token=substr(md5($res->id.time()),5,15);
                $key='user:id:'.$res->id;
                $response=[
                    'errno'=>'0',
                    'msg'=>'登录成功',
                    'uid'=>$res->id,
                    'token'=>$token
                ];
                Redis::set($key,$token);
                Redis::expire($key,60*60*24);
                return json_encode($response,JSON_UNESCAPED_UNICODE);die;
            }
        }else{
            $response=[
                'errno'=>'42001',
                'msg'=>'账号不存在'
            ];
            return json_encode($response,JSON_UNESCAPED_UNICODE);die;
        }

    }
}
