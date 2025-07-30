<?php
class ReverseInvoiceTest extends PHPUnit\Framework\TestCase
{
    public $szamlazz, $settings, $header, $seller, $buyer;

    function setUp() {

        $this->cleanOutputDirs();

        header("Content-Type: text/plain; charset=utf-8");
        require_once("./invoiceAgent.class.php");

        global $szamlazz;
        $szamlazz = new invoiceAgent();

    }

    function tearDown() {
        global $szamlazz;
        unset($szamlazz);
        $this->cleanOutputDirs();
    }

    function cleanOutputDirs() {
        array_map('unlink', glob("/var/www/html/evulon/pdf/*.pdf"));
        array_map('unlink', glob("/var/www/html/evulon/xml/*.xml"));
    }

    function setUpIni($invoiceNum) {
        global $szamlazz, $settings, $header, $seller, $buyer;

        $settings = [
            "username" => "<username>",       // username, required, plain string
            "password" => "<password>",       // user password, required, plain string
            "e_invoice" => true,              // use true or false, without quotes
            "keychain" => "",
            "download_invoice" => true,
            "download_count" => 12            // use whole number
        ];
    
        $header = [
            "invoice_num" => $invoiceNum,
            "invoice_date" => "2015-12-12",
            "fulfillment" => "2015-12-12",
            "invoice_type" => "SS"
        ];
    
        $seller = [
            "email_replyto" => "hello@evulon.hu",
            "email_subject" => "Számla értesítő",
            "email_content" => "Fizesse ki a számlát, különben a mindenkori banki kamat",
        ];
    
        $buyer = [
            "email" => "vevoneve@example.org",
        ];
    
    }

     /**
     * @runInSeparateProcess
     */
    //Annotation needed because PHPUnit will print a header to the screen and at that point you can't add more headers
    public function testReverseInvoice()
    {
        // Arrange
        
        // Act
        global $szamlazz, $settings, $header, $seller, $buyer;
        $this->setUpIni("2016-2");
        $success = $szamlazz->_reverseInvoice($settings, $header, $seller, $buyer);

        // Assert
        $this->assertTrue($success);

        $files = scandir("/var/www/html/evulon/pdf", 1);    //return false on failure. It contains "." and ".." too
        $this->assertNotEquals(reset($files), ".");
        $this->assertNotEquals(reset($files), "..");
        $this->assertFileExists("/var/www/html/evulon/pdf/".reset($files));
    }

}
