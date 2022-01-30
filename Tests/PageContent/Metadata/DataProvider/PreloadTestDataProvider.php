<?php

namespace Littled\Tests\PageContent\Metadata\DataProvider;

use Littled\PageContent\Metadata\Preload;

class PreloadTestDataProvider
{
    public static function renderTestProvider(): array
    {
        return [
            [new Preload('link', 'icon', 'https://localhost/favicon.ico', array('type' => 'image/x-icon')),
                '/^<li'.'nk rel=\"icon\" href=\"https:\/\/localhost\/favicon\.ico\" type=\"image\/x-icon\" \/>\n$/'],
            [new Preload('link', 'preconnect', 'https://fonts.googleapis.com'),
                '/^<li'.'nk rel=\"preconnect\" href=\"https:\/\/fonts\.googleapis\.com" \/>\n$/'],
            [new Preload('link', 'preconnect', 'https://fonts.gstatic.com', array('crossorigin' => null)),
                '/^<li'.'nk rel=\"preconnect\" href=\"https:\/\/fonts\.gstatic\.com" crossorigin \/>\n$/'],
            [new Preload('link', 'alternate', 'https://mysite.com/blog/?feed=rss2', array('type' => 'application/rss+xml', 'title' => 'my blog')),
                '/^<li'.'nk rel=\"alternate\" href=\"https:\/\/mysite\.com\/blog\/\?feed=rss2\" type=\"application\/rss\+xml\" title=\"my blog\" \/>\n$/'],
            ];
    }
}