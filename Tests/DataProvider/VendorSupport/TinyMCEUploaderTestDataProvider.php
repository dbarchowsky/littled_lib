<?php

namespace LittledTests\DataProvider\VendorSupport;

use Littled\Exception\InvalidValueException;

class TinyMCEUploaderTestDataProvider
{
    public static function formatTargetPathTestProvider(): array
    {
        return array(
            array(
                '/images/ideas/2023/03/image.jpg', '',
                '/var/www/html/images/ideas/2023/03/image.jpg',
                '/images/ideas/'),
            array(
                '/images/ideas/image.jpg', '',
                '/var/www/html/images/ideas/image.jpg',
                '/images/ideas/'),
            array('/images/ideas/2023/03/image.jpg', '',
                '/var/www/html/images/ideas/2023/03/image.jpg',
                '/images/ideas'),
            array(
                '/images/ideas/image.jpg', '',
                '/var/www/html/images/ideas/image.jpg',
                '/images/ideas'),
            array(
                '', InvalidValueException::class,
                '/var/www/html/images/articles/image.jpg',
                '/images/ideas'),
            array(
                '', InvalidValueException::class,
                '/var/www/html/path/one/way/image.jpg',
                '/some/other/path/'),
        );
    }
}