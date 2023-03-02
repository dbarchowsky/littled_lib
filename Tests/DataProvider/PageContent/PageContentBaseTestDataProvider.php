<?php
namespace Littled\Tests\DataProvider\PageContent;


class PageContentBaseTestDataProvider
{
    public static function getQueryStringTestProvider(): array
    {
        return array(
            array(new PageContentBaseTestData(
                array(
                    'intFilter=45',
                    'name=foo',
                    'boolFilter=1',
                    'dateBefore='.urlencode('04/25/2003')
                ),
                array(
                    'intFilter' => 45,
                    'name' => 'foo',
                    'boolFilter' => true,
                    'dateBefore' => '4/25/2003')
            )),
            array(new PageContentBaseTestData(
                array(
                    'intFilter=288',
                    'name=bar',
                    'boolFilter=0',
                    'dateBefore='.urlencode('01/06/2018')
                ),
                array(
                    'intFilter' => 288,
                    'name' => 'bar',
                    'boolFilter' => false,
                    'dateBefore' => '1/6/2018'), [],
                true
            )),
        );
    }

    public static function formatQueryStringTestProvider(): array
    {
        return array(
            array(new PageContentBaseTestData(
                array(
                    'intFilter=45',
                    'name=foo',
                    'boolFilter=1',
                    'dateBefore='.urlencode('04/25/2003'),
                ),
                array(
                    'intFilter' => 45,
                    'name' => 'foo',
                    'boolFilter' => true,
                    'dateBefore' => '4/25/2003',
                )
            )),
            array(new PageContentBaseTestData(
                array(
                    'intFilter=45',
                    'boolFilter=1',
                ),
                array(
                    'intFilter' => 45,
                    'name' => 'foo',
                    'boolFilter' => true,
                    'dateBefore' => '4/25/2003',
                )
            )),
            array(new PageContentBaseTestData(
                array(
                    'intFilter=45',
                    'boolFilter=1',
                ),
                array(
                    'intFilter' => 45,
                    'name' => '',
                    'boolFilter' => true,
                    'dateBefore' => '4/25/2003',
                ), [], false,
                array('name')
            )),
        );
    }
}