<?php

namespace App\Lib\Site;


use App\Lib\Post\FetchPost;
use Dibi\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nette\Utils\FileSystem;
use Spekulatius\PHPScraper\PHPScraper;

class PostsBuilder
{

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function build()
    {
        $siteUrl = site_url();
        $wpUrl = getenv('WP_URL');
        $siteBasename = basename($siteUrl);
        $jsonEncodedSiteUrl = str_replace('"', '', json_encode($siteUrl));

        // Build the site

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
        }
    }

}