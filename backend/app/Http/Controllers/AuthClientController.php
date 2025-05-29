<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthClientController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Use the 'client' guard
        if (!$token = Auth::guard('client')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('client')->user();

        $accessToken = JWTAuth::claims([
            'exp' => Carbon::now()->addMinutes(7200)->timestamp,
        ])->fromUser($user);

        $refreshToken = JWTAuth::claims([
            'exp' => Carbon::now()->addDays(7)->timestamp,
        ])->fromUser($user);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $refreshToken = JWTAuth::parseToken()->refresh();

            $user = Auth::guard('client')->user();

            $newAccessToken = JWTAuth::claims([
                'exp' => Carbon::now()->addMinutes(5)->timestamp,
            ])->fromUser($user);

            $newRefreshToken = JWTAuth::claims([
                'exp' => Carbon::now()->addDays(7)->timestamp,
            ])->fromUser($user);

            return response()->json([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }
    }

    public function detailsClient(Request $request)
    {
        $client = Auth::guard('client')->user(); 
        return response()->json($client);
    }
}
