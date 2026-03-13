# url-shortener

A high-throughput URL shortening service. Converts long URLs into short codes.


# URL Shortener API (Laravel 12)

Backend URL shortener built with:
- SQLite for local persistence
- Laravel local cache, without Redis
- Basic per-URL analytics (`click_count`, `last_access_at`)
- TTL-based expiration (`expires_in_days`)

## Requirements

- PHP 8.2+
- `pdo_sqlite` PHP extension enabled
- Composer 2+

Node.js is not required to use the API locally.

## First Run

After cloning the project, run:

```bash
composer run setup
php artisan serve --host=127.0.0.1 --port=8080
```

The `composer run setup` command:
- installs PHP dependencies
- creates `.env` automatically if it does not exist
- creates `database/database.sqlite` automatically if it does not exist
- generates `APP_KEY` only when it is empty
- runs the migrations

The command is safe to re-run. It does not overwrite `.env`, does not regenerate the application key if it already exists, and does not delete SQLite data.

## Quick Check

With the server running, validate it with:

```bash
curl http://127.0.0.1:8080/
```

Expected response:

```json
{
  "name": "URL Shortener API",
  "create_endpoint": "/api/v1/shorten"
}
```

## Endpoints

### 1) Create a short URL

`POST /api/v1/shorten`

Body:

```json
{
  "url": "https://www.example.com/some/long/url",
  "expires_in_days": 30
}
```

`expires_in_days` is optional. If omitted, the URL does not expire.

`201 Created` response:

```json
{
  "short_url": "http://127.0.0.1:8080/abc123",
  "short_code": "abc123",
  "expires_at": "2026-04-11T05:00:00+00:00"
}
```

Possible errors:
- `400` invalid payload or URL
- `429` rate limit

### 2) Redirect

`GET /{short_code}`

Responses:
- `301` with a `Location` header pointing to the original URL
- `404` short code not found
- `410` expired URL

## curl Examples

Create:

```bash
curl -X POST http://127.0.0.1:8080/api/v1/shorten \
  -H "Content-Type: application/json" \
  -d '{"url":"https://medium.com/@andreelm/make-url-shortener-with-laravel-4983511c7a9d","expires_in_days":30}'
```

Open redirect:

```bash
curl -i http://127.0.0.1:8080/YOUR_SHORT_CODE
```

## Tests

```bash
php artisan test
```

## Persistence

- Data is stored in `database/database.sqlite`
- Restarting the server does not delete links
- Data is only lost if that file is removed or if you run `php artisan migrate:fresh`

## Troubleshooting

If setup fails with an SQLite error, check whether the PHP extension is enabled:

```bash
php -m | grep -i sqlite
```

You should see `pdo_sqlite` in the output.
