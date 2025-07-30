<?php

namespace App\Providers;

use App\Accommodation;
use App\Observers\AccommodationObserver;
use App\Observers\OrganizationObserver;
use App\Organization;
use App\Providers\ChannelManager\ServiceProvider as ChannelManagerService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

use App\Order;
use App\Services\Payment\Service as PaymentService;
use App\Services\Billing\Service as BillingService;
use App\Services\UniqueProduct\Service as UniqueProductService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Accommodation::observe(AccommodationObserver::class);
        Organization::observe(OrganizationObserver::class);

        Validator::extend('PhoneNumber', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[0-9+-\.\s\(\)\/]+$/", $value);
        });

        $this->app->bind('payment', function ($app)
        {
            $order = app()->make(Order::class);
            $paymentConfig = config('payment');

            return new PaymentService($order, $paymentConfig);
        });

        $this->app->bind('channel_manager', function ($app) {
            return new ChannelManagerService($app);
        });

        $this->app->bind('billing', function ($app)
        {
            $order = app()->make(Order::class);
            $config = config('billing');

            return new BillingService($order, $config);
        });

        $this->app->bind('unique_product', function ()
        {
            $cart = app()->make(\App\Cart::class);

            return new UniqueProductService($cart);
        });

        $this->app->bind('currency_logger', function ()
        {
            $logger = new \Monolog\Logger('currency-update');
            $streamHandler = new \Monolog\Handler\StreamHandler(storage_path('logs/currency-update.log'));
            $logger->pushHandler($streamHandler);

            return $logger;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
