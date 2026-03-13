<?php

namespace Tests\Feature;

use App\Models\ShortUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_short_url_with_optional_expiration(): void
    {
        $response = $this->postJson('/api/v1/shorten', [
            'url' => 'https://www.example.com/some/long/url',
            'expires_in_days' => 30,
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'short_url',
                'short_code',
                'expires_at',
            ]);

        $shortCode = $response->json('short_code');

        $this->assertSame('sb', $shortCode);
        $this->assertDatabaseHas('short_urls', [
            'short_code' => $shortCode,
            'original_url' => 'https://www.example.com/some/long/url',
        ]);
    }

    public function test_it_returns_400_for_invalid_url_payload(): void
    {
        $response = $this->postJson('/api/v1/shorten', [
            'url' => 'www.google.com',
        ]);

        $response->assertStatus(400);
    }

    public function test_it_redirects_with_301_and_records_analytics(): void
    {
        $shortUrl = ShortUrl::create([
            'short_code' => 'abc123',
            'original_url' => 'https://laravel.com',
            'expires_at' => null,
        ]);

        $this->get('/abc123')
            ->assertStatus(301)
            ->assertRedirect('https://laravel.com');

        $shortUrl->refresh();

        $this->assertSame(1, (int) $shortUrl->click_count);
        $this->assertNotNull($shortUrl->last_access_at);
    }

    public function test_it_redirects_from_cache_and_still_records_analytics(): void
    {
        $shortUrl = ShortUrl::create([
            'short_code' => 'cache12',
            'original_url' => 'https://laravel.com/docs',
            'expires_at' => null,
        ]);

        Cache::put(ShortUrl::cacheKey('cache12'), $shortUrl->toCachePayload(), now()->addDay());

        $this->get('/cache12')
            ->assertStatus(301)
            ->assertRedirect('https://laravel.com/docs');

        $shortUrl->refresh();

        $this->assertSame(1, $shortUrl->click_count);
        $this->assertNotNull($shortUrl->last_access_at);
    }

    public function test_it_returns_404_for_unknown_short_code(): void
    {
        $this->get('/notfound1')->assertNotFound();
    }

    public function test_it_returns_410_for_expired_short_url(): void
    {
        ShortUrl::create([
            'short_code' => 'expired1',
            'original_url' => 'https://example.com',
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        $this->get('/expired1')->assertStatus(410);
    }

    public function test_it_returns_410_for_expired_short_url_cached_entry(): void
    {
        $shortUrl = ShortUrl::create([
            'short_code' => 'expired2',
            'original_url' => 'https://example.com',
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        Cache::put(ShortUrl::cacheKey('expired2'), $shortUrl->toCachePayload(), now()->addMinute());

        $this->get('/expired2')->assertStatus(410);

        $this->assertFalse(Cache::has(ShortUrl::cacheKey('expired2')));

        $shortUrl->refresh();

        $this->assertSame(0, $shortUrl->click_count);
        $this->assertNull($shortUrl->last_access_at);
    }
}
