<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\UserException;
use App\Http\Controllers\ResourceController;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Modules\Stylersauth\Entities\User;

/**
 * @resource Auth/PasswordController
 * Password Reset Controller
 * This controller is responsible for handling password reset requests.
 */
class ForgotPasswordController extends ResourceController
{
    use SendsPasswordResetEmails;

    /**
     * Create a new password controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws UserException
     */
    public function getResetToken(Request $request): JsonResponse
    {
        $request->merge(['email' => strtolower($request->get('email'))]);
        if ($request->wantsJson()) {

            $this->validateEmail($request);

            $user = User::where('email', $request->input('email'))->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => ['status' => trans('passwords.email_reset_invalid')]
                ], 202);
            }

            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $response = $this->broker()->sendResetLink($request->only('email'));

            if ($response == Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => ['status' => trans($response)]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => $request->input('email'),
                    'message' => ['status' => trans($response)]
                ], 202);
            }

        }

        return $this->sendResetLinkEmail($request);
    }


}
