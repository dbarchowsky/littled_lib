<?php
namespace Littled\Request;

use Littled\Validation\Validation;


class IntegerSelect extends IntegerInput implements RequestSelectInterface
{
    public static string    $input_template_filename = 'string-select-input.php';
    protected static string $template_filename = 'string-select-field.php';
    public ?int             $options_length = null;
    /** @var int|int[] */
    public mixed $value;
    /** @var int[]          List of available options to include in dropdown menus */
    public array            $options;

    /**
     * Adds a value to the current values stored in the object.
     * @param int $value
     * @return $this
     */
    public function addValue(int $value): IntegerSelect
    {
        if (!isset($this->value)) {
            $this->setInputValue($value);
            return $this;
        }
        if (is_array($this->value)) {
            $this->setInputValue(array_merge($this->value, [$value]));
            return $this;
        }
        $this->setInputValue([$this->value, $value]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function collectRequestData(?array $src = null, ?string $key = null): void
    {
        if ($this->allowMultiple()) {
            $this->value = Validation::collectIntegerArrayRequestVar($key ?? $this->key, $src);
        }
        else {
            parent::collectRequestData($src, $key);
        }
    }

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
     * @param mixed $value
     */
    public function lookupValueInSelectedValues(mixed $value): bool
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
    public function render(string $label = '', string $css_class = '', array $context=[]): void
    {
        if (!array_key_exists('options', $context)) {
            $context['options'] = $context;
        }
        parent::render($label, $css_class, $context);
    }

    /**
     * @inheritDoc
     */
    public function setOptionsLength(int $len): void
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

    /**
     * @inheritDoc
     */
    public function validate(): void
    {
        if (!is_array($this->value)) {
            parent::validate();
        }
        elseif ($this->allowMultiple()===false) {
            $this->throwValidationError("Bad value for $this->label.");
        }
        elseif($this->isRequired()) {
            $parsed = Validation::parseNumericArray($this->value);
            if (count($parsed) < 1) {
                $this->throwValidationError(ucfirst($this->label).' is required.');
            }
        }
    }
}