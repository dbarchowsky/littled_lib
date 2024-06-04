<?php

namespace Littled\Request;

use mysqli;
use Littled\Validation\Validation;

class FloatInput extends RenderedInput
{
    /** @var string             Form input element template filename */
    protected static string     $input_template_filename = 'string-text-input.php';
    /** @var string             Input container template filename */
    protected static string     $template_filename = 'string-text-field.php';
    /** @var string             Data type identifier used with bind_param() calls */
    protected static string     $bind_param_type = 'd';
    const                       DEFAULT_DATA_SIZE = 16;

    public function __construct(string $label, string $key, bool $required = false, $value = null, int $size_limit = 0, ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
        // make sure that invalid values are converted to a null value
        $this->setInputValue($value);
        $this->content_type = 'number';
    }

    /**
     * Collects the value corresponding to the $key property value in GET, POST, session, or cookies.
     * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
     * @param ?string $key Key to use in place of the internal $key property value.
     */
    public function collectRequestData(?array $src = null, ?string $key = null): void
    {
        if ($this->bypass_collect_request_data === true) {
            return;
        }
        $this->value = Validation::collectNumericRequestVar((($key) ?: ($this->key)), null, $src);
    }

    /**
     * @inheritDoc
     */
    public function collectAjaxRequestData(object $data): void
    {
        parent::collectAjaxRequestData($data);
        $this->value = Validation::parseNumeric($this->value);
    }

    /**
     * @inheritDoc
     */
    public function escapeSQL(mysqli $mysqli, bool $include_quotes = false): float|int|string|null
    {
        return Validation::parseNumeric($this->value);
    }

    /**
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return is_numeric($this->value);
    }

    /**
     * @inheritDoc
     */
    public function setInputValue(mixed $value): FloatInput
    {
        $this->value = Validation::parseNumeric($value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): void
    {
        if ((trim('' . $this->value) !== '') &&
            (Validation::parseInteger($this->value) === null)) {
            $this->throwValidationError(ucfirst($this->label) . ' is in unrecognized format.');
        }
        if ($this->isRequired() && !$this->hasData()) {
            $this->throwValidationError(ucfirst($this->label) . ' is required.');
        }
    }
}