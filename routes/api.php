<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::get('test','RedisController@test');
Route::get('wxLogin','LoginController@login')->middleware('wxcheck:login');
Route::get('wxSubmitUserInfo','LoginController@person')->middleware('wxcheck:keep');
Route::get('wxGetUserInfo','MsgController@wxGetUserInfo')->middleware('wxcheck:keep');
Route::get('wxGetRankList','MsgController@wxGetRankList')->middleware('wxcheck:keep');
Route::get('wxGetQuestions','PoemController@getpoem')->middleware('wxcheck:keep');
Route::get('wxSubmitAnswer','PoemController@answer')->middleware('wxcheck:keep');
Route::get('wxGetDailyReview','PoemController@review')->middleware('wxcheck:keep');
Route::get('wxGetPractice','PracticeController@getpoem')->middleware('wxcheck:keep');
Route::get('ganshi','HighController@test');
Route::get('ganbu','HighController@test2');