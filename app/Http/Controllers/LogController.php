<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @resource LogController
 * JS error reporting
 */
class LogController extends Controller
{

    /**
     * log
     * Save request to log as JS error
     * @param Request $request The request to be logged.
     * @return JsonResponse
     */
    public function log(Request $request): JsonResponse
    {
        $requestJson = stripslashes(str_replace('\n', "\n", json_encode($request->toArray(), JSON_PRETTY_PRINT)));
        Log::error('JS error caught: ' . $requestJson . "\n");
        return response()->json(['success' => true]);
    }

}
