<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\ApiToken;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('🔐 [MIDDLEWARE] ApiTokenAuth called', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'has_token' => $request->hasHeader('X-API-TOKEN')
        ]);

        $incomingToken = $request->header('X-API-TOKEN');

        if (!$incomingToken) {
            Log::warning('❌ [MIDDLEWARE] Missing API token');
            return response()->json(['error' => 'Missing API token'], 401);
        }

        Log::info('🔑 [MIDDLEWARE] Token received, checking validity...');

        // Check if incoming token is already a SHA-256 hash (64 characters)
        if (strlen($incomingToken) === 64 && ctype_xdigit($incomingToken)) {
            // Token is already hashed, compare directly
            $tokenToCompare = $incomingToken;
            Log::info('🔑 [MIDDLEWARE] Incoming token appears to be pre-hashed');
        } else {
            // Token is plain, hash it for comparison
            $tokenToCompare = hash('sha256', $incomingToken);
            Log::info('🔑 [MIDDLEWARE] Incoming token hashed for comparison');
        }

        $token = ApiToken::where('token', $tokenToCompare)
            ->where('is_active', true)
            ->first();

        if (!$token) {
            Log::warning('❌ [MIDDLEWARE] Invalid or inactive API token', [
                'token_prefix' => substr($tokenToCompare, 0, 10) . '...',
                'token_count' => ApiToken::count(),
                'active_tokens' => ApiToken::where('is_active', true)->count()
            ]);
            return response()->json(['error' => 'Invalid API token'], 401);
        }

        Log::info('✅ [MIDDLEWARE] Valid API token found', [
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

        Log::info('✅ [MIDDLEWARE] Proceeding to next middleware/controller');
        return $next($request);
    }

}
