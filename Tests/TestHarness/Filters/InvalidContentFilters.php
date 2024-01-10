<?php
namespace LittledTests\Filters\Samples;

use Littled\Filters\ContentFilters;

class InvalidContentFilters extends ContentFilters
{
    protected const INVALID_CONTENT_TYPE_ID = 145;

    public static function CONTENT_TYPE_ID(): int { return self::INVALID_CONTENT_TYPE_ID; }

}