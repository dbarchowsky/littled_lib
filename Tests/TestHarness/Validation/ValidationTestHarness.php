<?php
namespace Littled\Tests\TestHarness\Validation;

use Littled\Validation\Validation;


class ValidationTestHarness extends Validation
{
    public static function getDefaultInputSource(array $ignore_keys=[]): array
    {
        return parent::getDefaultInputSource($ignore_keys);
    }

    public static function _parseInput(int $filter, string $key, ?int $index=null, ?array $src=null )
    {
        return parent::_parseInput($filter, $key, $index, $src);
    }

    public static function getClientIP(): string
    {
        return parent::getClientIP();
    }
}