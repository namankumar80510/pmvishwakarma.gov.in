<?php

namespace App\Commands;

use App\Lib\Post\FetchPost;
use Dibi\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nette\Utils\FileSystem;
use Spekulatius\PHPScraper\PHPScraper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteBuildCommand extends Command
{

    public function getName(): ?string
    {
        return "site:build";
    }

    public function getDescription(): string
    {
        return "Build the site";
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $config = require_once ROOT_DIR . 'config.php';

        if (getenv('APP_ENV') === 'dev') {
            $siteUrl = getenv('SITE_URL');
        } else {
            $siteUrl = $config['site_url'];
        }

        $wpUrl = getenv('WP_URL');
        $siteBasename = basename($siteUrl);
        $jsonEncodedSiteUrl = str_replace('"', '', json_encode($siteUrl));

        $assets = $config['assets'];

        $output->writeln("Building the site...");

        // Build the site
        $httpclient = new Client([
            'verify' => false
        ]);
        $scraper = new PHPScraper([
            'disable_ssl' => true
        ]);
        $posts = (new FetchPost())->getAllPosts();

        $arr = [];
        foreach ($posts as $post) {
            $slug = ($post->post_name === 'homepage') ? '' : $post->post_name . "/";
            $arr[] = wpUrl($slug);
        }

        if (file_exists(BUILD_DIR)) {
            FileSystem::delete(BUILD_DIR);
        }

        foreach ($arr as $url) {
            $scraper->go($url);

            $html = '<!DOCTYPE html><html lang="en-US">' . $scraper->filter('html')->html() . '</html>';
            $html = str_replace($wpUrl, $siteUrl, $html);
            $html = str_replace(
                str_replace('"', '', json_encode($wpUrl)),
                $jsonEncodedSiteUrl,
                $html
            );
            $html = str_replace(
                '</body>',
                '<script src="/static/htmx.js"></script></body>',
                $html
            );
            $html = str_replace(
                '<body',
                '<body hx-boost="true"',
                $html
            );
            // Define the patterns to match the <link> tags
            $patterns = [
                '/<link rel="EditURI" type="application\/rsd\+xml" title="RSD" href="' . $jsonEncodedSiteUrl . '\/xmlrpc\.php\?rsd">/',
                '/<link rel="alternate" type="application\/json\+oembed" href="' . $jsonEncodedSiteUrl . '\/wp-json\/oembed\/1\.0\/embed\?url=.*?">/',
                '/<link rel="alternate" type="text\/xml\+oembed" href="' . $jsonEncodedSiteUrl . '\/wp-json\/oembed\/1\.0\/embed\?url=.*?&amp;format=xml">/',
                '/<link rel="https:\/\/api\.w\.org\/" href="' . $jsonEncodedSiteUrl . '\/wp-json\/" \/>/',
                '/<link rel="alternate" type="application\/json" href="' . $jsonEncodedSiteUrl . '\/wp-json\/wp\/v2\/pages\/.*?" \/>/'
            ];

            $replacements = [
                '',
                '',
                '',
                '',
                ''
            ];

            $html = preg_replace($patterns, $replacements, $html);

            // remove query strings from css and js
            $pattern = '/(<(?:link|script)[^>]+(?:href|src)=["\'])([^"\']+\.(?:css|js))\?([^"\']*)(["\'][^>]*>)/i';
            $replacement = '$1$2$4'; // Keep the capture groups for the tag, URL, and closing quote.

            $html = preg_replace($pattern, $replacement, $html);

            // remove query string from wp emoji
            $pattern = '/wp-emoji-release\.min\.js\?ver=[^"\']+/i';
            $replacement = '/wp-emoji-release.min.js';

            $html = preg_replace($pattern, $replacement, $html);

            // remove version query from js
            $pattern = '/interactivity\.min\.js\?ver=[^"\']+/i'; // Matches the script with version query
            $replacement = '/interactivity.min.js'; // Replace with the script without version query

            $html = preg_replace($pattern, $replacement, $html);

            $basename = basename($url);

            if ($basename === basename($wpUrl)) {
                $basename = '';
            } else {
                $basename .= '/';
            }

            FileSystem::write(BUILD_DIR . $basename . 'index.html', $html);

            $output->writeln('page added: ' . basename($url) . '!');
        }

        foreach ($assets as $asset) {

            $content = $httpclient->request('GET', wpUrl($asset))
                ->getBody()->getContents();
            $content = str_replace($wpUrl, $siteUrl, $content);

            FileSystem::write(
                BUILD_DIR . $asset,
                $content
            );

            $output->writeln('asset added: ' . $asset . '!');

        }

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

        $output->writeln("Robots.txt added!");

        // assets
        $uploadsDir = ROOT_DIR . rtrim(getenv('WP_DIR'), DS) . DS . 'wp-content/uploads';
        if (file_exists($uploadsDir)) {
            FileSystem::copy(
                $uploadsDir,
                BUILD_DIR . 'wp-content/uploads'
            );
            $output->writeln("Uploads directory copied!");
        }

        $cacheDir = ROOT_DIR . rtrim(getenv('WP_DIR'), DS) . DS . 'wp-content/cache';
        if (file_exists($cacheDir)) {
            FileSystem::copy(
                $cacheDir,
                BUILD_DIR . 'wp-content/cache'
            );
            $output->writeln("Cache directory copied!");
        }

        FileSystem::copy(
            ROOT_DIR . 'static',
            BUILD_DIR . 'static'
        );
        $output->writeln("Static files copied!");

        $output->writeln("Site built!");

        return 0;
    }

}