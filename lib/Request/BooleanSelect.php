<?php
namespace Littled\Request;


use Exception;
use Littled\PageContent\ContentUtils;

class BooleanSelect extends BooleanInput
{
    /** @var string Form element template filename */
    public static string $template_filename = 'string-select-field.php';
    /** @var string Form input element template filename */
    public static string $input_template_filename = 'string-select-input.php';

    /**
     * Returns input size attribute markup to inject into template.
     * @return string
     */
    public function formatSizeAttributeMarkup(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $label='', string $css_class='', array $options=[])
    {
        try {
            ContentUtils::renderTemplate(static::getTemplatePath(),
                array('input' => $this,
                    'label' => $label,
                    'css_class' => $css_class,
                    'options' => $options));
        }
        catch(Exception $e) {
            ContentUtils::printError($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function renderInput(?string $label='', array $options=[])
    {
        try {
            ContentUtils::renderTemplate(static::getInputTemplatePath(),
                array('input' => $this,
                    'label' => $label,
                    'options' => $options));
        }
        catch(Exception $e) {
            ContentUtils::printError($e->getMessage());
        }
    }
}