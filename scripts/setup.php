<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$envPath = $projectRoot.'/.env';
$envExamplePath = $projectRoot.'/.env.example';
$databasePath = $projectRoot.'/database/database.sqlite';

if (! file_exists($envPath)) {
    copy($envExamplePath, $envPath);
}

if (! file_exists($databasePath)) {
    touch($databasePath);
}

$envContents = file_get_contents($envPath);

if ($envContents === false) {
    fwrite(STDERR, "Unable to read .env file.\n");
    exit(1);
}

if (! preg_match('/^APP_KEY=.+/m', $envContents)) {
    passthru(PHP_BINARY.' artisan key:generate --ansi', $exitCode);

    if ($exitCode !== 0) {
        exit($exitCode);
    }
}

passthru(PHP_BINARY.' artisan migrate --force', $exitCode);

exit($exitCode);
