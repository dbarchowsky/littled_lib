<?php

namespace LittledTests\PageContent;


use Littled\PageContent\PageUtils;
use LittledTests\DataProvider\PageContent\FormatDateTestData;
use PHPUnit\Framework\TestCase;

class PageUtilsTest extends TestCase
{
    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\PageUtilsDataProvider::formatDateDataProvider()
     * @return void
     */
    public function testFormatDate(FormatDateTestData $data)
    {
        $result = PageUtils::formatDate($data->date, $data->format);
        $this->assertEquals($data->expected, $result, $data->msg);
    }
}