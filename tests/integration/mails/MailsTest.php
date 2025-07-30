<?php

namespace Tests\Integration\Mails;

use App\Email;
use App\Entities\EmailEntity;
use App\Mail\AdminNewOfferReceivedMail;
use App\Mail\AdminPaymentFailedMail;
use App\Mail\AdminPaymentSuccessMail;
use App\Mail\AdvisorNewOfferReceivedMail;
use App\Mail\OrderSent;
use App\Mail\UserBackToCartMail;
use App\Mail\UserBackToUniqueProductsCartMail;
use App\Mail\UserOfferRequestConfirmationMail;
use App\Mail\UserPaymentFailedMail;
use App\Mail\UserPaymentSuccessMail;
use App\Mail\UserProcessFinishedMail;
use App\Payment;
use Illuminate\Support\Facades\Mail;
use Tests\OrderTestTrait;
use Tests\TestCase;

class MailsTest extends TestCase
{

    use OrderTestTrait;

    static public $setupMode = self::SETUPMODE_ONCE;

    protected function assertMailablePropertyEquals($expected, $actual, string $field): bool
    {
        $this->assertEquals($expected, $actual,
            //"Failed asserting that content of '$field' matches the expected ". \json_encode($expected). "\nActual value: ". \json_encode($actual));
            "Failed asserting that content of '$field' matches the expected.");
        return true;
    }

    /**
     * @param $mail
     * @param $this
     * @param $settings
     * @param $siteLanguage
     * @return bool
     */
    protected function assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage): bool
    {
        $mail->build();

        $hasFrom = $this->assertMailablePropertyEquals($mail->from, [['name' => env('OTS_MAIL_NAME'), 'address' => env('OTS_MAIL_FROM_ADDRESS')]], 'From');
        $hasTo = $mail->hasTo($toEmail);
        $this->assertTrue($hasTo, 'Failed asserting that To address is correct');
        $hasView = $this->assertMailablePropertyEquals($mail->view, 'emails.dynamic-template', 'View');
        $emailEntity = (new EmailEntity(Email::findOrFail($mail->templateId)))->getFrontendData();

        $hasSubject = $this->assertMailablePropertyEquals($mail->subject, $emailEntity['subject'][$siteLanguage], 'Subject');
        $hasContent = $this->assertMailablePropertyEquals($mail->viewData['content'], strtr($emailEntity['content'][$siteLanguage], $dict), 'Content');

        return $hasFrom && $hasTo && $hasSubject && $hasView && $hasContent;
    }

    /**
     * @test
     */
    function a_UserOfferRequestConfirmationMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName
        ];
        $dict = [
            '{{username}}' => $config->username
        ];


        foreach (['en', 'hu'] as $siteLanguage) {
            Mail::fake();
            Mail::to($toEmail)->send(new UserOfferRequestConfirmationMail($config, $siteLanguage));
            Mail::assertSent(UserOfferRequestConfirmationMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
                return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
            });
        }
    }

    /**
     * @test
     */
    function a_AdminNewOfferReceivedMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $order = $this->prepareSampleOrder();
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName,
            'adminOrderDetailsLink' => $this->faker->url
        ];
        $dict = [
            '{{username}}' => $config->username,
            '{{adminOrderDetailsLink}}' => $config->adminOrderDetailsLink,
            '{{orderId}}' => $order->id,
            '{{orderCreatedAt}}' => $order->created_at,
            '{{customerName}}' => $order->fullName(),
            '{{customerEmail}}' => $order->email
        ];


        foreach (['en', 'hu'] as $siteLanguage) {
            Mail::fake();
            Mail::to($toEmail)->send(new AdminNewOfferReceivedMail($config, $order, $siteLanguage));
            Mail::assertSent(AdminNewOfferReceivedMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
                return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
            });
        }
    }

    /**
     * @test
     */
    function a_AdminPaymentFailedMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $order = $this->prepareSampleOrder();
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName,
            'adminOrderDetailsLink' => $this->faker->url
        ];
        $dict = [
            '{{username}}' => $config->username,
            '{{adminOrderDetailsLink}}' => $config->adminOrderDetailsLink,
            '{{orderId}}' => $order->id,
            '{{orderCreatedAt}}' => $order->created_at,
            '{{customerName}}' => $order->fullName(),
            '{{customerEmail}}' => $order->email
        ];

        $siteLanguage = $order->language();
        Mail::fake();
        Mail::to($toEmail)->send(new AdminPaymentFailedMail($config, $order));
        Mail::assertSent(AdminPaymentFailedMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
            return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
        });

    }

    /**
     * @test
     */
    function a_AdminPaymentSuccessMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $order = $this->prepareSampleOrder();
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName,
            'adminOrderDetailsLink' => $this->faker->url
        ];
        $dict = [
            '{{username}}' => $config->username,
            '{{adminOrderDetailsLink}}' => $config->adminOrderDetailsLink,
            '{{orderId}}' => $order->id,
            '{{orderCreatedAt}}' => $order->created_at,
            '{{customerName}}' => $order->fullName(),
            '{{customerEmail}}' => $order->email
        ];

        $siteLanguage = $order->language();

        Mail::fake();
        Mail::to($toEmail)->send(new AdminPaymentSuccessMail($config, $order));
        Mail::assertSent(AdminPaymentSuccessMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
            return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
        });

    }

    /**
     * @test
     */
    function a_AdvisorNewOfferReceivedMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $order = $this->prepareSampleOrder();
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName,
            'adminOrderDetailsLink' => $this->faker->url
        ];
        $dict = [
            '{{username}}' => $config->username,
            '{{adminOrderDetailsLink}}' => $config->adminOrderDetailsLink,
            '{{orderId}}' => $order->id,
            '{{orderCreatedAt}}' => $order->created_at,
            '{{customerName}}' => $order->fullName(),
            '{{customerEmail}}' => $order->email
        ];


        foreach (['en', 'hu'] as $siteLanguage) {
            Mail::fake();
            Mail::to($toEmail)->send(new AdvisorNewOfferReceivedMail($config, $order, $siteLanguage));
            Mail::assertSent(AdvisorNewOfferReceivedMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
                return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
            });
        }
    }

    /**
     * @test
     */
    function a_HLSUpdateErrorMail_is_sent_correctly()
    {
        $this->markTestIncomplete('Need to implement');
    }

    /**
     * @test
     */
    function a_OrderSent_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $order = $this->prepareSampleOrder();
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName
        ];
        $dict = [
            '{{username}}' => $config->username,
            'view' => 'emails.userorder'
        ];


        Mail::fake();
        Mail::to($toEmail)->send(new OrderSent($order));
        Mail::assertSent(OrderSent::class, function ($mail) use ($toEmail, $dict) {
            $mail->build();

            $hasFrom = $this->assertMailablePropertyEquals($mail->from, [['name' => env('OTS_MAIL_NAME'), 'address' => env('OTS_MAIL_FROM_ADDRESS')]], 'From');
            $hasTo = $mail->hasTo($toEmail);
            $this->assertTrue($hasTo, 'Failed asserting that To address is correct');
            $hasView = $this->assertMailablePropertyEquals($mail->view, 'emails.userorder', 'View');
            $hasSubject = $this->assertMailablePropertyEquals($mail->subject, 'OTS Order details', 'Subject');
            $hasContent = true; //TODO: need to implement check

            return $hasFrom && $hasTo && $hasSubject && $hasView && $hasContent;
        });

    }


    /**
     * @test
     */
    function a_UserBackToCartMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName,
            'link' => $this->faker->url
        ];
        $dict = [
            '{{username}}' => $config->username,
            '{{link}}' => $config->link
        ];


        foreach (['en', 'hu'] as $siteLanguage) {
            Mail::fake();
            Mail::to($toEmail)->send(new UserBackToCartMail($config, $siteLanguage));
            Mail::assertSent(UserBackToCartMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
                return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
            });
        }
    }

    /**
     * @test
     */
    function a_UserBackToUniqueProductsCartMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName,
            'link' => $this->faker->url
        ];
        $dict = [
            '{{username}}' => $config->username,
            '{{link}}' => $config->link
        ];


        foreach (['en', 'hu'] as $siteLanguage) {
            Mail::fake();
            Mail::to($toEmail)->send(new UserBackToUniqueProductsCartMail($config, $siteLanguage));
            Mail::assertSent(UserBackToUniqueProductsCartMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
                return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
            });
        }
    }

    /**
     * @test
     */
    function a_UserPaymentFailedMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $order = $this->prepareSampleOrder();
        (new Payment([
            'order_id' => $order->id,
            'request_id' => $this->faker->randomNumber,
            'payment_order_id' => $this->faker->numberBetween(100000000000,999999999999)
        ]))->saveOrFail();
        $dict = [
            '{{username}}' => $order->fullName(),
            '{{orderId}}' => $order->id,
            '{{transactionId}}' => $order->payment->payment_order_id
        ];

        $siteLanguage = $order->language();
        Mail::fake();
        Mail::to($toEmail)->send(new UserPaymentFailedMail($order));
        Mail::assertSent(UserPaymentFailedMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
            return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
        });

    }

    /**
     * @test
     */
    function a_UserPaymentSuccessMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $order = $this->prepareSampleOrder();
        (new Payment([
            'order_id' => $order->id,
            'request_id' => $this->faker->randomNumber,
            'payment_order_id' =>$this->faker->numberBetween(100000000000,999999999999)
        ]))->saveOrFail();
        $dict = [
            '{{username}}' => $order->fullName(),
            '{{orderId}}' => $order->id,
            '{{transactionId}}' => $order->payment->payment_order_id
        ];

        $siteLanguage = $order->language();
        Mail::fake();
        Mail::to($toEmail)->send(new UserPaymentSuccessMail($order));
        Mail::assertSent(UserPaymentSuccessMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
            return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
        });

    }

    /**
     * @test
     */
    function a_UserProcessFinishedMail_is_sent_correctly()
    {
        $toEmail = $this->faker->safeEmail;
        $config = (object)[
            'username' => $this->faker->firstNameMale . " " . $this->faker->lastName
        ];
        $dict = [
            '{{username}}' => $config->username
        ];


        foreach (['en', 'hu'] as $siteLanguage) {
            Mail::fake();
            Mail::to($toEmail)->send(new UserProcessFinishedMail($config, $siteLanguage));
            Mail::assertSent(UserProcessFinishedMail::class, function ($mail) use ($toEmail, $dict, $siteLanguage) {
                return $this->assertMailSettingsCorrect($mail, $toEmail, $dict, $siteLanguage);
            });
        }
    }

}
