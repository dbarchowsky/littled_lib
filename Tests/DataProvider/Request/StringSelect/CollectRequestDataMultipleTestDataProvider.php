<?php
namespace LittledTests\DataProvider\Request\StringSelect;

class CollectRequestDataMultipleTestDataProvider
{
    public static function testProvider(): array
    {
        return array_map(
            function(CollectRequestDataMultipleTestData $e){
                return $e->mapTestProvider();
            },
            array(
                new CollectRequestDataMultipleTestData([], 'pKey', []),
                new CollectRequestDataMultipleTestData(
                    array('foo' => 'foo', 'bar' => 'bar', 'biz' => 'biz'),
                    'testKey',
                    array('testKey' => array('foo' => 'foo', 'bar' => 'bar', 'biz' => 'biz'))),
                new CollectRequestDataMultipleTestData(
                    array('0' => 'foo', '1' => 'bar', '2' => 'biz'),
                    'testKey',
                    array('testKey' => array('0' => 'foo', '1' => 'bar', '2' => 'biz'))),
                new CollectRequestDataMultipleTestData(
                    array('a' => 'a', 'b' => 'b', 'c' => 'c'),
                    'testKey',
                    array('testKey' => array('0' => 'foo', '1' => 'bar', '2' => 'biz')),
                    array('testKey' => array('a' => 'a', 'b' => 'b', 'c' => 'c'))),
                new CollectRequestDataMultipleTestData(
                    array('foo' => 'foo', 'biz' => 'biz'),
                    'testKey',
                    array('testKey' => array('foo' => 'foo', 'bar' => '', 'biz' => 'biz'))),
                new CollectRequestDataMultipleTestData(
                    array('foo' => 'foo', 'biz' => '26'),
                    'testKey',
                    array('testKey' => array('foo' => 'foo', 'biz' => 26))),
            ));
    }
}