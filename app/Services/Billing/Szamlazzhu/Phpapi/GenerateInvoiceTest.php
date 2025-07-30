<?php
class GenerateInvoiceTest extends PHPUnit\Framework\TestCase
{
    public $szamlazz, $settings, $header, $seller, $buyer, $waybill;

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

    function setUpIni($buyerName, $isEInvoice, $isDownloadInvoice, $isDeposit, $isFinal, $isProform, $isCorrective, $prefix = "MEGFI") {
        global $szamlazz, $settings, $header, $seller, $buyer, $waybill;

        $settings = [
            "username" => "<username>",    // username, required,  plain string
            "password" => "<password>",    // user password, required, plain string
            "e_invoice" => $isEInvoice,    // use true or false, without quotes
            "keychain" => "1234", 
            "download_invoice" => $isDownloadInvoice,
            "download_count" => 1,         // use whole number,
            "return_invoice_number_as_result" => true
        ];
    
        $header = [
            "invoice_date" => "2015-12-12", // set date in YYYY-MM-DD format
            "fulfillment" => "2015-12-12",
            "payment_due" => "2015-12-12",
            "payment_method" => "csekk",
            "currency" => "USD",
            "language" => "it",
            "comment" => "Dollarban fizetem.",
            "exchange_bank" => "MNB",
            "exchange_rate" => "292.3",
            "is_deposit" => $isDeposit,
            "is_final" => $isFinal,
            "is_proform" => $isProform,
            "is_corrective" => $isCorrective,
            "num_prefix" => $prefix,
            "is_paid" => false
        ];
    
        $seller = [
            "bank" => "OTP Bank",
            "bank_account" => "11111111-22222222-33333333",
            "email_replyto" => "hello@evulon.hu",
            "email_subject" => "Számla értesítő",
            "email_content" => "Fizesse ki a számlát, különben a mindenkori banki kamat",
            "signatory" => "Evulocion"
        ];
    
        $buyer = [
            "name" => $buyerName,
            "country" => "USA",
            "zip" => "2030",
            "city" => "Érd",
            "address" => "Tárnoki út 23.",
            "email" => "vevoneve@example.org",
            "send_email" => true,
            "tax_no" => "12345678-1-42",
            "postal_name" => "Kovács Bt. postázási név",
            "postal_zip" => "2040",
            "postal_city" => "Budaörs",
            "postal_address" => "Szivárvány utca 8. VI.em. 82.",
            // xml branch how to: 
             "buyer_account" => ["account_date" => "2015-12-12", "buyer_id" => "123456", "account_id" => "123456"], 
            "signatory" => "Vevő Aláírója",
            "phone" => "+3630-555-55-55, Fax:+3623-555-555",
            "comment" => "A portáról felszólni a 214-es mellékre."
        ];
    
        $waybill = [
            "destination" => "103",
            "parcel" => "PPP",
            "trans-o-flex" => ["shipment_id" => "asdf", "country_code" => "hu"]
        ];
    
        $szamlazz->addItem([
            "name" => "Eladó stuff",
            "quantity" => "1.0",
            "quantity_unit" => "db",
            "unit_price" => "10000",
            "vat" => "27",
            "net_price" => "10000.0",
            "vat_amount" => "2700.0",
            "gross_amount" => "12700.0",
            "comment" => "tétel megjegyzés 1"
        ]);
    }

     /**
     * @runInSeparateProcess
     */
    //Annotation needed because PHPUnit will print a header to the screen and at that point you can't add more headers
    public function testGenerateDijbekero()
    {
        // Arrange
        
        // Act
        global $szamlazz, $settings, $header, $seller, $buyer, $waybill;
        $this->setUpIni("Kovács Bt.", false, true, false, false, true, false);    //díjbekérő
        $success = $szamlazz->_generateInvoice($settings, $header, $seller, $buyer, $waybill);

        // Assert
        $this->assertTrue($success);

        $files = scandir("/var/www/html/evulon/pdf", 1);    // return false on failure. It contains "." and ".." too
        $this->assertNotEquals(reset($files), ".");    
        $this->assertNotEquals(reset($files), "..");
        $this->assertFileExists("/var/www/html/evulon/pdf/".reset($files));

        $this->assertEquals(reset($files)[0], "D");        // D, like Díjbekérő prefix
    }

     /**
     * @runInSeparateProcess
     */
        public function testGenerateElolegszamla()
    {
        // Arrange
        
        // Act
        global $szamlazz, $settings, $header, $seller, $buyer, $waybill;
        $this->setUpIni("Kovács Bt.", false, true, true, false, false, false);    //előlegszámla
        $success = $szamlazz->_generateInvoice($settings, $header, $seller, $buyer, $waybill);

        // Assert
        $this->assertTrue($success);

        $files = scandir("/var/www/html/evulon/pdf", 1);    //return false on failure. It contains "." and ".." too
        $this->assertNotEquals(reset($files), ".");    
        $this->assertNotEquals(reset($files), "..");
        $this->assertFileExists("/var/www/html/evulon/pdf/".reset($files));

        $this->assertEquals(reset($files)[0], "M");        //M, the default prefix is MEGFI
    }

     /**
     * @runInSeparateProcess
     */
        public function testGenerateHelyesbitoszamla()
    {
        // Arrange
        
        // Act
        global $szamlazz, $settings, $header, $seller, $buyer, $waybill;
        $this->setUpIni("Kovács Bt.", false, true, false, false, false, true);    //helyesbítőszámla
        //array_merge($header, array("invoice_num" => "MEGFI-2015-3") );
        $header += ["correctived_num" => "MEGFI-2015-3"];  //helyesbitett szamla szama
        $success = $szamlazz->_generateInvoice($settings, $header, $seller, $buyer, $waybill);

        // Assert
        $this->assertTrue($success);

        $files = scandir("/var/www/html/evulon/pdf", 1);    //return false on failure. It contains "." and ".." too
        $this->assertNotEquals(reset($files), ".");    
        $this->assertNotEquals(reset($files), "..");
        $this->assertFileExists("/var/www/html/evulon/pdf/".reset($files));

        $this->assertEquals(reset($files)[0], "M");        //M, the default prefix is MEGFI
    }

     /**
     * @runInSeparateProcess
     */
    public function testSzamlaXMLContent()
    {
        // Arrange
        
        // Act
        global $szamlazz, $settings, $header, $seller, $buyer, $waybill;
        $vevoNeve = "Kovács > tsa Bt.";
        $this->setUpIni($vevoNeve, false, true, false, false, false, false);    //sima számla
        $success = $szamlazz->_generateInvoice($settings, $header, $seller, $buyer, $waybill);

        // Assert
        $this->assertTrue($success);

        $pdfFiles = scandir("/var/www/html/evulon/pdf", 1);    //return false on failure. It contains "." and ".." too
        $xmlFiles = scandir("/var/www/html/evulon/xml", 1);
        $this->assertNotEquals(reset($pdfFiles), ".");    
        $this->assertNotEquals(reset($pdfFiles), "..");
        $this->assertFileExists("/var/www/html/evulon/pdf/".reset($pdfFiles));
    
        $this->assertEquals(reset($pdfFiles)[0], "M");        //M, the default prefix is MEGFI
    
        //is the buyer's name in XML equals the input?
        $xmlSzamla = simplexml_load_file("/var/www/html/evulon/xml/".reset($xmlFiles));
        $vevoNeveinXml = $xmlSzamla->vevo->nev;
        $this->assertEquals($vevoNeve, $vevoNeveinXml);
    }

}
