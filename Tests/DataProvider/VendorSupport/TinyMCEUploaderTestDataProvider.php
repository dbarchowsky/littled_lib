<?php

namespace Littled\Tests\DataProvider\VendorSupport;

use Littled\Exception\InvalidValueException;

class TinyMCEUploaderTestDataProvider
{
    public static function formatTargetPathTestProvider(): array
    {
        return array(
            array(
                '/images/ideas/2023/03/', '',
                '/var/www/html/images/ideas/2023/03/',
                '/images/ideas/'),
            array(
                '/images/ideas/', '',
                '/var/www/html/images/ideas/',
                '/images/ideas/'),
            array('/images/ideas/2023/03/', '',
                '/var/www/html/images/ideas/2023/03/',
                '/images/ideas'),
            array(
                '/images/ideas/2023/03/', '',
                '/var/www/html/images/ideas/2023/03',
                '/images/ideas/'),
            array(
                '/images/ideas/', '',
                '/var/www/html/images/ideas',
                '/images/ideas/'),
            array(
                '/images/ideas/', '',
                '/var/www/html/images/ideas/',
                '/images/ideas'),
            array(
                '', InvalidValueException::class,
                '/var/www/html/images/articles/',
                '/images/ideas'),
            array(
                '', InvalidValueException::class,
                '/var/www/html/path/one/way/',
                '/some/other/path/'),
        );
    }
}