<?php

namespace Modules\Stylersauth\Http\Controllers;

use App\Entities\UserEntity;
use App\User;
use Illuminate\Http\Request;
use Nwidart\Modules\Routing\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @resource Stylersauth/StylersauthController
 */
class StylersauthController extends Controller
{
    public function __construct()
    {
        $this->middleware('logrequest')->except(['authenticate']);
    }

    /**
     * authenticate
     * Log in a User by email and password and return a fresh token
     * @param Request $request
     * @return type
     */
    public function authenticate(Request $request)
    {
        // grab credentials from request
        $request->merge(['email' => strtolower($request->get('email'))]);
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['success' => false, 'error' => 'The provided password or username is not correct. Please try again.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(['success' => true, 'data' => compact('token')]);
    }

    /**
     * logout
     * Log out any User logged in and invalidate token
     * @return type
     */
    public function logout()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['success' => false, 'error' => 'user_not_found'], 404);
            }
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['success' => true, 'error' => 'user_logged_out'], 200);

        } catch (TokenExpiredException $e) {
            return response()->json(['success' => false, 'error' => 'token_expired'], 401);

        } catch (TokenInvalidException $e) {
            return response()->json(['success' => false, 'error' => 'token_invalid'], 401);

        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'token_absent'], 401);
        }
    }

    /**
     * user
     * Return logged in user & its token
     * @param Request $request
     * @return type
     */
    public function user(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['success' => false, 'error' => 'user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['success' => false, 'error' => 'token_expired'], 401);
        } catch (TokenBlacklistedException $e) {
            return response()->json(['success' => false, 'error' => 'token_blacklisted'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['success' => false, 'error' => 'token_invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'token_absent'], 401);
        }

        $user = User::find($user->id);

        // the token is valid and we have found the user via the sub claim
        return response()->json([
            'success' => true,
            'token' => $request->refreshToken ? JWTAuth::refresh(JWTAuth::getToken()) : null,
            'data' => (new UserEntity($user))->getFrontendData(['roles', 'organizations'])
        ], 200);
    }

}
