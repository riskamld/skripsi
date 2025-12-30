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
        \Log::info('🔐 [MIDDLEWARE] ApiTokenAuth called', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'has_token' => $request->hasHeader('X-API-TOKEN')
        ]);

        $plainToken = $request->header('X-API-TOKEN');

        if (!$plainToken) {
            \Log::warning('❌ [MIDDLEWARE] Missing API token');
            return response()->json(['error' => 'Missing API token'], 401);
        }

        \Log::info('🔑 [MIDDLEWARE] Token received, checking validity...');

        $hashed = hash('sha256', $plainToken);

        $token = ApiToken::where('token', $hashed)
            ->where('is_active', true)
            ->first();

        if (!$token) {
            \Log::warning('❌ [MIDDLEWARE] Invalid or inactive API token', [
                'hashed_token_prefix' => substr($hashed, 0, 10) . '...',
                'token_count' => ApiToken::count(),
                'active_tokens' => ApiToken::where('is_active', true)->count()
            ]);
            return response()->json(['error' => 'Invalid API token'], 401);
        }

        \Log::info('✅ [MIDDLEWARE] Valid API token found', [
            'token_id' => $token->id,
            'token_name' => $token->name,
            'last_used_at' => $token->last_used_at
        ]);

        $token->update([
            'last_used_ip' => $request->ip(),
            'last_used_at' => now(),
        ]);

        // ⭐ penting
        $request->attributes->set('api_token', $token);

        \Log::info('✅ [MIDDLEWARE] Proceeding to next middleware/controller');
        return $next($request);
    }

}
