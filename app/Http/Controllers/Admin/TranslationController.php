<?php

namespace App\Http\Controllers\Admin;

use App\Entities\TranslationExporterEntity;
use App\Entities\TranslationImporterEntity;
use App\Exceptions\UserException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @resource Admin/TranslationController
 */
class TranslationController extends Controller
{

    /**
     * download
     * Export translation JSON to CSV of the specified language and forces the browser to download it
     * @param $isoCode
     * @return Response
     * @throws UserException
     */
    public function download(string $isoCode)
    {
        if (strlen($isoCode) !== 2) {
            throw new UserException('Language ISO code must be 2 characters long!');
        }

        $translationEntity = new TranslationExporterEntity($isoCode);
        $translationEntity->generateCsv();
        $csvFilePath = $translationEntity->getCsvFilePath();

        return response()->download($csvFilePath);
    }

    /**
     * import
     * Imports an uploaded translation CSV to JSON
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $uploadedFile = $request->file('file');

        $translationEntity = new TranslationImporterEntity($uploadedFile);
        $translationEntity->generateNewTranslations();

        return response()->json([
            'success' => true,
            'data' => [],
            'request' => $request->all()
        ]);
    }

}
