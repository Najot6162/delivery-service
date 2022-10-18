<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Passport\RefreshTokenRepository;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message' => $validator->errors()
            ], 400);
        }
        $credentials = request(['phone', 'password']);

        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $tokenData = auth()->user()->createToken('MyApiToken');
        $token = $tokenData->accessToken;
        $expiration = $tokenData->token->expires_at->diffInSeconds(Carbon::now());
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expiration,
        ]);
    }

    public function logout(Request $request)
    {
        $token = auth()->user()->token();

        /* --------------------------- revoke access token -------------------------- */
        $token->revoke();
        $token->delete();

            if ($request->user()->fcm_token) {
            $user = User::findOrFail($request->user()->id);
            $user->fcm_token = null;
            $user->save();
        }
        /* -------------------------- revoke refresh token -------------------------- */
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getAuthUser()
    {
        $user = Auth::user();
        $user = User::with(['userPermission', 'userPermission.menus', 'carModel'])->where('id', $user->id)->first();
        return response()->json([
            'user' => $user
        ]);
    }


}
