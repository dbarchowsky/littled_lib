<?php
namespace Littled\Tests\DataProvider\Request;

use Littled\Exception\ContentValidationException;
use Littled\Tests\DataProvider\Request\StringSelect\ValidateTestData;
use Littled\Tests\DataProvider\Request\StringSelect\ValidateTestExpectations;

class CategorySelectTestDataProvider
{
    public static function validateInputTestProvider(): array
    {
        return array_map(function($e) { return array($e); }, array(
            new ValidateTestData(
                new ValidateTestExpectations(
                    ContentValidationException::class,
                    '/is required/i',
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