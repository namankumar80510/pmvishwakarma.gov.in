<?php declare(strict_types=1);

function wpUrl(string $path = ''): string
{
    return rtrim(getenv('WP_URL'), '/') . '/' . ltrim($path, '/');
}