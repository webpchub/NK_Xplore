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

/*
 |--------------------------------------------------------------------------
 | Authentication Routes
 |--------------------------------------------------------------------------
*/
Route::post('v1/login', 'UserController@login');
Route::post('v1/register', 'UserController@register');
Route::patch('v1/verify/{activation_code}','UserController@verify');
Route::post('v1/forgot_password','UserController@forgotPassword');
Route::post('v1/reset_password/{token}','UserController@resetPassword');
Route::post('v1/change_password',['middleware' => 'auth:api','uses' => 'UserController@changePassword']);
Route::get('v1/profile',['middleware' => 'auth:api','uses' => 'UserController@profile']);

