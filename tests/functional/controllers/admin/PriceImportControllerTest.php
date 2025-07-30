<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 2018.01.17.
 * Time: 15:42
 */

namespace Tests\Functional\Controllers\Admin;

use App\Facades\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

class PriceImportControllerTest extends TestCase
{
    /**
     * @test
     */
    public function is_file_uploaded() {
        $this->markTestIncomplete('TODO: need to check and correct');
        $filePath = __DIR__.'/PriceImportControllerTestData/Hotel_A_accommodation_2018_01_17_125121.csv';
        $expectedFileContent = file_get_contents($filePath);
        $file = new UploadedFile($filePath, 'Hotel_A_accommodation_2018_01_17_125121.csv');

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/price/import', 'POST', $token, [], false, ['file' => $file]);
        $this->assertEquals($expectedFileContent, $responseData->data);}
}
