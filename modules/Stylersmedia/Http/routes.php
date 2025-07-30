<?php

Route::group(['middleware' => 'web', 'prefix' => 'stylersmedia', 'namespace' => 'Modules\Stylersmedia\Http\Controllers'], function()
{
    Route::get('/', 'StylersmediaController@index');
});

Route::group(['middleware' => ['cors', 'jwt.auth', 'role:admin'], 'prefix' => 'stylersmedia', 'namespace' => 'Modules\Stylersmedia\Http\Controllers'], function() {
    Route::get('gallery/get-options', 'GalleryController@getOptions');
    Route::resource('gallery', 'GalleryController');
    Route::post('gallery/update-priority/{id}', 'GalleryController@updatePriority');
    Route::resource('gallery-item', 'GalleryItemController');
    Route::post('gallery-item/upload', 'GalleryItemController@upload');
});