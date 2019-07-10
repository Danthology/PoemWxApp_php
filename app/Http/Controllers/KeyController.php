<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\facades\Response;
use Illuminate\Support\facades\Input;
use Illuminate\Support\Facades\Redis;
use DB;
use Cache;

class KeyController extends Controller
{
    public function getkey()
	{
		$dan["appId"]="";
		$dan["appSecret"]="";
		return $dan;
	}
}
