<?php
namespace Littled\Request;

use Littled\Validation\Validation;

class IntegerInput extends RenderedInput
{
    /** @var string         Data type identifier used with bind_param() calls */
    protected static string $bind_param_type = 'i';
    protected static string $input_template_filename = 'string-text-input.php';
    protected static string $template_filename = 'string-text-field.php';

    const DEFAULT_DATA_SIZE = 8;

    /**
     * @inheritdoc
     */
    public function __construct(
        string      $label          = '',
        string      $key            = '',
        bool        $required       = false,
        mixed       $value          = null,
        int         $size_limit     = 0,
        int|null    $index          = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
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
        $this->value = Validation::collectIntegerRequestVar((($key) ?: ($this->key)), $this->index, $src);
    }

    /**
     * @inheritDoc
     */
    public function collectAjaxRequestData(object $data): void
    {
        parent::collectAjaxRequestData($data);
        $this->value = Validation::parseInteger($this->value);
    }

    /**
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return is_numeric($this->value);
    }

    /**
     * {@inheritDoc}
     */
    public function renderHidden(mixed $runtime_value = null, array $context = []): void
    {
        if ($runtime_value !== null) {
            $runtime_value = Validation::parseInteger($runtime_value);
        }
        parent::renderHidden($runtime_value, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function safeValue(array|int $options = []): string
    {
        if (!is_numeric($this->value) && !is_array($this->value)) {
            return ('');
        }
        return parent::safeValue($options);
    }

    /**
     * @param mixed $value Value to assign as the value of the object.
     * @return $this
     */
    public function setInputValue(mixed $value): static
    {
        if ($this->allowMultiple()) {
            $this->value = [];
            if (!is_array($value)) {
                $value = [$value];
            }
            foreach ($value as $i) {
                if (is_int($i = Validation::parseInteger($i)) && !in_array($i, $this->value)) {
                    $this->value[] = $i;
                }
            }
        }
        else {
            if (is_array($value)) {
                if (count($value) === 0) {
                    $this->value = null;
                    return $this;
                }
                $this->value = Validation::parseInteger($value[0]);
            } else {
                $this->value = Validation::parseInteger($value);
            }
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): void
    {
        if (is_array($this->value)) {
            $this->throwValidationError(ucfirst($this->label) . ' is in unrecognized format.');
        }
        if ((trim('' . $this->value) !== '') &&
            (Validation::parseInteger($this->value) === null)) {
            $this->throwValidationError(ucfirst($this->label) . ' is in unrecognized format.');
        }
        if ($this->isRequired() && !$this->hasData()) {
            $this->throwValidationError(ucfirst($this->label) . ' is required.');
        }
    }
}