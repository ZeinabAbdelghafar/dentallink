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
            
            if (!$user || !$user->verified) {
                return response()->json(['Status' => 403, 'msg' => 'Not Authorized'], 403);
            }

            return $next($request);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}