<?php

namespace LittledTests\Exceptions;


use Littled\Exception\NotInitializedException;
use PHPUnit\Framework\TestCase;

class NotInitializedExceptionTest extends TestCase
{
    public function testGetMessage()
    {
        $msg = 'Something was not initialized.';
        try {

            throw new NotInitializedException($msg);
        }
        catch (NotInitializedException $e) {
            $expected = '/^'.preg_quote(NotInitializedException::class).'.* '.preg_quote($msg).'/';
            self::assertEquals($msg, $e->getMessage());
            self::assertMatchesRegularExpression($expected, $e);
        }
    }
}