<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\facades\Response;
use Illuminate\Support\facades\Input;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cookie;
use DB;
use Cache;

class PracticeController extends Controller
{
	public function getpoem()//这是一坨屎
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
		$dan["content"]["questionAnswer"]=$value["answer"];
		$dan["statusCode"]=200;
		return response()->json($dan);
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
	
	public function mb_str_split($str)
	{
		return preg_split('/(?<!^)(?!$)/u', $str );
    }
}
