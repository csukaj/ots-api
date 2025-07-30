<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */

/**
 * Public
 */
Route::group(
    [
        'middleware' => ['cors', 'setLanguage']
    ],
    function () {
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

        # Ezek a hivasok csak a fejlesztes megkonnyitese erdekeben vannak, eles
        # kornyezetben tilos oket elerhetove tenni! @ivan @20180607
        # @todo @ivan majd egy idore ki kell kommentezni a teljes blokkot, es megnezni hogy mukodik e teljes folyamat nelkule!
        if (\App::environment() != 'production')
        {
            Route::post('/payment/details', 'PaymentController@details');
            Route::post('/payment/charge', 'PaymentController@charge');

            Route::post('/billing/create','BillingController@create');
        }

    }
);

/**
 * Administrators only
 */
Route::group(
    [
        'middleware' => ['cors', 'jwt.auth', 'role:admin|advisor'],
        'namespace' => 'Admin',
        'prefix' => 'admin'
    ],
    function () {
        Route::resource('user', 'UserController');
        Route::get('user-list', 'UserController@getUserList');

        Route::resource('email', 'EmailController');
        Route::resource('location', 'LocationController');
        Route::resource('hotel-chain', 'HotelChainController');
        Route::resource('ship-company', 'ShipCompanyController');
        Route::resource('ship', 'ShipController');

        Route::resource('cart', 'CartController');

        Route::resource('organization-classification', 'OrganizationClassificationController');
        Route::resource('date-range', 'DateRangeController');
        Route::post('date-range/update-collection', 'DateRangeController@updateCollection');
        Route::resource('organization-prices', 'OrganizationPricesController');
        Route::resource('organization-group-prices', 'OrganizationGroupPricesController');
        Route::resource('cruise-prices', 'CruisePricesController');
        Route::resource('age-range', 'AgeRangeController');

        Route::resource('org-group-classification', 'OrganizationGroupClassificationController');

        Route::resource('device-usage', 'DeviceUsageController');
        Route::resource('device-classification', 'DeviceClassificationController');

        Route::resource('product', 'ProductController');
        Route::resource('price', 'PriceController');
        Route::resource('price-element', 'PriceElementController');
        Route::post('price-element/update-collection', 'PriceElementController@updateCollection');
        Route::resource('price-modifier', 'PriceModifierController');
        Route::resource('price-modifier-combinations', 'PriceModifierCombinationController');

        Route::get('/content/posts', 'ContentController@posts');
        Route::get('/content/pages', 'ContentController@pages');
        Route::get('/content/posts-of-category/{id}', 'ContentController@postsOfCategory');
        Route::resource('content', 'ContentController');

        Route::resource('contentmedia', 'ContentMediaController');
        Route::post('contentmedia/upload', 'ContentMediaController@upload');

        Route::get('translation/download/{iso_code}', 'TranslationController@download');
        Route::post('translation/import', 'TranslationController@import');
        Route::post('price/import', 'PriceImportController@import');

        Route::get('device-minimum-nights', 'DeviceMinimumNightsController@index');
        Route::post('device-minimum-nights', 'DeviceMinimumNightsController@store');

        Route::resource('program', 'ProgramController');
        Route::put('program-relation/sequence', 'ProgramRelationController@saveSequence');
        Route::resource('program-relation', 'ProgramRelationController');
        Route::resource('program-classification', 'ProgramClassificationController');
        Route::resource('program-fee', 'ProgramFeeController');

        Route::resource('ship-group', 'ShipGroupController');
        Route::resource('organization-group-poi', 'OrganizationGroupPoiController');

        Route::resource('cruise', 'CruiseController');
        Route::resource('cruise-classification', 'CruiseClassificationController');

        Route::resource('schedule', 'ScheduleController');

        Route::resource('log', 'AdminLogController', ['only' => ['show']]);

        Route::resource('supplier', 'SupplierController');

        Route::get('billing/getpdf/{id}','BillingController@billinggetpdf');
        Route::resource('review', 'ReviewController');

        // Route::get('updateimages', function () { \Illuminate\Support\Facades\Artisan::call('command:regenerateimages', []); });
    }
);

/**
 * Extranet users only
 */
Route::group(
    [
        'middleware' => ['cors', 'jwt.auth', 'role:admin|manager|advisor'],
        'namespace' => 'Extranet',
        'prefix' => 'extranet'
    ],
    function () {
        Route::resource('accommodation', 'AccommodationController', ['only' => ['index', 'show']]);
        Route::resource('ship-group', 'ShipGroupController', ['only' => ['index']]);
        Route::resource('device', 'DeviceController', ['only' => ['index']]);
        Route::resource('availability', 'AvailabilityController', ['only' => ['index', 'store']]);
        Route::resource('user-settings', 'UserSettingsController', ['only' => ['index', 'store', 'update']]);

        Route::post('accommodation-search', 'AccommodationSearchController@index');
        Route::get('accommodation-search/searchable-texts', 'AccommodationSearchController@searchableTexts');
        Route::get('accommodation-search/search-options', 'AccommodationSearchController@searchOptions');

    }
);

/**
 * Administrators and managers - actions must be manually gated!
 */
Route::group(
    [
        'middleware' => ['cors', 'jwt.auth', 'role:admin|manager|advisor'],
        'namespace' => 'Gated',
        'prefix' => 'admin'
    ],
    function () {
        Route::get('accommodation/overview/{id}', 'AccommodationController@overview');
        Route::resource('accommodation', 'AccommodationController');
        Route::get('get_device_names', 'DeviceController@getDeviceNames');
        Route::get('device/channel-manager-ids', 'DeviceController@getChannelManagerIds');
        Route::resource('device', 'DeviceController');
        Route::resource('order', 'OrderController');
        Route::post('order/set-status', 'OrderController@setStatus');
    }
);
