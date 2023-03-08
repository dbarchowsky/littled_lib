<?php
namespace Littled\Tests\DataProvider\Validation;

class CheckSourceValueTestDataProvider
{
    public static function checkSourceValueTestProvider(): array
    {
        return array(
            array(new CheckSourceValueTestData(
                new CheckSourceValueTestExpectations(true, 'pKey', 'pValue'),
                'pKey',
                array('foo' => 'bar', 'pKey' => 'pValue', 'biz' => 'bash')
            )),
            array(new CheckSourceValueTestData(
                new CheckSourceValueTestExpectations(true, 'pKey', 'custom value'),
                'pKey',
                array('foo' => 'bar', 'pKey' => 'pValue', 'biz' => 'bash'),
                array('foo' => 'rab', 'pKey' => 'custom value')
            )),
            array(new CheckSourceValueTestData(
                new CheckSourceValueTestExpectations(false, 'pKey'),
                'pKey',
                array('foo' => 'bar', 'pKey' => 'pValue', 'biz' => 'bash'),
                []
            )),
            array(new CheckSourceValueTestData(
                new CheckSourceValueTestExpectations(false, 'pKey'),
                'pKey',
                array('foo' => 'bar', 'biz' => 'bash')
            )),
            array(new CheckSourceValueTestData(
                new CheckSourceValueTestExpectations(true, 'pKey', ''),
                'pKey',
                array('foo' => 'bar', 'biz' => 'bash', 'pKey' => '')
            )),
        );
    }
}