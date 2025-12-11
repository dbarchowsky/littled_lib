<?php
namespace Littled\Request;

use Littled\Validation\Validation;


class IntegerSelect extends IntegerInput implements RequestSelectInterface
{
    use RequestSelect;

    public      static string   $input_template_filename = 'string-select-input.php';
    protected   static string   $template_base_path;
    protected   static string   $template_filename = 'string-select-field.php';

    /** @var int[] */
    public      array           $options;
    /** @var int|int[] */
    public      mixed           $value;

    /**
     * Adds a value to the current values stored in the object.
     * @param int|array $value
     * @return $this
     */
    public function addValue(int|array $value): IntegerSelect
    {
        if (!isset($this->value)) {
            $this->setInputValue($value);
            return $this;
        }
        $value = is_array($value) ? $value : [$value];
        $value = array_merge(is_array($this->value) ? $this->value : [$this->value], $value);
        $this->setInputValue($value);
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
    public function validate(): void
    {
        if (!is_array($this->value)) {
            parent::validate();
        }
        elseif ($this->allowMultiple()===false) {
            $this->throwValidationError("Bad value for $this->label.");
        }
        else {
            $parsed = Validation::parseNumericArray($this->value);
            if ($this->isRequired() && count($parsed) < 1) {
                $this->throwValidationError(ucfirst($this->label).' is required.');
            }
            elseif (count($parsed) < count($this->value)) {
                $this->throwValidationError(ucfirst($this->label).' contains invalid values.');
            }
        }
    }
}