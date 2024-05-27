<?php

$xmlContent = file_get_contents(__DIR__. '/build/sitemap.xml');

// Remove newlines, tabs, and extra spaces to make it a single line
$xmlContent = str_replace(array("\n", "\r", "\t"), '', $xmlContent);
$xmlContent = preg_replace('/>\s+</', '><', $xmlContent);

file_put_contents(__DIR__. '/build/sitemap.xml', $xmlContent);