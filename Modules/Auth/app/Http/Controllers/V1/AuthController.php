<?php

namespace Modules\Auth\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Actions\ChangePasswordAction;
use Modules\Auth\Actions\LoginUserAction;
use Modules\Auth\Http\Requests\ChangePasswordRequest;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\ResetPasswordRequest;
use Modules\Auth\Http\Resources\UserResource;

class AuthController extends Controller
{
    /**
     * Authenticate the user and issue a Sanctum token.
     */
    public function login( LoginRequest $request, LoginUserAction $action): JsonResponse {

      try {
        $result = $action->execute(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

     return response()->json([
    'success' => true,
    'message' => 'Login successful.',
    'data' => [
        'user' => new UserResource($result['user']),
        'token' => $result['token'],
    ],
]);
    } catch (\RuntimeException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => null,
        ], 401);
    }
    }



    /**
 * Return the currently authenticated user.
 */
public function me(Request $request): JsonResponse
{
    return response()->json([
        'success' => true,
        'message' => 'Authenticated user retrieved successfully.',
        'data' => [
            'user' => new UserResource($request->user()),
        ],
    ]);
}

public function logout(Request $request): JsonResponse
{
    $request->user()
        ->currentAccessToken()
        ->delete();

    return response()->json([
        'success' => true,
        'message' => 'Logout successful.',
        'data' => null,
    ]);
}

/**
 * Change the authenticated user's password.
 */
public function changePassword( ChangePasswordRequest $request,ChangePasswordAction $action): JsonResponse {
    try {
        $action->execute(
            user: $request->user(),
            currentPassword: $request->validated('current_password'),
            newPassword: $request->validated('password'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
            'data' => null,
        ]);
    } catch (\RuntimeException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => null,
        ], 422);
    }
}

public function forgotPassword(ForgotPasswordRequest $request): JsonResponse {
    $status = Password::sendResetLink( //send email to user with reset password link
        $request->validated()
    );

    if ($status !== Password::RESET_LINK_SENT) { //if the status is not equal to the reset link sent constant => email not sent, return an error response
        return response()->json([
            'success' => false,
            'message' => __($status),
            'data' => null,
        ], 422);
    }

    return response()->json([
        'success' => true,
        'message' => __($status),
        'data' => null,
    ]);
}

public function resetPassword( ResetPasswordRequest $request): JsonResponse {

    $status = Password::reset(
        $request->validated(),
        function ($user, $password) {
            $user->update([
                'password' => $password,//Laravel hashes the password through the model cast.
            ]);
        }
    );
    //password ::reset =>it will read the passed data then check if the token is valid and if the email exists in the database,
    //then  if both are valid it will call the callback function to update the password in the database.
    //but if the token is unvalid or negative the operation will fail directly

    if ($status !== Password::PASSWORD_RESET) {
        return response()->json([
            'success' => false,
            'message' => __($status),
            'data' => null,
        ], 422);
    }

    return response()->json([
        'success' => true,
        'message' => __($status),
        'data' => null,
    ]);
}

}
