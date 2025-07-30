<?php

Route::group(['middleware' => ['cors', 'jwt.auth', 'role:admin'], 'prefix' => 'stylerscontact', 'namespace' => 'Modules\Stylerscontact\Http\Controllers'], function() {
    Route::resource('/contact', 'ContactController');
    Route::resource('/person', 'PersonController');
});