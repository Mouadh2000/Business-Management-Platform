<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->is_admin || !$user->is_staff) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin staff can perform this action.'
            ], 403);
        }

        return $next($request);
    }
}