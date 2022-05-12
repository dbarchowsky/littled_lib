<?php
namespace Littled\Tests\Validation\DataProvider;

use Littled\Validation\Validation;

class ValidationTestHarness extends Validation
{
    public static function publicGetClientIP(): string
    {
        return Validation::getClientIP();
    }
}