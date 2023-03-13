<?php
namespace Littled\Request;


use Exception;
use Littled\PageContent\ContentUtils;
use Littled\Validation\Validation;

class BooleanSelect extends BooleanInput implements RequestSelectInterface
{
    /** @var string         Form element template filename */
    public static string    $template_filename = 'string-select-field.php';
    /** @var string         Form input element template filename */
    public static string    $input_template_filename = 'string-select-input.php';
    protected bool          $allow_multiple=false;
    /** @var bool[]|int[]|string[] List of available options to include in dropdown menus */
    protected array         $options;

    /**
     * @inheritDoc
     */
    public function allowMultiple(bool $allow = true)
    {
        if ($allow) {
            $allow = false; // always false for boolean
        }
        $this->allow_multiple = $allow;
    }

    /**
     * @inheritDoc
     */
    public function doesAllowMultiple(): bool
    {
        // always false for boolean dropdowns
        return false;
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
     * Options getter.
     * @return bool[]|int[]|string[]
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getOptionsLength(): ?int
    {
        // interface method. nothing necessary here when working with the 2 or 3 options for boolean options
        return 0;
    }

    /**
     * Test supplied value against the internal value of the input object.
     * @param null|bool|int|string $value
     * @return bool|int|string
     */
    public function lookupValueInSelectedValues($value): bool
    {
        return ($this->value!==null) && ($this->value===Validation::parseBoolean($value));
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

    /**
     * Options setter.
     * @param bool[]|string[]|int[] $options
     * @return $this
     */
    public function setOptions(array $options): BooleanSelect
    {
        $this->options = $options;
        return $this;
    }

    public function setOptionsLength(int $len)
    {
        // interface method. nothing necessary working with boolean values
    }
}