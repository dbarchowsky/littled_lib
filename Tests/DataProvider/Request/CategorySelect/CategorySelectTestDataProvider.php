<?php
namespace Littled\Tests\DataProvider\Request\CategorySelect;

use Littled\Exception\ContentValidationException;
use Littled\Tests\DataProvider\Request\StringSelect\ValidateTestData;
use Littled\Tests\DataProvider\Request\StringSelect\ValidateTestExpectations;

class CategorySelectTestDataProvider
{
    public static function collectRequestDataTestProvider(): array
    {
        return array_map(
            function($e) { return array($e); }, array(
                new CollectRequestDataTestData(
                    new CollectRequestDataTestExpectations(array('a', 'b'), array('a', 'b')),
                    array('catTerm' => ['a', 'b']),
                    true
                ),
                new CollectRequestDataTestData(
                    new CollectRequestDataTestExpectations(['c'], 'c'),
                    array('catTerm' => ['c', 'd', 'e']),
                    false
                ),
                new CollectRequestDataTestData(
                    new CollectRequestDataTestExpectations(['new term'], '', 'new term'),
                    array('catTerm' => [], 'catNew' => 'new term'),
                    false
                ),
                new CollectRequestDataTestData(
                    new CollectRequestDataTestExpectations(['foo'], [], 'foo'),
                    array('catTerm' => [], 'catNew' => 'foo'),
                    true
                ),
                new CollectRequestDataTestData(
                    new CollectRequestDataTestExpectations(['de', 'ee', 'fe', 'he'], ['de', 'ee', 'fe'], 'he'),
                    array('catTerm' => ['de', 'ee', 'fe'], 'catNew' => 'he'),
                    true
                ),
                new CollectRequestDataTestData(
                    new CollectRequestDataTestExpectations([], [], ''),
                    array('catTerm' => '', 'catNew' => ''),
                    true
                ),
            )
        );
    }

    public static function validateInputTestProvider(): array
    {
        return array_map(function($e) { return array($e); }, array(
            new ValidateTestData(
                new ValidateTestExpectations(
                    ContentValidationException::class,
                    '/^Category is required\.$/i',
                    0),
                true,
                true,
                'catTerm',
                array (
                    'catTerm' => [],
                    'catNew' => ''
                )),
            new ValidateTestData(
                new ValidateTestExpectations('', '', 3),
                true,
                true,
                'catTerm',
                array (
                    'catTerm' => ['foo', 'bar', 'biz'],
                    'catNew' => ''
                )
            ),
            new ValidateTestData(
                new ValidateTestExpectations('', '', 0),
                true,
                true,
                'catTerm',
                array (
                    'catTerm' => [],
                    'catNew' => 'new term'
                )
            ),
        ));
    }
}