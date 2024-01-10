<?php
namespace LittledTests\TestHarness\Validation;

use Littled\Validation\Validation;


class ValidationTestHarness extends Validation
{
    /**
     * @inheritDoc
     * Public interface for tests
     */
    public static function checkSourceValue(?array &$src, string $key): bool
    {
        return parent::checkSourceValue($src, $key);
    }

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