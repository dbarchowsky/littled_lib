<?php

namespace Littled\Tests\DataProvider\PageContent\Metadata;

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
            [new MetadataElement('name', 'ROBOTS', 'NOARCHIVE'),
                '/^<me'.'ta name=\"ROBOTS\" content=\"NOARCHIVE\" \/>\n$/'],
            [new MetadataElement('name', 'MSSmartTagsPreventParsing', 'content-true'),
                '/^<me'.'ta name=\"MSSmartTagsPreventParsing\" content=\"content-true\" \/>\n$/'],
            ];
    }
}