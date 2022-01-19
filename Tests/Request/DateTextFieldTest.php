<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Database\MySQLConnection;
use Littled\Request\DateTextField;
use PHPUnit\Framework\TestCase;

/**
 * Class DateTextFieldTest
 * @package Littled\Tests\Request
 */
class DateTextFieldTest extends TestCase
{
    /** @var DateTextField Test DateTextField object. */
    public $obj;
    /** @var MySQLConnection Test database connection. */
    public $conn;

    function __construct()
    {
        parent::__construct();
        $this->obj = new DateTextField("Test date", 'dateTest');
        $this->conn = new MySQLConnection();
    }

    public function testFormatFrontFacingValue()
    {
        $this->obj->setInputValue("Oct. 23, 2018");
        $this->assertEquals("10/23/2018", $this->obj->formatFrontFacingValue());

        $this->obj->setInputValue("2000-02-01");
        $this->assertEquals("2/1/2000", $this->obj->formatFrontFacingValue());
    }

    public function testFormatFrontFacingValueUsingEmptyString()
    {
        $this->obj->value = '';
        $this->assertEquals('', $this->obj->formatFrontFacingValue());

        $this->obj->value = null;
        $this->assertEquals('', $this->obj->formatFrontFacingValue());
    }

    public function testFormatFrontFacingValueWithInvalidDate()
    {
        $this->obj->value = '29673-928374-837464';
        $this->assertEquals('29673-928374-837464', $this->obj->formatFrontFacingValue());
    }
}