<?php

namespace Littled\Request;

use Littled\Validation\RequestValidation;
use Littled\Validation\Validation;


class StringInput extends RenderedInput
{
    protected static string     $bind_param_type = 's';
    protected static string     $input_template_filename = 'string-text-input.php';
    protected static string     $template_base_path;
    protected static string     $template_filename = 'string-text-field.php';

    /**
     * @inheritDoc
     */
    public function __construct(
        string          $label          = '',
        string          $key            = '',
        bool            $required       = false,
        mixed           $value          = null,
        int             $size_limit     = 0,
        int|null        $index          = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);

        // override to avoid null values
        $this->setInputValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function clearValue(): void
    {
        $this->value = '';
    }

    /**
     * Collects the value of this form input and stores it in the object.
     * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
     * @param ?int $filters Filters for parsing request variables, e.g., FILTER_UNSAFE_RAW, FILTER_SANITIZE_STRING, etc.
     * @param ?string $key Key to use in place of the internal $key property value.
     */
    public function collectRequestData(?array $src = null, ?int $filters = null, ?string $key = null): void
    {
        if (true === $this->bypass_collect_request_data) {
            return;
        }
        $key = $key ?: $this->key;
        if (null === $filters) {
            $filters = RequestValidation::DEFAULT_REQUEST_FILTER;
        }
        $this->value = Validation::collectStringRequestVar($key, $filters, $this->index, $src);
    }

    /**
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return ('' !== trim('' . $this->value) && false !== $this->value && true !== $this->value);
    }

    /**
     * @inheritDoc
     */
    public function setInputValue(mixed $value): static
    {
        $this->value = '' . $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): void
    {
        if ($this->required) {
            if (!is_string($this->value)) {
                $this->throwValidationError($this->formatErrorLabel() . ' is required.');
            }
            if (strlen(trim($this->value)) < 1) {
                $this->throwValidationError($this->formatErrorLabel() . ' is required.');
            }
            if (strlen($this->value) > $this->size_limit) {
                $this->throwValidationError($this->formatErrorLabel() . " is limited to $this->size_limit characters.");
            }
        }
    }
}