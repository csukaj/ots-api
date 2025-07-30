<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @resource Controller
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $authUser;

    /**
     * getAuthUser
     * Return the user logged in or NULL
     * @return User|null
     * @throws JWTException
     */
    protected function getAuthUser() {
        if (is_null($this->authUser)) {
            $authUserData = JWTAuth::parseToken()->authenticate();
            if (!is_null($authUserData)) {
                $this->authUser = User::find($authUserData->id);
            }
        }
        return $this->authUser;
    }

    /**
     * gateAllows
     * Check if the gate allows the action to be called
     * @param string $action
     * @param array|mixed $identifier
     * @return bool
     * @throws JWTException
     */
    protected function gateAllows($action, $identifier = null) {
       return Gate::forUser($this->getAuthUser())->allows($action, \json_encode($identifier));
    }
}
