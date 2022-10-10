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

    public function loginGrant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::with(['userPermission', 'userPermission.menus', 'carModel'])->where('phone', $request->phone)->first();
        $baseUrl = url('http://devserv.gx.uz');
        $response = Http::post("{$baseUrl}/oauth/token", [
            'username' => $request->email,
            'password' => $request->password,
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'grant_type' => 'password'
        ]);

        $result = json_decode($response->getBody(), true);
        if (!$response->ok()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'result'=>$result,
            'user' => $user,
            "data"=>"ok"
        ]);
    }
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $baseUrl = url('http://devserv.gx.uz');
        $response = Http::post("{$baseUrl}/oauth/token", [
            'refresh_token' => $request->refresh_token,
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'grant_type' => 'refresh_token'
        ]);

        $result = json_decode($response->getBody(), true);
        if (!$response->ok()) {
            return response()->json(['error' => $result['error_description']], 401);
        }
        return response()->json($result);
    }

}
