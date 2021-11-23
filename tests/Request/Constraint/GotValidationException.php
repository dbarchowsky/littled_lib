<?php
declare(strict_types=1);
namespace Littled\Tests\Request\Constraint;

use Littled\Exception\ContentValidationException;
use PHPUnit\Framework\Constraint\Constraint;

final class GotValidationException extends Constraint
{

	/**
	 * @inheritDoc
	 */
	public function toString(): string
	{
		return 'got validation exception';
	}

	protected function matches($other): bool
	{
		try {
			$other->validate();
		}
		catch(ContentValidationException $e) {
			return true;
		}
		return false;
	}
}