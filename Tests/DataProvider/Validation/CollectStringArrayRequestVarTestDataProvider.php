<?php
namespace Littled\Tests\DataProvider\Validation;

class CollectStringArrayRequestVarTestDataProvider
{
    public static function collectStringArrayRequestVarTestProvider(): array
    {
        return array(
            array(new CollectStringArrayRequestVarTestData(
                array('a' => 'a', 'b' => 'b', 'c' => 'c'),
                'pKey',
                array('pKey' => ['a' => 'a', 'b' => 'b', 'c' => 'c']))),
            array(new CollectStringArrayRequestVarTestData(
                array('a' => 'a', 'c' => 'c'),
                'pKey',
                array('pKey' => ['a' => 'a', 'b' => '', 'c' => 'c']))),
            array(new CollectStringArrayRequestVarTestData(
                array('d' => 'd', 'e' => 'e', 'f' => 'f'),
                'pKey',
                array('pKey' => ['a' => 'a', 'b' => 'b', 'c' => 'c']),
                array('pKey' => ['d' => 'd', 'e' => 'e', 'f' => 'f']))),
            array(new CollectStringArrayRequestVarTestData(
                array('0' => 'a', '1' => 'b', '2' => 'c'),
                'pKey',
                array('pKey' => ['0' => 'a', '1' => 'b', '2' => 'c']))),
        );
    }
}