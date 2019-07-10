<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\facades\Response;
use Illuminate\Support\facades\Input;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cookie;
use DB;
use Cache;

class PoemsController extends Controller
{
    public function getpoem()//这是一坨屎
	{
		date_default_timezone_set("Asia/Shanghai");
		if(time()<1558270856)
		{
			$skey=Cookie::get('skey');
			if(!Redis::get($skey))
			{
				$dan["statusCode"]=201;
				$dan["msg"]="skey过期";
				return response()->json($dan);
			}
			$redis=json_decode(Redis::get($skey));
			$time=date("Ymd");
			$QuesCnt_key=md5($redis->openid.$time.'2');
			$QuesCnt=Redis::get($QuesCnt_key);
			if($QuesCnt>=8)
			{
				$dan["statusCode"]=301;
				return response()->json($dan);
			}
			$rand=rand(0,1);
			if($rand==0)
			{
				$find=DB::table('poem')->select('title','content')->inRandomOrder()->skip(0)->take(4)->get()->toArray();
				$count=0;
				$comment=Array(Array());
				foreach($find as $ob)
				{				
					$comment[$count]["title"]=$ob->title;
					$comment[$count]["content"]=$ob->content;
					$Ques=$this->cut($ob->content);
					if(count($Ques)==1)
					{
						$dan["statusCode"]=302;
						return response()->json($dan);
					}
					if($count==0)
					{
						$dan["content"]["questionDescription"]=$Ques[0]."，";
						$questionOption[$count]=$Ques[1];
					}
					else
					{
						$questionOption[$count]=$Ques[1];
					}
					$count++;
				}
				$value["title"]=$comment[0]["title"];
				$value["content"]=$comment[0]["content"];
				$value["answer"]=$questionOption[0];
				$dan["content"]["questionType"]=0;
				shuffle($questionOption);
				$dan["content"]["questionOption"]=$questionOption;
			}
			else
			{
				$find=DB::table('poem')->select('title','content')->inRandomOrder()->first();
				$value["title"]=$find->title;
				$value["content"]=$find->content;
				$Ques=$this->cut($find->content);
				if(count($Ques)==1)
				{
					$dan["statusCode"]=302;
					return response()->json($dan);
				}
				$value["answer"]=$Ques[0];
				$awsl1=$this->mb_str_split($Ques[0]);
				$awsl2=$this->mb_str_split($Ques[1]);
				shuffle($awsl2);
				$long=count($awsl1);
				$dan["content"]["questionsize"]=$long;
				$count=0;
				for($i=$long;$i<9;$i++)
				{
					$awsl1[$i]=$awsl2[$count];
					$count++;
				}
				shuffle($awsl1);
				$dan["content"]["questionContent"]=$awsl1;
				$dan["content"]["questionType"]=1;
			}
			if($QuesCnt)
			{
				$QuesNo=$QuesCnt+1;
			}
			else
			{
				$QuesNo=1;
			}
			Redis::setex($QuesCnt_key, 720000, $QuesNo);
			$value=json_encode($value);
			$questionSessId=md5($redis->openid.$time.$QuesNo.'2');
			Redis::setex($questionSessId, 720000, $value);
			$dan["content"]["questionSessId"]=$questionSessId;
			$dan["statusCode"]=200;
			$cnt=DB::table('wx_account')->select('userQuesCnt')->where('openid',$redis->openid)->first();
			$cnt_num=$cnt->userQuesCnt+1;
			DB::table('wx_account')->where('openid',$redis->openid)->update(['userQuesCnt'=>$cnt_num]);
			return response()->json($dan);
		}
		else
		{
			$dan["statusCode"]=301;
			return response()->json($dan);
		}
    }
	
	public function cut($content)//将屎分割
	{
		$content=str_ireplace("？","。",$content);
		$content=str_ireplace("！","。",$content);
		$arr=explode("。",$content);
		array_pop($arr);
		shuffle($arr);
		$Ques=explode("，",$arr[0],2);
		return $Ques;
	}
	public function answer()
	{
		date_default_timezone_set("Asia/Shanghai");
		$questionSessId=request()->input('questionSessId');
		$answer=request()->input('answer');
		$skey=Cookie::get('skey');
		$redis=json_decode(Redis::get($skey));
		$find=DB::table('wx_account')->select('num2')->where('openid',$redis->openid)->first();
		$num=$find->num2;
		$question=json_decode(Redis::get($questionSessId));
		if($answer==$question->answer)
		{
			$newnum=$num+10;
			DB::table('wx_account')->where('openid',$redis->openid)->update(['num2'=>$newnum]);
			$dan["answerCode"]=1;
			$dan["msg"]="答案正确";
		}
		else
		{
			$dan["answerCode"]=0;
			$dan["msg"]="答案错误";
		}
        $question->answer=md5(rand(0,99)*rand(0,99));
		Redis::setex($questionSessId, 720000, json_encode($question));
		$dan["statusCode"]=200;
		return response()->json($dan);
	}
	public function review()
	{
		date_default_timezone_set("Asia/Shanghai");
		$skey=Cookie::get('skey');
		$redis=json_decode(Redis::get($skey));
		$time=date("Ymd");
		$QuesCnt_key=md5($redis->openid.$time);
		$QuesCnt=Redis::get($QuesCnt_key);
		$dan["statusCode"]=200;
		if(!$QuesCnt)
		{
			return response()->json($dan);
		}
		for($i=1;$i<=$QuesCnt;$i++)
		{
			$questionSessId=md5($redis->openid.$time.$i);
			$question=json_decode(Redis::get($questionSessId));
			$dan["content"][$i-1]["poemBody"]=$question->content;
			$title_all=$question->title;
			$find2=preg_replace("/\\d+/",'', $title_all);
			$arr=explode("：",$find2);
			$dan["content"][$i-1]["poemAuthor"]=$arr[0];
			$dan["content"][$i-1]["poemTitle"]=$arr[1];
		}
		return response()->json($dan);
	}
	public function mb_str_split($str)
	{
		return preg_split('/(?<!^)(?!$)/u', $str );
    }
}
