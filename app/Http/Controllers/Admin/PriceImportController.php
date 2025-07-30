<?php

namespace App\Http\Controllers\Admin;

use App\Entities\PriceImportParserEntity;
use App\Http\Controllers\Controller;
use App\Manipulators\PriceImportSetter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceImportController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     * TODO:
     * A request-ből kellene kivenni a fájlt és nem pedig a $_FILES-ból:
     * file_get_contents($request->files->get("uploaded_file")->getPathName()),
     * de ez sajnos a fejlesztés idejében nem működött és a debug-olásra nem jutott több idő...:(
     * @throws \Exception
     */
    public function import(Request $request): JsonResponse
    {
        $priceImport = new PriceImportParserEntity($this->getRequestFile($request));
        $priceList = $priceImport->run()->createPriceList()->priceList;

        return response()->json([
            'success' => (bool)PriceImportSetter::savePriceElements($priceList),
            'data' => [
                'priceList' => $priceList
            ],
            'request' => $request->all()
        ]);
    }

    /**
     * @return string
     */
    private function getRequestFile(Request $request=null): string
    {
        $fileKey = 'file';
        if($request && $request->file($fileKey)){
            return $request->file($fileKey)->getPathName();
        }
        return $_FILES[$fileKey]['tmp_name'];
    }
}
