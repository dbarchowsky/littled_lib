<?php

namespace LittledTests\DataProvider\Filters\FilterCollection;

class FormatQueryStringTestDataProvider
{
    public static function formatQueryStringTestProvider(): array
    {
        return array(
            array(new FormatQueryStringTestData(
                new FormatQueryStringTestExpectations(array(
                    'nameFilter=foo',
                    'intFilter=83',
                    'boolFilter=0',
                    'dateAfter='.urlencode('1/30/2022'))),
                array(
                    'name_filter' => 'foo',
                    'int_filter' => 83,
                    'bool_filter' => false,
                    'date_after' => '1/30/2022')

            )),
            array(new FormatQueryStringTestData(
                new FormatQueryStringTestExpectations(
                    array('boolFilter=1'),
                    array('nameFilter=')),
                array('bool_filter' => true)
            )),
            array(new FormatQueryStringTestData(
                new FormatQueryStringTestExpectations(
                    array('nameFilter=biz'),
                    array('boolFilter=')),
                array('name_filter' => 'biz')
            )),
        );
    }


}