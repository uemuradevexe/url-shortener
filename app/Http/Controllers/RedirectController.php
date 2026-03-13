<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RedirectController extends Controller
{
    public function __invoke(string $shortCode): RedirectResponse
    {
        $cacheKey = ShortUrl::cacheKey($shortCode);
        $cached = Cache::get($cacheKey);

        if ($cached === null) {
            $shortUrl = ShortUrl::query()
                ->where('short_code', $shortCode)
                ->first();

            abort_if(! $shortUrl, 404);

            abort_if($shortUrl->isExpired(), 410, 'Short URL expired.');

            $cached = $shortUrl->toCachePayload();
            Cache::put($cacheKey, $cached, $shortUrl->expires_at ?? now()->addDay());
        }

        $expiresAt = ShortUrl::expiresAtFromCache($cached);

        if ($expiresAt !== null && $expiresAt->isPast()) {
            Cache::forget($cacheKey);
            abort(410, 'Short URL expired.');
        }

        ShortUrl::query()
            ->whereKey($cached['id'])
            ->update([
                'click_count' => DB::raw('click_count + 1'),
                'last_access_at' => now(),
            ]);

        return redirect()->away($cached['original_url'], 301);
    }
}
