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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/register', 'AuthController@register');

Route::post('auth/login', 'AuthController@login');
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('auth/user', 'AuthController@user');
    Route::post('auth/logout', 'AuthController@logout');
    Route::resource('user', 'UserController');
    Route::post('user/group_delete', 'UserController@groupDelete');
    Route::resource('roles', 'RoleController');
    Route::post('roles/group_delete', 'RoleController@groupDelete');
    Route::resource('permissions', 'PermissionController');
    Route::post('permissions/group_delete', 'PermissionController@groupDelete');
});

Route::group(['middleware' => 'jwt.refresh'], function () {
    Route::get('auth/refresh', 'AuthController@refresh');
});
