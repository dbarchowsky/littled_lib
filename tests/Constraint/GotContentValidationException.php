<?php
declare(strict_types=1);
namespace Littled\Tests\Constraint;

use Littled\Exception\ContentValidationException;
use PHPUnit\Framework\Constraint\Constraint;
use Exception;

final class GotContentValidationException extends Constraint
{

	/**
	 * @inheritDoc
	 */
	public function toString(): string
	{
		return 'got validation exception';
	}

	/**
	 * @param mixed $other
	 * @return bool
	 * @throws Exception
	 */
	protected function matches($other): bool
	{
		try {
			if (method_exists($other, 'validate')) {
				$other->validate();
			}
			elseif (method_exists($other, 'validateInput')) {
				$other->validateInput();
			}
			else {
				throw new Exception("Validation method not available.");
			}
		}
		catch(ContentValidationException $e) {
			return true;
		}
		return false;
	}
}