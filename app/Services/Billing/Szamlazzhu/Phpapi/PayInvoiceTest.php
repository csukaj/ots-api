<?php
class PayInvoiceTest extends PHPUnit\Framework\TestCase
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
            "username" => "<username>",        // username, required, plain text
            "password" => "<password>",        // user password, required, plain text
            "invoice_num" => $invoiceNum,
            "download_invoice" => true,
            "additive" => true                 // ha true, akkor nem törli a korábbi jóváírásokat
        ];
    
        $szamlazz->addPayment([
            "date" => "2015-12-13",
            "transaction_title" => "készpénz",
            "amount" => "999",
        ]);
    
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
        $this->setUpIni("MEGFI-2015-54");
        $success = $szamlazz->_payInvoice($settings, $header, $seller, $buyer);        //jóváíró számla

        // Assert
        $this->assertTrue($success);

        $files = scandir("/var/www/html/evulon/pdf", 1);    //return false on failure. It contains "." and ".." too
        $this->assertNotEquals(reset($files), ".");    
        $this->assertNotEquals(reset($files), "..");
        $this->assertFileExists("/var/www/html/evulon/pdf/".reset($files));

        $this->assertEquals(reset($files)[0], "M");        //M, the default prefix is MEGFI

    }

}
