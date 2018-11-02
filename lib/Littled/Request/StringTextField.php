<?php
namespace Littled\Request;

use Littled\PageContent\PageContent;


/**
 * Class StringTextFieldInput
 * @package Littled\Request
 */
class StringTextField extends StringInput
{
	/**
	 * Returns string containing HTML to render the input elements in a form.
	 * @param string $label Text to display as the label for the form input.
	 * A null value will cause the internal label value to be used. An empty
	 * string will cause the label to not be rendered at all.
	 * @param string[optional] $css_class CSS class name(s) to apply to the input container.
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	function render( $label=null,  $css_class='' )
	{
		parent::render($label, $css_class);
		if ($label===null) { $label=$this->label;}
		PageContent::render(self::$template_base_path."string-text-field.php", array(
			'input' => &$this,
			'label' => $label,
			'css_class' => $css_class
		));
	}

	/**
	 * Renders the corresponding form field with a label to collect the input data.
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	function renderInput()
	{
		PageContent::render(self::$template_base_path."string-text-input.php", array(
			'input' => &$this
		));
	}
}