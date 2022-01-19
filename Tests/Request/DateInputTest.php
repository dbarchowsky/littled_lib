<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Request\DateInput;
use PHPUnit\Framework\TestCase;
use Exception;
use mysqli;

class DateInputTest extends TestCase
{
    /** @var DateInput Test DateInput object. */
    public $obj;
    /** @var MySQLConnection Test database connection. */
    public $conn;

    function __construct()
    {
        parent::__construct();
        $this->obj = new DateInput("Test date", 'p_date');
        $this->conn = new MySQLConnection();
    }

    /**
     * @throws ContentValidationException
     */
    public function testValidateValidValues()
    {
        $this->obj->required = true;
        $this->obj->value = '1/15/2018';
        $this->obj->validate();
        $this->assertEquals('2018-01-15 00:00:00', $this->obj->value);

        $this->obj->value = '12/31/1999';
        $this->obj->validate();
        $this->assertEquals('1999-12-31 00:00:00', $this->obj->value);

        $this->obj->value = '1/1/2094';
        $this->obj->validate();
        $this->assertEquals('2094-01-01 00:00:00', $this->obj->value);

        $this->obj->value = '2018-02-14';
        $this->obj->validate();
        $this->assertEquals('2018-02-14 00:00:00', $this->obj->value);

        $this->obj->value = '01/01/2004';
        $this->obj->validate();
        $this->assertEquals('2004-01-01 00:00:00', $this->obj->value);

        $this->obj->value = '11/01/2020';
        $this->obj->validate();
        $this->assertEquals('2020-11-01 00:00:00', $this->obj->value);

	    $this->obj->value = '11-1-2020';
	    $this->obj->validate();
	    $this->assertEquals('2020-01-11 00:00:00', $this->obj->value);

	    $this->obj->value = 'May 13, 1980';
	    $this->obj->validate();
	    $this->assertEquals('1980-05-13 00:00:00', $this->obj->value);
    }

    public function testSetInputValue()
    {
    	$m = date('m');
    	$d = date('d');

    	$this->obj->setInputValue('');
    	$this->assertEquals('', $this->obj->value);

	    $this->obj->setInputValue('1/1/2018');
	    $this->assertEquals('2018-01-01', $this->obj->value);

	    $this->obj->setInputValue('2018-01-01');
	    $this->assertEquals('2018-01-01', $this->obj->value);

	    $this->obj->setInputValue('June 13, 1969');
	    $this->assertEquals('1969-06-13', $this->obj->value);

	    $this->obj->setInputValue('dfdfdfd');
	    $this->assertEquals('', $this->obj->value);

	    $this->obj->setInputValue('7269');
	    $this->assertEquals("7269-$m-$d", $this->obj->value);
    }

    public function testValidateMissingDateValue()
    {
        $this->obj->required = true;
        try {
            $this->obj->validate();
        }
        catch(ContentValidationException $ex) {
            $this->assertEquals("Test date is required.", $ex->getMessage());
        }

        $this->obj->value = null;
        try {
            $this->obj->validate();
        }
        catch(ContentValidationException $ex) {
            $this->assertEquals("Test date is required.", $ex->getMessage());
        }

        $this->obj->value = '';
        try {
            $this->obj->validate();
        }
        catch(ContentValidationException $ex) {
            $this->assertEquals("Test date is required.", $ex->getMessage());
        }
    }

    public function testValidateValueSize()
    {
        $str = str_repeat("a", $this->obj->sizeLimit + 1);
        $this->obj->value = $str;
        try {
            $this->obj->validate();
        }
        catch(ContentValidationException $ex) {
            $this->assertEquals("{$this->obj->label} is limited to {$this->obj->sizeLimit} characters.", $ex->getMessage());
        }
    }

    public function testValidateInvalidDateFormats()
    {
        $this->obj->required = false;
        $this->obj->value = '32-2-2018';
        try {
            $this->obj->validate();
        }
        catch (ContentValidationException $ex) {
            $this->assertEquals("Test date is not in a recognized date format.", $ex->getMessage());
        }

	    $this->obj->value = 'duygoci';
	    try {
		    $this->obj->validate();
	    }
	    catch (ContentValidationException $ex) {
		    $this->assertEquals("Test date is not in a recognized date format.", $ex->getMessage());
	    }
    }

    /**
     * @throws Exception
     */
    public function testEscapeSQL()
    {
        if (!defined('MYSQL_HOST') ||
            !defined('MYSQL_USER') ||
            !defined('MYSQL_PASS') ||
            !defined('MYSQL_SCHEMA') ||
            !defined('MYSQL_PORT')) {
            throw new Exception('Databae connection properties not defined.');
        }
	    $mysqli = new mysqli();
	    $mysqli->connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_SCHEMA, MYSQL_PORT);

    	$this->obj->setInputValue('May 23, 2018');
    	$this->assertEquals("'2018-05-23 00:00:00'", $this->obj->escapeSQL($mysqli));

	    $this->obj->value = null;
	    $this->assertEquals('NULL', $this->obj->escapeSQL($mysqli));

	    $this->obj->setInputValue('');
	    $this->assertEquals('NULL', $this->obj->escapeSQL($mysqli));

	    $this->obj->value = "fdoclxps";
	    $this->assertEquals("'fdoclxps'", $this->obj->escapeSQL($mysqli));
    }

    public function testValidateWhenNotRequired()
    {
    	$error_msg = '';
    	$this->obj->required = false;
    	$this->obj->value = null;
    	try {
		    $this->obj->validate();
	    }
    	catch(ContentValidationException $ex) {
    		$error_msg = $ex->getMessage();
	    }
	    $this->assertEquals('', $error_msg);

    	$this->obj->value = '';
	    try {
		    $this->obj->validate();
	    }
	    catch(ContentValidationException $ex) {
		    $error_msg = $ex->getMessage();
	    }
	    $this->assertEquals('', $error_msg);
    }
}