<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\facades\Response;
use Illuminate\Support\facades\Input;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cookie;
use DB;
use Cache;

class MSGController extends Controller
{
    public function wxGetUserInfo()
	  {
		date_default_timezone_set("Asia/Shanghai");
		$skey=Cookie::get('skey');
		if(!Redis::get($skey))
		{
			$dan["statusCode"]=201;
			$dan["msg"]="skey过期";
			return response()->json($dan);
		}
		$redis=json_decode(Redis::get($skey));
		$find=DB::table('wx_account')->select('code','email','real_name','num','userQuesCnt')->where('openid',$redis->openid)->first();
        $dan["userCode"]=$find->code;
		$dan["userEmail"]=$find->email;
		$dan["userName"]=$find->real_name;
		$dan["userScore"]=$find->num;
		$dan["userQuesCnt"]=$find->userQuesCnt;
		$dan["statusCode"]=200;
		return response()->json($dan);
	  }
	public function wxGetRankList()
	{
		date_default_timezone_set("Asia/Shanghai");
		$skey=Cookie::get('skey');
		if(!Redis::get($skey))
		{
			$dan["statusCode"]=201;
			$dan["msg"]="skey过期";
			return response()->json($dan);
		}
		$redis=json_decode(Redis::get($skey));
		$find=DB::table('wx_account')->select('real_name','num','imgUrl')->orderBy('num', 'desc')->skip(0)->take(10)->get()->toArray();
		$count=0;
		$comment=Array(Array());
		foreach($find as $ob)
		{				
			$comment[$count]["wxUserName"]=$ob->real_name;
			$comment[$count]["wxAvatar"]=$ob->imgUrl;
			$comment[$count]["userScore"]=$ob->num;
            $count++;
		}
		$dan["rankData"]=$comment;
		$find2=DB::table('wx_account')->select('real_name','num','imgUrl')->where('openid',$redis->openid)->first();
		$dan["selfData"]["wxUserName"]=$find2->real_name;
		$dan["selfData"]["wxAvatar"]=$find2->imgUrl;
		$dan["selfData"]["userScore"]=$find2->num;
		$count=DB::table('wx_account')->where('num','>',$find2->num)->count();
		$dan["selfData"]["rank"]=$count+1;
		$dan["statusCode"]=200;
		return response()->json($dan);
	}
}
