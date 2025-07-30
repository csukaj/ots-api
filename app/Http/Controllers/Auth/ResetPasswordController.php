<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ResourceController;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * @resource Auth/ResetPasswordController
 * Password Reset Controller
 * This controller is responsible for handling password reset requests.
 */
class ResetPasswordController extends ResourceController
{
    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        $request->merge(['email' => strtolower($request->get('email'))]);
        $this->validate($request, $this->rules(), $this->validationErrorMessages());
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );

        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['success' => true, 'data' => null, 'message' => ['status' =>trans('passwords.reset')]]);
        } else {
            return response()->json([
                'success' => true,
                'data' => $request->input('email'),
                'message' => ['status' => trans($response)]
            ], 202);
        }

    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        //this needed because User model automatically encrypts password 
        $user->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();

        $this->guard()->login($user);
    }


}
