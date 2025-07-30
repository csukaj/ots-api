<?php

namespace App\Http\Controllers\Extranet;

use App\Entities\UserEntity;
use App\Entities\UserSettingEntity;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Manipulators\UserSettingSetter;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class UserSettingsController extends ResourceController
{
    private $user;

    public function __construct(Auth $auth)
    {
        //Get Authenticated user
        $this->middleware(function ($request, $next) {
            $this->user = User::findOrFail(Auth::id());;
            return $next($request);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        return $this->prepareResponse();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        foreach ($request->get('settings') as $setting) {
            $setting['user_id'] = $this->user->id;
            (new UserSettingSetter($setting))->set();
        }
        $this->user->refresh();
        return $this->prepareResponse();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\UserException
     */
    public function update(Request $request, $id): JsonResponse
    {
        foreach ($request->get('settings') as $setting) {
            $setting['user_id'] = $this->user->id;
            (new UserSettingSetter($setting))->set();
        }
        $this->user->refresh();
        return $this->prepareResponse();
    }

    private function prepareResponse(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => (new UserEntity($this->user))->getFrontendData(),
                'settings' => UserSettingEntity::getCollection($this->user->settings),
                'options' => (new TaxonomyEntity(
                    Taxonomy::findOrFail(Config::getOrFail('taxonomies.user_setting'))
                ))->getFrontendData(['descendants'])
            ]
        ]);
    }
}
