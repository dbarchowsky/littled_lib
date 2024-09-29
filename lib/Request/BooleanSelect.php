<?php
namespace Littled\Request;


use Exception;
use Littled\PageContent\ContentUtils;
use Littled\Validation\Validation;

class BooleanSelect extends BooleanInput implements RequestSelectInterface
{
    use RequestSelect;

    /** @var string         Form element template filename */
    public static string    $template_filename = 'string-select-field.php';
    /** @var string         Form input element template filename */
    public static string    $input_template_filename = 'string-select-input.php';
    /** @var bool[]|int[]|string[] List of available options to include in dropdown menus */
    public array            $options;

    public function __construct(string $label, string $key, bool $required = false, mixed $value = null, int $size_limit = 0, ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
        $this->suppressDefaultToNull();
    }

    /**
     * Returns input size attribute markup to inject into template.
     * @return string
     */
    public function formatSizeAttributeMarkup(): string
    {
        return '';
    }

    /**
     * Test supplied value against the internal value of the input object.
     * @param mixed $value
     * @return bool
     */
    public function lookupValueInSelectedValues(mixed $value): bool
    {
        return ($this->value!==null) && ($this->value===Validation::parseBoolean($value));
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $label='', string $css_class='', array $context=[]): void
    {
        try {
            ContentUtils::renderTemplate(static::getTemplatePath(),
                [
                    'input' => $this,
                    'label' => $label,
                    'css_class' => $css_class,
                    'options' => $context]);
        }
        catch(Exception $e) {
            ContentUtils::printError($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function renderInput(?string $label='', array $options=[]): void
    {
        try {
            ContentUtils::renderTemplate(static::getInputTemplatePath(),
                ['input' => $this,
                    'label' => $label,
                    'options' => $options]);
        }
        catch(Exception $e) {
            ContentUtils::printError($e->getMessage());
        }
    }
}