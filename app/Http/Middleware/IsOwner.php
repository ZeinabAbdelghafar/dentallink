<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsOwner
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user->id != $request->route('id')) {
            return response()->json(['error' => 'Unauthorized: You do not have permission to access this resource.'], 401);
        }

        return $next($request);
    }
}