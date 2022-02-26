<?php
namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use Littled\PageContent\ContentUtils;
use Littled\Validation\Validation;

class BooleanInput extends RequestInput
{
	/** @var string */
	protected static string $template_filename = 'hidden-input.php';

	/**
	 * Clears the data container value.
	 */
	public function clearValue()
	{
		$this->value = null;
	}


	/**
	 * Collects the value of this form input and stores it in the object.
	 * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 * @param ?string $key Key to use in place of the internal $key property value.
	 */
	public function collectRequestData (?array $src=null, ?string $key=null)
	{
		$this->value = Validation::collectBooleanRequestVar((($key)?:($this->key)), $this->index, $src);
	}

	/**
	 * Assigns property value from corresponding value in JSON data passed along with a client request.
	 * @param object $data
	 */
	public function collectJsonRequestData(object $data)
	{
		parent::collectJsonRequestData($data);
		$this->value = Validation::parseBoolean($this->value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function escapeSQL($mysqli, $include_quotes=false): string
	{
        $value = $this->formatValueMarkup();
        return ((''===$value)?('NULL'):($value));
	}

    /**
     * {@inheritDoc}
     */
    public function formatValueMarkup(): string
    {
        if ($this->value===false || $this->value==='false' || $this->value==='0' || $this->value===0 || $this->value==='off') {
            return ('0');
        }
        if ($this->value===true || $this->value==='true' || $this->value==='1' || $this->value===1 || $this->value==='on') {
            return ('1');
        }
        return ('');
    }

    /**
	 * {@inheritDoc}
	 */
	public function isEmpty(): bool
	{
		return true;
	}

	/**
	 * Render the form input element(s) in the DOM.
	 * @param string $label If a value is provided, it will override the object's internal $label property value.
	 * @param string $css_class CSS class name to apply to the form input element.
	 */
	public function render(string $label='', string $css_class='')
	{
		if (false === $this->isTemplateDefined()) {
			ContentUtils::printError("\"".__METHOD__."\" not implemented.");
		}

		if (!$label) {
			$label = $this->label;
		}
		$selection_state = ((true === $this->value)?(' checked="checked"'):(''));
		$required_str = (($this->required)?($this::getRequiredIndicator()):(''));

		ContentUtils::renderTemplateWithErrors($this::getTemplatePath(),
			array(
				'input' => &$this,
				'label' => $label,
				'css_class' => $css_class,
				'selection_state' => $selection_state,
				'required_field_indication' => $required_str
			));
	}

	/**
	 * Assigns a value to the object. Checks that passed value is boolean.
	 * @param bool $value Value to assign.
	 */
	public function setInputValue ($value)
	{
		$this->value = Validation::parseBoolean($value);
	}

	/**
	 * Validates the collected value as a non-empty string within its size limit.
	 * @throws ContentValidationException
	 */
	public function validate ( )
	{
		if ($this->required) {
			if ($this->value===null) {
				$this->throwValidationError(ucfirst($this->label)." is required.");
			}
		}
		if ($this->value!==null && $this->value!==true && $this->value!==false) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
	}
}