<?php
namespace Littled\Tests\Log;

use Littled\Log\Log;
use PHPUnit\Framework\TestCase;
use Exception;
use Throwable;

class LogTest extends TestCase
{
    /**
     * @dataProvider \Littled\Tests\Log\DataProvider\LogTestDataProvider::displayExceptionMessageTestProvider()
     * @param string $expected
     * @param bool $throw_exception
     * @param bool $is_verbose
     * @param string $custom_error
     * @return void
     */
    function testDisplayExceptionMessage(string $expected, bool $throw_exception=true, bool $is_verbose=false, string $custom_error='')
    {
        try {
            if ($throw_exception) {
                // throw Exception
                throw new Exception("This is the exception message, not a custom message.");
            }
            else {
                // throw Error
                $zero = 0;
                $zero = 1 / $zero;
            }
        }
        catch(Throwable $e) {
            $this->expectOutputRegex($expected);
            Log::displayExceptionMessage($e, $is_verbose, $custom_error);
        }
    }

    function testGetShortMethodName()
    {
        $this->assertEquals('LogTest::testGetShortMethodName', Log::getShortMethodName());
    }
}