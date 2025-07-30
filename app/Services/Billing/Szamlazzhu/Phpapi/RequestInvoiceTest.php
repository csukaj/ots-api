<?php
/* 
 *  NOT YET IMPLEMENTED!!!
 *  The pdf needs to be verified manually!!!
 */
class RequestInvoiceTest extends PHPUnit\Framework\TestCase
{
    public $szamlazz, $scope;

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
        array_map('unlink', glob("/var/www/html/evulon/pdf/*.pdf"));    // Save our pdf to evulon folder
        array_map('unlink', glob("/var/www/html/evulon/xml/*.xml"));
    }

    function setUpIni($invoiceNum) {
        global $szamlazz, $scope;

        $scope = [
            "username" => "<username>",       // username, required, plain string
            "password" => "<password>",       // user password, required, plain string
            "invoice_num" => $invoiceNum
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
        global $szamlazz, $scope;
        $invoiceNum = "MEGFI-2015-54";
        $this->setUpIni($invoiceNum);
        $valasz = $szamlazz->_requestInvoicePDF($scope);        //jóváíró számla

        // Assert
        $this->assertEquals($valasz["invoice_number"], $invoiceNum);

        $pdfFile = 'reqInvoice.pdf';
        file_put_contents($pdfFile, $valasz["pdf"]);
    }

}
