<?php

namespace LittledTests\DataProvider\PageContent;


class PageUtilsDataProvider
{
    public static function formatDateDataProvider(): array
    {
        return array(
            array(new FormatDateTestData('2024-01-15', 'M j, Y', 'Jan 15, 2024')),
            array(new FormatDateTestData('2024-01-15', 'm/d/Y', '01/15/2024')),
            array(new FormatDateTestData('January 15,2024', 'Y-m-d', '2024-01-15')),
            array(new FormatDateTestData('Jan. 15, 2024', 'Y-m-d', '2024-01-15')),
            array(new FormatDateTestData('01/15/2024', 'Y-m-d', '2024-01-15')),
        );
    }

}