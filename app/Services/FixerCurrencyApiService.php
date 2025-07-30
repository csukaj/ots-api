<?php
namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;


/**
 * https://fixer.io/documentation
 *
 * Class FixerCurrencyApiService
 * @package App\Services
 */
class FixerCurrencyApiService
{
    protected $endPoint = 'https://data.fixer.io/api/';
    protected $endPointTypes = 'latest';
    protected $accessKey = '6f156c3ebebbb4e9d821116b128555bb';
    protected $base = 'EUR';
    protected $symbols = 'HUF';

    /**
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCurrencies()
    {
        $responseObj = json_decode($this->getRequest()->getBody()->getContents());
        if (!$responseObj->success) {
            Log::error('Fixer.io service error: '.json_encode($responseObj));
            return false;
        }
        Log::info('Fixer.io service success');
        return $responseObj;
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getRequest()
    {
        app('currency_logger')->info('[' . app()->environment() . '] START request . IP: ' . request()->ip());
        return (new HttpClient())->request('GET',
            "{$this->endPoint}{$this->endPointTypes}",
            [
                'query' => [
                    'access_key'=>$this->accessKey,
                    'base'=>$this->base,
                    'symbols'=>$this->symbols
                ]
            ]
        );
    }
}
