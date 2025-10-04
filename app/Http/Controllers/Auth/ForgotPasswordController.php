<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ForgotPassword;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }


    /**
 * Send Password Reset Link
 *
 * This endpoint sends a password reset link to the given email address
 * if it belongs to a registered user or client.
 *@group User Authentication
 * @bodyParam email string required The email address of the user or client. Example: user@example.com
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Password reset link emailed successfully."
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "email": [
 *       "The email field is required."
 *     ]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Password reset link couldn't be sent, please check email settings."
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Password reset link couldn't be sent, please configure email settings."
 * }
 *
 * @group Authentication
 *
 * This method determines whether the provided email belongs to a user or client,
 * then uses the appropriate password broker to send a reset link.
 */

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        // dd($request->email);

        if (isEmailConfigured()) {
            $provider = $this->determineProvider($request->email);
            try {
                // Check if the email exists in the users table
                $userExists = User::where('email', $request->email)->exists();

                // Check if the email exists in the clients table
                $clientExists = Client::where('email', $request->email)->exists();


                if ($userExists) {
                    // If the email exists in the users table, temporarily set the default broker to 'users'
                    config(['auth.defaults.passwords' => 'users']);
                } elseif ($clientExists) {
                    // If the email exists in the clients table, temporarily set the default broker to 'clients'
                    config(['auth.defaults.passwords' => 'clients']);
                }

                $response = $this->broker($provider)->sendResetLink(
                    $request->only('email'),
                    function ($user, $token) use ($provider) {
                        // Send the custom notification
                        $resetUrl = $this->generateResetUrl($token, $user->email);
                        $user->notify(new ForgotPassword($user, $resetUrl));
                        // dd($resetUrl);
                    }
                );
                // Restore the default broker configuration
                config(['auth.defaults.passwords' => 'users']);

                if ($response == Password::RESET_LINK_SENT) {
                    // Session::flash('message', __($response));
                    return response()->json(['error' => false, 'message' => __('Password reset link emailed successfully.')]);
                } else {
                    return response()->json(['error' => true, 'message' => __($response)]);
                }
            } catch (\Exception $e) {
                // dd($e);
                return response()->json(['error' => true, 'message' => 'Password reset link couldn\'t be sent, please check email settings.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Password reset link couldn\'t be sent, please configure email settings.']);
        }
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function ResetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);
        $provider = $this->determineProvider($request->email);
        if ($provider == 'users') {
            $status = Password::broker('users')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                    // if (isEmailConfigured()) {
                    //     event(new PasswordReset($user));
                    // }
                }
            );
        } else {
            $status = Password::broker('clients')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (Client $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                    // if (isEmailConfigured()) {
                    //     event(new PasswordReset($user));
                    // }
                }
            );
        }

        if ($status === Password::PASSWORD_RESET) {
            Session::flash('message', __($status));
            return response()->json(['error' => false]);
        } else {
            return response()->json(['error' => true, 'message' => __($status)]);
        }
    }

/**
 * Reset Password (API)
 *
 * Reset a user's or client's password using a valid token. This is used after the user clicks a reset link in their email.
 *
 * @group User Authentication
 *
 * @header workspace_id integer required The ID of the workspace the user belongs to. Example: 1
 * @header Accept string required Must be `application/json`. Example: application/json
 * @header Content-Type string required Must be `application/json`. Example: application/json
 *
 * @bodyParam token string required The password reset token from the reset email. Example: abc123
 * @bodyParam email string required The email of the user or client. Example: john.doe@example.com
 * @bodyParam password string required The new password (min 6 characters). Example: newPassword123
 * @bodyParam password_confirmation string required Must match the password field. Example: newPassword123
 * @bodyParam account_type string required Type of account: `user` or `client`. Example: user
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Password reset successful.",
 *   "data": []
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "This password reset token is invalid.",
 *   "data": []
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "An unexpected error occurred.",
 *   "data": {
 *     "error": "Exception message here"
 *   }
 * }
 */

    public function api_resetPassword(Request $request)
{
    $isApi = $request->get('isApi', true); // Default true for API context

    try {
        $formFields = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $provider = $this->determineProvider($request->email);

        $broker = $provider === 'users' ? Password::broker('users') : Password::broker('clients');

        $status = $broker->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Optional: Trigger password reset event if email is configured
                // if (isEmailConfigured()) {
                //     event(new PasswordReset($user));
                // }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return formatApiResponse(false, __('Password reset successful.'), [], 200);
        } else {
            return formatApiResponse(true, __($status), [], 422);
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return formatApiValidationError($e, $isApi);
    } catch (\Exception $e) {
        return formatApiResponse(true, 'An unexpected error occurred.', [
            'error' => $e->getMessage()
        ], 500);
    }
}

    protected function determineProvider($email)
    {
        // Determine whether the email belongs to a user or a client
        return User::where('email', $email)->exists() ? 'users' : (Client::where('email', $email)->exists() ? 'clients' : null);
        // dd($email);
    }

    // Generate the reset password URL
    protected function generateResetUrl($token, $email)
    {
        // Generate the URL with the token embedded in the path and email as a query parameter
        return url('/reset-password/' . $token) . '?' . http_build_query([
            'email' => $email,
        ]);
    }
}
