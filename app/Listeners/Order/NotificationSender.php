<?php

namespace App\Listeners\Order;

use App\Events\Order\BaseOrderStatusEvent;
use App\Events\Order\ClosedStatusEvent;
use App\Events\Order\ConfirmedStatusEvent;
use App\Events\Order\NewUniqueProductOrderStatusEvent;
use App\Events\Order\PaymentFailedStatusEvent;
use App\Events\Order\PaymentSuccessStatusEvent;
use App\Events\Order\WaitingForOfferStatusEvent;
use App\Facades\Config;
use App\Mail\AdminNewOfferReceivedMail;
use App\Mail\AdminPaymentFailedMail;
use App\Mail\AdminPaymentSuccessMail;
use App\Mail\AdvisorNewOfferReceivedMail;
use App\Mail\UserBackToCartMail;
use App\Mail\UserBackToUniqueProductsCartMail;
use App\Mail\UserOfferRequestConfirmationMail;
use App\Mail\UserPaymentFailedMail;
use App\Mail\UserPaymentSuccessMail;
use App\Mail\UserProcessFinishedMail;
use App\Order;
use App\Services\OrderStatusLogger;
use App\User;
use Illuminate\Support\Facades\Mail;

class  NotificationSender
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PaymentFailedStatusEvent $event
     * @return void
     * @throws \Exception
     */
    public function handle(BaseOrderStatusEvent $event)
    {
        $request = isset($event->request) ? $event->request : $event;
        $site = is_array($request) ? $request['site'] : $request->site;
        $siteLanguage = $this->getSiteLanguage($site ?: '');

        switch (get_class($event)) {
            case WaitingForOfferStatusEvent::class:
                $config = (object)[
                    'username' => $request['first_name'] . " " . $request['last_name']
                ];
                Mail::to($request['email'])->send(new UserOfferRequestConfirmationMail($config, $siteLanguage));

                $order = $request; /////TODO!!! - must be prettier


                $siteAdvisors = User::withRole('advisor')->forSite($site)->get();
                if ($siteAdvisors) {
                    $config = (object)[
                        'username' => null,
                        'adminOrderDetailsLink' => $this->getAdminOrderDetailsLink($order)
                    ];
                    foreach ($siteAdvisors as $advisor) {
                        $config->username = $advisor->name;
                        Mail::to($advisor->email)->send(new AdvisorNewOfferReceivedMail($config, $order, $siteLanguage));
                    }
                }

                $admins = User::withRole('admin')->get();
                if ($admins) {
                    $config = (object)[
                        'username' => null,
                        'adminOrderDetailsLink' => $this->getAdminOrderDetailsLink($order)
                    ];
                    foreach ($admins as $admin) {
                        $config->username = $admin->name;
                        Mail::to($admin->email)->send(new AdminNewOfferReceivedMail($config, $order, $siteLanguage));
                    }
                }

                $events = [
                    '`Offer request confirmation` mail sent to user',
                    '`New offer received` mail sent to advisor'
                ];
                break;

            case ConfirmedStatusEvent::class:
                $config = (object)[
                    'username' => $request['first_name'] . " " . $request['last_name'],
                    'link' => $this->getBackToCartLink($this->generateNewToken($request), $request['site'])
                ];
                Mail::to($request['email'])->send(new UserBackToCartMail($config, $siteLanguage));

                $events = [
                    '`Back to Cart` mail sent to user'
                ];
                break;

            case NewUniqueProductOrderStatusEvent::class:
                $site = $request['site'];

                if (!empty($request['company_name']))
                {
                    $name = $request['company_name'];
                }
                else
                {
                    $name = $request['first_name'] . " " . $request['last_name'];
                }

                $mail = new UserBackToUniqueProductsCartMail((object)[
                        'username' => $name,
                        'link' => $this->getBackToUniqueProductCartLink($this->generateNewToken($request), $site)
                    ],
                    $this->getSiteLanguage($site)
                );
                Mail::to($request['email'])->send($mail);

                $events = [
                    '`Back to Cart` mail sent to user'
                ];
                break;

            case ClosedStatusEvent::class:
                $config = (object)[
                    'username' => $request['first_name'] . " " . $request['last_name']
                ];
                Mail::to($request['email'])->send(new UserProcessFinishedMail($config, $siteLanguage));

                $events = [
                    '`Process finished` mail sent to user'
                ];
                break;

            case PaymentSuccessStatusEvent::class:
                $order = Order::find($request['id']);

                Mail::to($request['email'])->send(new UserPaymentSuccessMail($order));

                $admins = User::withRole('admin')->get();
                if ($admins) {
                    $config = (object)[
                        'username' => null,
                        'adminOrderDetailsLink' => $this->getAdminOrderDetailsLink($order)
                    ];
                    foreach ($admins as $admin) {
                        $config->username = $admin->name;
                        Mail::to($admin->email)->send(new AdminPaymentSuccessMail($config, $order));
                    }
                }

                $events = [
                    '`Payment success` mail sent to user'
                ];
                break;

            case PaymentFailedStatusEvent::class:
                $order = Order::find($request['id']);

                Mail::to($request['email'])->send(new UserPaymentFailedMail($order));

                $admins = User::withRole('admin')->get();
                if ($admins) {
                    $config = (object)[
                        'username' => null,
                        'adminOrderDetailsLink' => $this->getAdminOrderDetailsLink($order)
                    ];
                    foreach ($admins as $admin) {
                        $config->username = $admin->name;
                        Mail::to($admin->email)->send(new AdminPaymentFailedMail($config, $order));
                    }
                }

                $events = [
                    '`Payment failed` mail sent to user'
                ];
                break;
        }

        (new OrderStatusLogger((object)['id' => $request['id']]))->addLog([
            'date' => date('Y-m-d H:i:s'),
            'events' => $events
        ]);

    }

    private function getSiteLanguage($site)
    {
        $siteLanguages = Config::get('ots.site_languages');
        return isset($siteLanguages[$site]) ? $siteLanguages[$site] : 'en';
    }

    /**
     * @param $token
     * @param $site
     * @return string
     * @throws \Exception
     */
    private function getBackToCartLink(string $token, string $site): string
    {
        $language = $this->getSiteLanguage($site);
        $uris = Config::getOrFail('order.backToCartLinks');

        return "http://{$site}/{$uris[$language]}/{$token}";
    }

    /**
     * @param string $token
     * @param string $site
     * @return string
     * @throws \Exception
     */
    private function getBackToUniqueProductCartLink(string $token, string $site): string
    {
        $language = $this->getSiteLanguage($site);
        $uris = Config::getOrFail('order.backToUniqueProductCartLinks');

        return "http://{$site}/{$uris[$language]}/{$token}";
    }

    /**
     * @param Order $order
     * @return string
     * @throws \Exception
     */
    private function getAdminOrderDetailsLink(Order $order): string
    {
        return env('ADMIN_URL') . "/order/{$order->id}";
    }

    private function generateNewToken($request)
    {
        $tokenString = $request['id'] . str_random(40) . $request['created_at'] . date('YmdHis');
        $token = sha1(base64_encode($tokenString));
        Order::setToken($request['id'],$token);
        return $token;
    }
}
