<?php

use Illuminate\Http\Request;
use App\Http\Controllers\SocialMedia;

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

Route::group([
    'prefix' => 'v1',
    'middleware' => 'api',
    'namespace' => 'SocialMedia'] ,function(){

        Route::post("/login","AccountController@login");
        Route::post("/register","AccountController@register");
        Route::post("/accountconfirmation","AccountController@accountConfirmation");
        Route::post("/verification","AccountController@verification");
        Route::post("/passwordreset","AccountController@passwordReset");
        Route::post("/me","AccountController@me");
        Route::post("/mycertificate","AccountController@myCertificate");
        Route::post("/myregion","AccountController@myRegion");
        Route::post("/logout","AccountController@logout");

        Route::post("/certificate","ProfileController@getCertificate");
        Route::post("/region","ProfileController@getRegion");
        
        Route::post("/edit","ProfileController@edit");
        Route::post("/deletepictureprofile","ProfileController@deletePictureProfile");
        Route::post("/deletepicturewall","ProfileController@deletePictureWall");
        
        Route::post("/createpost","ProfileController@createPost");
        
        Route::post("/posts","ProfileController@getPosts")->middleware('numeric');
        Route::post("/notifications","ProfileController@getNotifications")->middleware('numeric');
        Route::post("/frinds","Controller@getFrinds");
        Route::post("/orderfrinds","ProfileController@getOrderFrinds")->middleware('numeric');
        Route::post("/sentorders","ProfileController@getSentOrders")->middleware('numeric');

        Route::group([
            'middleware' => 'is_post',] ,function(){
                Route::post("/editpost","ProfileController@editPost");
                Route::post("/deletefile","ProfileController@deleteContentFile");
                Route::post("/deletepost","ProfileController@deletePost");

                Route::post("/createcomment","HomeController@createComment");
                Route::post("/comments","HomeController@getComments")->middleware('numeric');
                
                Route::post("/createlike","HomeController@createLike");
            });
        
        Route::post("/isfrind","Controller@isFrind");

        Route::group([
            'middleware' => 'is_comment',] ,function(){
                Route::post("/editcomment","HomeController@editComment");
                Route::post("/deletecomment","HomeController@deleteComment");
                Route::post("/replies","HomeController@getReplies")->middleware('numeric');
                Route::post("/user","HomeController@getUser");
            });

        Route::post("/managingfrind","HomeController@managingFrind");
        Route::post("/getuser","HomeController@get_user");
            
        Route::group([
            'middleware' => 'is_frind',] ,function(){
                Route::post("/acceptorderfrind","HomeController@acceptOrderFrind");
                Route::post("/deleteorderfrind","HomeController@deleteOrderFrind");
            });
            
        Route::post("/mainposts","HomeController@mainPosts");    
        
        Route::post("/suggestionfrinds","HomeController@suggestionFrinds");
    });
