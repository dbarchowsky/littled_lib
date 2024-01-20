<?php
namespace Littled\Request;


class IntegerSelect extends IntegerInput implements RequestSelectInterface
{
    public static string    $input_template_filename = 'string-select-input.php';
    protected static string $template_filename = 'string-select-field.php';
    public ?int             $options_length = null;
    /** @var int|int[] */
    public $value;
    /** @var int[]          List of available options to include in dropdown menus */
    public array            $options;

    /**
     * Returns input size attribute markup to inject into template.
     * @return string
     */
    public function formatSizeAttributeMarkup(): string
    {
        return ((0 < $this->options_length)?(" size=\"$this->options_length\""):(''));
    }

    /**
     * @inheritDoc
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
        return $this->options_length;
    }

    /**
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return (is_numeric($this->value) || (is_array($this->value) && count($this->value) > 0));
    }

    /**
     * @inheritDoc
     * @param null|int $value
     */
    public function lookupValueInSelectedValues($value): bool
    {
        if (is_array($this->value)) {
            return in_array($value, $this->value);
        }
        else {
            return ($value!==null) && ($value === $this->value);
        }
    }

    /**
     * @inheritDoc
     */
    public function render(string $label = '', string $css_class = '', array $context=[])
    {
        if (!array_key_exists('options', $context)) {
            $context['options'] = $context;
        }
        parent::render($label, $css_class, $context);
    }

    /**
     * @inheritDoc
     */
    public function setOptionsLength(int $len)
    {
        $this->options_length = $len;
    }

    /**
     * @inheritDoc
     * @param int[] $options
     * @return $this
     */
    public function setOptions(array $options): IntegerSelect
    {
        $this->options = $options;
        return $this;
    }
}