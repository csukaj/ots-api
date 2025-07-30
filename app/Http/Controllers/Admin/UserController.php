<?php

namespace App\Http\Controllers\Admin;

use App\Accommodation;
use App\Entities\UserEntity;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Manipulators\UserSetter;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Modules\Stylersauth\Entities\Role;
use Modules\Stylersauth\Entities\RoleEntity;

/**
 * @resource Admin/UserController
 */
class UserController extends ResourceController
{

    use SendsPasswordResetEmails;

    private $entityAdditions = ['roles', 'organizations', 'sites'];

    /**
     * index
     * Display a listing of Users
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => UserEntity::getCollection(User::orderBy('name', 'ASC')->get(), $this->entityAdditions),
            'organizations' => Accommodation::getEnglishNames(),
            'roles' => RoleEntity::getCollection(Role::orderBy('display_name')->get()),
            'sites' => array_keys(Config::getOrFail('ots.site_languages'))
        ]);
    }

    /**
     * index
     * Display a listing of Users
     * @return JsonResponse
     */
    public function getUserList(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => UserEntity::getCollection(User::orderBy('name', 'ASC')->get()),
        ]);
    }

    /**
     * show
     * Display the specified User
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new UserEntity(User::findOrFail($id)))->getFrontendData($this->entityAdditions)
        ]);
    }

    /**
     * store
     * Store a newly created User
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        $request->merge(['email' => strtolower($request->get('email'))]);
        $requestArray = $request->toArray();
        $requestArray['password'] = str_random(40);
        $user = (new UserSetter($requestArray))->set();

        $success = true;
        if (strpos($request->get('email'), '@example.com') === false) {// if not test user.... TODO: better test case...
            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $response = $this->broker()->sendResetLink($request->only('email'));
            $success = ($response == Password::RESET_LINK_SENT);
        }

        return response()->json([
            'success' => $success,
            'data' => (new UserEntity($user))->getFrontendData($this->entityAdditions)
        ]);
    }

    /**
     * update
     * Update the specified User
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->merge(['email' => strtolower($request->get('email'))]);
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $user = (new UserSetter($requestArray))->set();
        return response()->json([
            'success' => true,
            'data' => (new UserEntity($user))->getFrontendData($this->entityAdditions)
        ]);
    }

    /**
     * destroy
     * Remove the specified User
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json([
            'success' => $user->delete(),
            'data' => (new UserEntity(User::withTrashed()->findOrFail($id)))->getFrontendData($this->entityAdditions)
        ]);
    }

}
