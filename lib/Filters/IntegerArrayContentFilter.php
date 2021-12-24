<?php
namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\Validation\Validation;


/**
 * Class IntegerArrayContentFilter
 * @package Littled\Filters
 */
class IntegerArrayContentFilter extends IntegerContentFilter
{
	/**
	 * collects filter value from request variables (GET or POST).
	 */
	protected function collectRequestValue()
	{
		$this->value = Validation::collectIntegerArrayRequestVar($this->key);
	}

	/**
	 * Output markup that will preserve the filter's value in an HTML form.
	 * @throws ConfigurationUndefinedException
	 * @throws ResourceNotFoundException
	 */
	public function saveInForm()
	{
		if (!is_array($this->value)) {
			return;
		}
		if (!defined('LITTLED_TEMPLATE_DIR')) {
			throw new ConfigurationUndefinedException("LITTLED_TEMPLATE_DIR not found in app settings.");
		}
		foreach($this->value as $value) {
			ContentUtils::renderTemplate(LITTLED_TEMPLATE_DIR . "framework/forms/hidden-input.php", array(
				'key' => $this->key,
				'index' => '[]',
				'value' => $value
			));
		}
	}
}