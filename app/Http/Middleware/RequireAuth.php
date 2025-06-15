<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class RequireAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['error' => 'Authorization header is missing'], 401);
            }

            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 403,
                    'error' => 'User not found or token invalid',
                    'message' => 'Authentication failed. Please log in again.'
                ], 403);
            }

            if (!$user->verified) {
                return response()->json([
                    'status' => 403,
                    'error' => 'User not verified',
                    'message' => 'Your account is not verified. Please verify your email to continue.'
                ], 403);
            }

            return $next($request);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}
