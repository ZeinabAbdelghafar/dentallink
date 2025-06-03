<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckUser
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();
            
            if (!$token) {
                return response()->json(['error' => 'Authorization header is missing'], 400);
            }

            $user = JWTAuth::parseToken()->authenticate();
            $request->user = $user;

            return $next($request);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}