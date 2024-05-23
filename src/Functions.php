<?php declare(strict_types=1);

function wpUrl(string $path = ''): string
{
    return rtrim(getenv('WP_URL'), '/') . '/' . ltrim($path, '/');
}

function config(string $key): mixed
{
    // Load the configuration once per request
    static $config;
    if (!$config) {
        $config = require_once ROOT_DIR . 'config.php';
    }

    // Explode the key into parts (e.g., 'database.connections.mysql.host')
    $keys = explode('.', $key);

    // Traverse the configuration array
    $value = $config;
    foreach ($keys as $segment) {
        if (!isset($value[$segment])) {
            // Key not found
            return null; // Or throw an exception
        }
        $value = $value[$segment];
    }

    return $value;
}

function site_url(): string
{
    if (getenv('APP_ENV') === 'dev') {
        $siteUrl = getenv('SITE_URL');
    } else {
        $siteUrl = config('site_url');
    }

    return $siteUrl;
}