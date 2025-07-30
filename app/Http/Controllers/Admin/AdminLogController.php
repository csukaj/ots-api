<?php

namespace App\Http\Controllers\Admin;

use App\AdminLog;
use App\Entities\AdminLogEntity;
use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/AdminLogController
 */
class AdminLogController extends ResourceController
{

    /**
     * show
     * Display admin log for a User
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request=null): JsonResponse
    {
        $lastLoginRecord = AdminLog
            ::forUser($id)
            ->forRoute('stylersauth/user')
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();
        $availabilityLog = AdminLog
            ::forUser($id)
            ->forRoute('extranet/availability')
            ->orderBy('id', 'desc')
            ->paginate(10);
        $availabilityLog->setCollection(collect(AdminLogEntity::getCollection($availabilityLog->getCollection())));

        return response()->json([
            'success' => true,
            'data' => $availabilityLog,
            'lastLogin' => $lastLoginRecord ? (new AdminLogEntity($lastLoginRecord))->getFrontendData()['created_at'] : null
        ]);
    }
}
