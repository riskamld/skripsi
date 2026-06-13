<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Hanya dari localhost
        $ip = $request->ip();
        if (!in_array($ip, ['127.0.0.1', '::1', '::ffff:127.0.0.1'])) {
            return response()->json(['status' => 'forbidden'], 403);
        }

        $event   = $request->input('event');
        $from    = $request->input('from'); // nomor tanpa @s.whatsapp.net
        $message = $request->input('message', '');

        Log::info("[WA Webhook] event={$event} from={$from} msg=" . substr($message, 0, 50));

        if ($event === 'message.received' && $from) {
            // Cari place berdasarkan nomor yang cocok
            // Normalize: hapus leading 62 atau 0, cari partial match
            $normalized = ltrim($from, '0');
            if (str_starts_with($normalized, '62')) $normalized = substr($normalized, 2);

            $place = Place::where('has_whatsapp', true)
                ->where('outreach_status', 'sent')
                ->where(function ($q) use ($from, $normalized) {
                    $q->where('phone', 'like', "%{$normalized}")
                      ->orWhere('phone', $from)
                      ->orWhere('phone', '0' . $normalized)
                      ->orWhere('phone', '62' . $normalized);
                })
                ->first();

            if ($place) {
                $place->update(['outreach_status' => 'responded']);
                Log::info("[WA Webhook] Marked {$place->name} (#{$place->id}) as responded");
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
