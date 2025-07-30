<?php

namespace App\Http\Controllers\Admin;

use App\Entities\EmailEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\EmailSetter;
use App\Email;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/UserController
 */
class EmailController extends ResourceController
{
    /**
     * index
     * Display a listing of Emails
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EmailEntity::getCollection(Email::all())
        ]);
    }

    /**
     * show
     * Display the specified Email
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new EmailEntity(Email::findOrFail($id)))->getFrontendData()
        ]);
    }

    /**
     * update
     * Update the specified Email
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \App\Exceptions\EmailException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $email = (new EmailSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new EmailEntity($email))->getFrontendData()
        ]);
    }
}
