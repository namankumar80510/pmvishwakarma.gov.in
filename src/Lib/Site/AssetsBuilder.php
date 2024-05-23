<?php

namespace App\Lib\Site;

use GuzzleHttp\Client;
use Nette\Utils\FileSystem;

class AssetsBuilder
{
    public function build()
    {
        $siteUrl = site_url();
        $wpUrl = getenv('WP_URL');
        $httpclient = new Client([
            'verify' => false
        ]);
        $assets = config('assets');
        foreach ($assets as $asset) {
            $content = $httpclient->request('GET', wpUrl($asset))
                ->getBody()->getContents();
            $content = str_replace($wpUrl, $siteUrl, $content);
            $content = str_replace(basename($wpUrl), basename($siteUrl), $content);
            FileSystem::write(
                BUILD_DIR . $asset,
                $content
            );
        }

        $uploadsDir = ROOT_DIR . rtrim(getenv('WP_DIR'), DS) . DS . 'wp-content/uploads';
        if (file_exists($uploadsDir)) {
            FileSystem::copy(
                $uploadsDir,
                BUILD_DIR . 'wp-content/uploads'
            );
        }

        $cacheDir = ROOT_DIR . rtrim(getenv('WP_DIR'), DS) . DS . 'wp-content/cache';
        if (file_exists($cacheDir)) {
            FileSystem::copy(
                $cacheDir,
                BUILD_DIR . 'wp-content/cache'
            );
        }

        FileSystem::copy(
            ROOT_DIR . 'static',
            BUILD_DIR . 'static'
        );

        
        FileSystem::write(
            BUILD_DIR . 'robots.txt',
            <<<TXT
User-agent: *
Disallow: /wp-admin/
Disallow: /404.html

Sitemap: $siteUrl/sitemap_index.xml
Sitemap: $siteUrl/post-sitemap.xml
Sitemap: $siteUrl/page-sitemap.xml
TXT
        );
    }
}