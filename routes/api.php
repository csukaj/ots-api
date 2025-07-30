<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Public API Routes
 */
Route::middleware(['cors', 'setLanguage'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('/log', 'LogController@log');
    Route::get('/age-ranges', 'AgeRangeController@index');

    Route::post('/accommodation-search', 'Search\AccommodationSearchController@index');
    Route::get('/accommodation-search/searchable-texts', 'Search\AccommodationSearchController@searchableTexts');
    Route::get('/accommodation-search/search-options', 'Search\AccommodationSearchController@searchOptions');

    Route::post('/charter-search', 'Search\CharterSearchController@index');
    Route::get('/charter-search/search-options', 'Search\CharterSearchController@searchOptions');

    Route::post('/cruise-search', 'Search\CruiseSearchController@index');
    Route::get('/cruise-search/search-options', 'Search\CruiseSearchController@searchOptions');

    Route::post('/cart/update', 'CartController@update');
    Route::post('/order', 'OrderController@send');
    Route::get('/order/{token}', 'OrderController@getByToken');
    Route::post('/contact', 'ContactController@send');

    Route::get('/content/posts', 'ContentController@posts');
    Route::get('/content/pages', 'ContentController@pages');
    Route::get('/content/posts-of-category/{id}', 'ContentController@postsOfCategory')->where('id', '[0-9]+');
    Route::get('/content/{id}', 'ContentController@show')->where('id', '[0-9]+');
    Route::post('/content/by-url', 'ContentController@showByUrl');

    Route::post('/payment/create', 'PaymentController@create');
    Route::post('/payment/status', 'PaymentController@status');
    Route::post('/payment/pay', 'PaymentController@pay');
    Route::get('/payment/notification', 'PaymentController@notification');

    // Development only routes
    if (!app()->environment('production')) {
        Route::post('/payment/details', 'PaymentController@details');
        Route::post('/payment/charge', 'PaymentController@charge');
        Route::post('/billing/create','BillingController@create');
    }
});

/**
 * Administrators only
 */
Route::middleware(['cors', 'jwt.auth', 'role:admin|advisor'])
    ->namespace('Admin')
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('user', 'UserController');
        Route::get('user-list', 'UserController@getUserList');

        Route::apiResource('email', 'EmailController');
        Route::apiResource('location', 'LocationController');
        Route::apiResource('hotel-chain', 'HotelChainController');
        Route::apiResource('ship-company', 'ShipCompanyController');
        Route::apiResource('ship', 'ShipController');

        Route::apiResource('cart', 'CartController');

        Route::apiResource('organization-classification', 'OrganizationClassificationController');
        Route::apiResource('date-range', 'DateRangeController');
        Route::post('date-range/update-collection', 'DateRangeController@updateCollection');
        Route::apiResource('organization-prices', 'OrganizationPricesController');
        Route::apiResource('organization-group-prices', 'OrganizationGroupPricesController');
        Route::apiResource('cruise-prices', 'CruisePricesController');
        Route::apiResource('age-range', 'AgeRangeController');

        Route::apiResource('org-group-classification', 'OrganizationGroupClassificationController');

        Route::apiResource('device-usage', 'DeviceUsageController');
        Route::apiResource('device-classification', 'DeviceClassificationController');

        Route::apiResource('product', 'ProductController');
        Route::apiResource('price', 'PriceController');
        Route::apiResource('price-element', 'PriceElementController');
        Route::post('price-element/update-collection', 'PriceElementController@updateCollection');
        Route::apiResource('price-modifier', 'PriceModifierController');
        Route::apiResource('price-modifier-combinations', 'PriceModifierCombinationController');

        Route::get('/content/posts', 'ContentController@posts');
        Route::get('/content/pages', 'ContentController@pages');
        Route::get('/content/posts-of-category/{id}', 'ContentController@postsOfCategory');
        Route::apiResource('content', 'ContentController');

        Route::apiResource('contentmedia', 'ContentMediaController');
        Route::post('contentmedia/upload', 'ContentMediaController@upload');

        Route::get('translation/download/{iso_code}', 'TranslationController@download');
        Route::post('translation/import', 'TranslationController@import');
        Route::post('price/import', 'PriceImportController@import');

        Route::get('device-minimum-nights', 'DeviceMinimumNightsController@index');
        Route::post('device-minimum-nights', 'DeviceMinimumNightsController@store');

        Route::apiResource('program', 'ProgramController');
        Route::put('program-relation/sequence', 'ProgramRelationController@saveSequence');
        Route::apiResource('program-relation', 'ProgramRelationController');
        Route::apiResource('program-classification', 'ProgramClassificationController');
        Route::apiResource('program-fee', 'ProgramFeeController');

        Route::apiResource('ship-group', 'ShipGroupController');
        Route::apiResource('organization-group-poi', 'OrganizationGroupPoiController');

        Route::apiResource('cruise', 'CruiseController');
        Route::apiResource('cruise-classification', 'CruiseClassificationController');

        Route::apiResource('schedule', 'ScheduleController');

        Route::apiResource('log', 'AdminLogController', ['only' => ['show']]);

        Route::apiResource('supplier', 'SupplierController');

        Route::get('billing/getpdf/{id}','BillingController@billinggetpdf');
        Route::apiResource('review', 'ReviewController');
    });

/**
 * Extranet users only
 */
Route::middleware(['cors', 'jwt.auth', 'role:admin|manager|advisor'])
    ->namespace('Extranet')
    ->prefix('extranet')
    ->group(function () {
        Route::apiResource('accommodation', 'AccommodationController', ['only' => ['index', 'show']]);
        Route::apiResource('ship-group', 'ShipGroupController', ['only' => ['index']]);
        Route::apiResource('device', 'DeviceController', ['only' => ['index']]);
        Route::apiResource('availability', 'AvailabilityController', ['only' => ['index', 'store']]);
        Route::apiResource('user-settings', 'UserSettingsController', ['only' => ['index', 'store', 'update']]);

        Route::post('accommodation-search', 'AccommodationSearchController@index');
        Route::get('accommodation-search/searchable-texts', 'AccommodationSearchController@searchableTexts');
        Route::get('accommodation-search/search-options', 'AccommodationSearchController@searchOptions');
    });

/**
 * Administrators and managers - actions must be manually gated!
 */
Route::middleware(['cors', 'jwt.auth', 'role:admin|manager|advisor'])
    ->namespace('Gated')
    ->prefix('admin')
    ->group(function () {
        Route::get('accommodation/overview/{id}', 'AccommodationController@overview');
        Route::apiResource('accommodation', 'AccommodationController');
        Route::get('get_device_names', 'DeviceController@getDeviceNames');
        Route::get('device/channel-manager-ids', 'DeviceController@getChannelManagerIds');
        Route::apiResource('device', 'DeviceController');
        Route::apiResource('order', 'OrderController');
        Route::post('order/set-status', 'OrderController@setStatus');
    });