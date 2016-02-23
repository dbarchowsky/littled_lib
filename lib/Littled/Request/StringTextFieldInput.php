<?php
namespace Littled\Request;


use Littled\PageContent\PageContent;

class StringTextFieldInput extends StringInput
{
	/**
	 * Returns string containing HTML to render the input elements in a form.
	 * @param string $label Text to display as the label for the form input.
	 * A null value will cause the internal label value to be used. An empty
	 * string will cause the label to not be rendered at all.
	 * @param string $css_class (Optional) CSS class name(s) to apply to the
	 * input container.
	 * @return void
	 */
	function render( $label=null,  $css_class='')
	{
		if (!defined('LITTLED_TEMPLATE_DIR')) {
			return ('');
		}
		if ($label===null) { $label=$this->label;}
		PageContent::render(LITTLED_TEMPLATE_DIR.self::TEMPLATE_PATH."string-text-field.php", array(
			'input' => &$this,
			'label' => $label,
			'css_class' => $css_class
		));
	}

	/**
	 * Renders the corresponding form field with a label to collect the input data.
	 */
	function renderInput()
	{
		if (!defined('LITTLED_TEMPLATE_DIR')) {
			return ('');
		}
		PageContent::render(LITTLED_TEMPLATE_DIR.self::TEMPLATE_PATH."string-text-input.php", array(
			'input' => &$this
		));
	}
}