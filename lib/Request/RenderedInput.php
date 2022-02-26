<?php

namespace Littled\Request;


use Littled\PageContent\ContentUtils;

class RenderedInput extends RequestInput
{
	/**
	 * Returns string containing HTML to render the input elements in a form.
	 * @param string $label (Optional) Text to display as the label for the form input.
	 * A null value will cause the internal label value to be used. An empty
	 * string will cause the label to not be rendered at all.
	 * @param string $css_class (Optional) CSS class name(s) to apply to the input container.
	 */
	public function render( string $label='', string $css_class='' )
	{
		if (!$label) {
			$label=$this->label;
		}
		ContentUtils::renderTemplateWithErrors(static::getTemplatePath(), array(
			'input' => &$this,
			'label' => $label,
			'css_class' => $css_class
		));
	}

	/**
	 * Renders the corresponding form field with a label to collect the input data.
	 * @param string[optional] $label
	 */
	public function renderInput($label=null)
	{
		if (!$label) {
			$label = $this->label;
		}
		ContentUtils::renderTemplateWithErrors(static::getInputTemplatePath(), array(
			'input' => &$this,
			'label' => $label
		));
	}
}