<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Exception\ContentValidationException;
use Littled\Request\PhoneNumberTextField;
use Littled\Tests\TestExtensions\ContentValidationTestCase;

class PhoneNumberTextFieldTest extends ContentValidationTestCase
{
	/**
	 * @throws ContentValidationException
	 */
	function testValidateNotRequiredNumber()
	{
		$ptf = new PhoneNumberTextField("Phone Number", "phone");
		$ptf->setAsNotRequired();

		// not required & no value = no validation error
		$this->assertNull($ptf->validate());

		// bad number (4-digits)
		$ptf->value = "3452";
		$this->assertContentValidationException($ptf);

		// bad format (7 digits)
		$ptf->value = "5653452";
		$this->assertContentValidationException($ptf);

		// good phone number (10 digits, no space)
		$ptf->value = "3105653452";
		$this->assertNull($ptf->validate());

		// bad phone number (contains alphanumeric)
		$ptf->value = "3105b53452";
		$this->assertContentValidationException($ptf);

		// good phone number (10 digits with dashes)
		$ptf->value = "310-565-3452";
		$this->assertNull($ptf->validate());

		// bad phone number (10 digits with misplaced dashes)
		$ptf->value = "310-5653-452";
		$this->assertContentValidationException($ptf);

		// good phone number (10 digits with parentheses)
		$ptf->value = "(310) 565-3452";
		$this->assertNull($ptf->validate());

		// good phone number (10 digits with periods)
		$ptf->value = "310.565.3452";
		$this->assertNull($ptf->validate());

		// good phone number (10 digits with spaces)
		$ptf->value = "310 565 3452";
		$this->assertNull($ptf->validate());

		// bad phone number (10 digits with other characters)
		$ptf->value = "310_565_3452";
		$this->assertContentValidationException($ptf);
	}
}