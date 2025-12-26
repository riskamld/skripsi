<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiToken;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->header('X-API-TOKEN');

        if (!$plainToken) {
            return response()->json(['error' => 'Missing API token'], 401);
        }

        $hashed = hash('sha256', $plainToken);

        $token = ApiToken::where('token', $hashed)
            ->where('is_active', true)
            ->first();

        if (!$token) {
            return response()->json(['error' => 'Invalid API token'], 401);
        }

        $token->update([
            'last_used_ip' => $request->ip(),
            'last_used_at' => now(),
        ]);

        // ⭐ penting
        $request->attributes->set('api_token', $token);

        return $next($request);
    }

}
