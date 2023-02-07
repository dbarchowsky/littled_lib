<?php
namespace Littled\Tests\TestHarness\Account;

use Littled\Account\UserAccount;
use Littled\Exception\ContentValidationException;
use Littled\Log\Log;


class UserAccountTestHarness extends UserAccount
{
	/**
	 * @throws ContentValidationException
	 */
	public function validateUsername(): void
	{
		throw new ContentValidationException(Log::getShortMethodName()."Not implemented.");
	}
}