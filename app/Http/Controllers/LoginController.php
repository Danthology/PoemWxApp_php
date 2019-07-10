<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\facades\Response;
use Illuminate\Support\facades\Input;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cookie;
use DB;
use Cache;
use App\Http\Controllers\KeyController;

class LoginController extends Controller
{
    public function login()
	{
		date_default_timezone_set("Asia/Shanghai");
		$code=request()->input('code');
		$nickName=request()->input('nickName');
		$avatarUrl=request()->input('avatarUrl');
		//获取请求
		if(!$nickName||!$avatarUrl)
		{
			$dan["statusCode"]=300;
			return response()->json($dan);
		}
		$getkey=new KeyController;
		$key=$getkey->getkey();
		$url="https://api.weixin.qq.com/sns/jscode2session?appid=".$key["appId"]."&secret=".$key["appSecret"]."&js_code=".$code."&grant_type=authorization_code";
		$contents = file_get_contents($url);
		$wxreturn=json_decode($contents);
		//请求微信获取openid session_key
		$trd_session=md5($wxreturn->openid.$wxreturn->session_key);
		$value["openid"]=$wxreturn->openid;
		$value["session_key"]=$wxreturn->session_key;
		$value=json_encode($value);
		Redis::setex($trd_session, 7200, $value);
		//redis操作
		if($wxreturn->openid)
		{
			$ob=DB::table('wx_account')->select('code')->where('openid',$wxreturn->openid)->first();
			if($ob)
			{
				if($ob->code)
				{
					$dan["statusCode"]=200;
					$dan["skey"]=$trd_session;
					return response()->json($dan);
				}
				else
				{
					$dan["statusCode"]=202;
					$dan["skey"]=$trd_session;
					return response()->json($dan);
				}
			}
			else
			{
				DB::table('wx_account')->insert(['openid'=>$wxreturn->openid,'name'=>$nickName,'imgUrl'=>$avatarUrl]);
				$dan["statusCode"]=202;
				$dan["skey"]=$trd_session;
				return response()->json($dan);
			}
		}
		else
		{
			$dan["statusCode"]=500;
			return response()->json($dan);
		}
	}
	public function person()
	{
		date_default_timezone_set("Asia/Shanghai");
		$skey=Cookie::get('skey');
		$redis=json_decode(Redis::get($skey));
		$userCode=request()->input('userCode');
		$userEmail=request()->input('userEmail');
		$userName=request()->input('userName');
		if(!$userCode||!$userEmail||!$userName)
		{
			$dan["statusCode"]=300;
			return response()->json($dan);
		}
		DB::table('wx_account')->where('openid',$redis->openid)->update(['code'=>$userCode,'email'=>$userEmail,'real_name'=>$userName]);
		$dan["statusCode"]=200;
		$dan["msg"]="提交成功";
		return response()->json($dan);
	}
}
