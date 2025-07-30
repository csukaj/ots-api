<?php

Route::group(['middleware' => 'cors', 'prefix' => 'stylersauth', 'namespace' => 'Modules\Stylersauth\Http\Controllers'], function() {
    
    Route::any('/authenticate', 'StylersauthController@authenticate');
    
});

Route::group(['middleware' => ['cors', 'jwt.auth'], 'prefix' => 'stylersauth', 'namespace' => 'Modules\Stylersauth\Http\Controllers'], function() {
    
    Route::any('/user', [
        'uses' => 'StylersauthController@user'
    ]);
    
    Route::any('/logout', [
        'uses' => 'StylersauthController@logout'
    ]);
    
});

Route::group(['middleware' => ['cors', 'jwt.auth', 'role:admin'], 'prefix' => 'stylersauth', 'namespace' => 'Modules\Stylersauth\Http\Controllers'], function() {
    
    Route::any('/admin', function() {
        return ['message' => 'Hello admin.'];
    });
    
});