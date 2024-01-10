<?php

namespace LittledTests\TestExtensions;

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
}