<?php
namespace Littled\Tests\DataProvider\Request\StringSelect;


class CollectRequestDataSingleTestDataProvider
{
    public static function testProvider(): array
    {
        return array_map(
            function(CollectRequestDataSingleTestData $e){
                return $e->mapTestProvider();
            },
            array(
                new CollectRequestDataSingleTestData(
                    '',
                    'pKey',
                    []),
                new CollectRequestDataSingleTestData(
                    'foo',
                    'pKey',
                    array('pKey' => 'foo')),
                new CollectRequestDataSingleTestData(
                    'bar',
                    'pKey',
                    array('pKey' => ['bar'])),
                new CollectRequestDataSingleTestData(
                    'biz',
                    'pKey',
                    array('pKey' => ['biz', 'bash'])),
            ));
    }
}