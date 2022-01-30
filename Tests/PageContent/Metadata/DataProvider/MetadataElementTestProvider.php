<?php

namespace Littled\Tests\PageContent\Metadata\DataProvider;

use Littled\PageContent\Metadata\MetadataElement;

class MetadataElementTestProvider
{
    public static function renderTestProvider(): array
    {
        return [
            [new MetadataElement('name', 'title', 'my title'),
                '/^<me'.'ta name=\"title\" content=\"my title\" \/>\n$/'],
            [new MetadataElement('charset', 'xxx', 'metadata-value'),
                '/^<me'.'ta charset=\"xxx\" content=\"metadata-value\" \/>\n$/'],
            ];
    }
}