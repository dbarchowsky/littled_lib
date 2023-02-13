<?php
namespace Littled\Tests\TestHarness\Validation;

use Littled\Validation\Validation;


class ValidationTestHarness extends Validation
{
    public static function publicGetDefaultInputSource(array $ignore_keys=[]): array
    {
        return parent::publicGetDefaultInputSource($ignore_keys);
    }

    public static function parseInput_Public( int $filter, string $key, ?int $index=null, ?array $src=null )
    {
        return static::_parseInput($filter, $key, $index, $src);
    }

    public static function publicGetClientIP(): string
    {
        return Validation::getClientIP();
    }
}