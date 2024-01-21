<?php

namespace LittledTests\TestExtensions;

use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;
use LittledTests\Constraint\GotContentValidationException;
use PHPUnit\Framework\TestCase;

class ContentValidationTestCase extends TestCase
{
    public static function assertContentValidationException($condition, string $message=''): void
	{
		static::assertThat($condition, static::gotContentValidationException(), $message);
	}

	public static function gotContentValidationException(): GotContentValidationException
	{
		return new GotContentValidationException;
	}

    /**
     * @param string $expected_exception
     * @param $value
     * @param RequestInput $input
     * @return void
     */
    protected function _testValidate(string $expected_exception, $value, RequestInput $input)
    {
        if ($value !== '[use default]') {
            $input->value = $value;
        }
        try {
            $input->validate();
            self::assertEquals($expected_exception, $input->has_errors);
        }
        catch(ContentValidationException $e) {
            if ($expected_exception==='') {
                self::fail('Expected validation to pass, caught unexpected ' . get_class($e) . '.');
            }
            self::assertMatchesRegularExpression($expected_exception, $e);
        }
    }
}