<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use App\Services\Base62Encoder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShortenUrlController extends Controller
{
    public function __invoke(Request $request, Base62Encoder $base62Encoder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => ['required', 'string', 'url:http,https', 'max:2048'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Malformed or invalid URL payload.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();
        $expiresAt = isset($validated['expires_in_days'])
            ? Carbon::now()->addDays((int) $validated['expires_in_days'])
            : null;

        $shortUrl = DB::transaction(function () use ($base62Encoder, $validated, $expiresAt): ShortUrl {
            $shortUrl = ShortUrl::query()->create([
                'short_code' => null,
                'original_url' => $validated['url'],
                'expires_at' => $expiresAt,
            ]);

            $shortUrl->short_code = $base62Encoder->encode($shortUrl->id);
            $shortUrl->save();

            return $shortUrl;
        });

        Cache::put(
            ShortUrl::cacheKey($shortUrl->short_code),
            $shortUrl->toCachePayload(),
            $shortUrl->expires_at ?? now()->addDay()
        );

        return response()->json([
            'short_url' => $request->root().'/'.$shortUrl->short_code,
            'short_code' => $shortUrl->short_code,
            'expires_at' => $shortUrl->expires_at?->toIso8601String(),
        ], 201);
    }
}
