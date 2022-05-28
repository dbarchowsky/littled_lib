<?php
namespace Littled\Tests\Request;

use Littled\Exception\ContentValidationException;
use Littled\Request\EmailTextField;
use Littled\Request\StringTextField;
use PHPUnit\Framework\TestCase;
use Littled\Database\MySQLConnection;


/**
 * Class EmailTextField
 * @package Littled\Tests\Request
 */
class EmailTextFieldTest extends TestCase
{
	const MISSING_EMAIL_VALIDATION_MSG = 'Test email is required.';
	const INVALID_EMAIL_VALIDATION_MSG = 'Test email is not in a recognized email format.';

	/** @var EmailTextField Test EmailTextField object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;

	public function setUp() : void
	{
		$this->obj = new EmailTextField("Test email", 'p_email');
		$this->conn = new MySQLConnection();
	}

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->conn->closeDatabaseConnection();
    }

    public function testConstructor()
	{
		$obj = new EmailTextField("Label", "key", false, "dbarchowsky@gmail.com", 200, 4);
		$this->assertEquals("Label", $obj->label);
		$this->assertEquals("key", $obj->key);
		$this->assertFalse($obj->required);
		$this->assertEquals("dbarchowsky@gmail.com", $obj->value);
		$this->assertEquals(200, $obj->size_limit);
		$this->assertEquals(4, $obj->index);
	}

	public function testSetTemplateFilename()
	{
		$email_template_path = '/path/to/email-template.php';
		$text_template_path = '/path/to/text-template.php';

		EmailTextField::setTemplateFilename($email_template_path);
		$this->assertEquals($email_template_path, EmailTextField::getTemplateFilename());

		StringTextField::setTemplateFilename($text_template_path);
		$this->assertEquals($email_template_path, EmailTextField::getTemplateFilename());
		$this->assertEquals($text_template_path, StringTextField::getTemplateFilename());
	}

	public function testValidateNotRequired()
	{
		$this->obj->required = false;
		$this->obj->validate();
        $this->assertFalse($this->obj->has_errors);
	}

	public function testValidateDefaultValue()
	{
		$this->obj->required = true;
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateNullValue()
	{
		$this->obj->required = true;
		$this->obj->value = null;
		self::expectException(ContentValidationException::class);
		$this->obj->validate();
	}

	public function testValidateEmptyStringRequired()
	{
		$this->obj->required = true;
		$this->obj->value = "";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::MISSING_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateEmptyStringNotRequired()
	{
		$this->obj->required = false;
		$this->obj->value = "";
		try {
			$this->obj->validate();
			self::assertEquals('Validation ok.', 'Validation ok.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals('', 'Content validation exception thrown.');
		}
	}

	public function testValidateBlankStringRequired()
	{
		$this->obj->required = true;
		$this->obj->value = " ";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::MISSING_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateIntegerValue()
	{
		$this->obj->required = true;
		$this->obj->value = 43;
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::MISSING_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateMissingDomainRequired()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowsky";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::INVALID_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateMissingDomainNotRequired()
	{
		$this->obj->required = false;
		$this->obj->value = "dbarchowsky";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::INVALID_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateMissingTLDAndPeriodRequired()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowsky@gmail";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::INVALID_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateMissingTLDAndPeriodNotRequired()
	{
		$this->obj->required = false;
		$this->obj->value = "dbarchowsky@gmail";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::INVALID_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateMissingAtSign()
	{
		$this->obj->required = true;
		$this->obj->value = "gmail.com";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::INVALID_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateMissingName()
	{
		$this->obj->required = true;
		$this->obj->value = "@gmail.com";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::INVALID_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateMissingTLD()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowksy@gmail.";
		try {
			$this->obj->validate();
			self::assertEquals('', 'Content validation exception not thrown.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals(self::INVALID_EMAIL_VALIDATION_MSG, $ex->getMessage());
		}
	}

	public function testValidateValidEmails()
	{
		$this->obj->required = true;
		$this->obj->value = "dbarchowsky@gmail.com";
		try {
			$this->obj->validate();
			self::assertEquals('Validation ok.', 'Validation ok.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals('', 'Content validation exception found.');
		}

		$this->obj->value = "dbar.chowsky@gmail.com";
		try {
			$this->obj->validate();
			self::assertEquals('Validation ok.', 'Validation ok.');
		}
		catch (ContentValidationException $ex) {
			self::assertEquals('', 'Content validation exception found.');
		}
	}
}