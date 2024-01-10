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
	public function render( string $label='', string $css_class='', array $context=[])
	{
		if (!$label) {
			$label=$this->label;
		}
        $context = array_merge($context, array(
            'input' => &$this,
            'label' => $label,
            'css_class' => $css_class
        ));
		ContentUtils::renderTemplateWithErrors(static::getTemplatePath(), $context);
	}

	/**
	 * @param ?int $value_override Value to insert into the element instead of the object's stored value.
	 * @param array $context Optional array of variables to insert into the element template.
	 * @return void
	 */
	public function renderHidden(?int $value_override=null, array $context=[])
	{
		$context = array_merge($context, array(
			'input' => &$this
		));
		if ($value_override !== null) {
			$context['value_override'] = $value_override;
		}
		ContentUtils::renderTemplateWithErrors(static::getHiddenTemplatePath(), $context);
	}

	/**
	 * Renders the corresponding form field with a label to collect the input data.
	 * @param ?string $label
	 */
	public function renderInput(?string $label=null)
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