<?php
namespace Littled\Tests\Validation\DataProvider;

use Littled\Validation\Validation;

class ValidationTestHarness extends Validation
{
    public static function parseInput_Public( int $filter, string $key, ?int $index=null, ?array $src=null )
    {
        return static::_parseInput($filter, $key, $index, $src);
    }

    public static function publicGetClientIP(): string
    {
        return Validation::getClientIP();
    }
}