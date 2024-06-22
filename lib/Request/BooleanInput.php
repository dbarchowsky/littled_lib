<?php

namespace Littled\Request;

use Littled\Exception\ContentValidationException;
use Littled\PageContent\ContentUtils;
use Littled\Validation\Validation;


class BooleanInput extends RenderedInput
{
    /** @var string */
    protected static string $template_filename = 'hidden-input.php';
    /** @var string             Data type identifier used with bind_param() calls */
    protected static string $bind_param_type = 'i';

    /**
     * Clears the data container value.
     */
    public function clearValue(): void
    {
        $this->value = null;
    }

    /**
     * Collects the value of this form input and stores it in the object.
     * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
     * @param ?string $key Key to use in place of the internal $key property value.
     */
    public function collectRequestData(?array $src = null, ?string $key = null): void
    {
        $this->value = Validation::collectBooleanRequestVar((($key) ?: ($this->key)), $this->index, $src);
    }

    /**
     * @inheritDoc
     */
    public function collectAjaxRequestData(object $data): void
    {
        parent::collectAjaxRequestData($data);
        $this->value = Validation::parseBoolean($this->value);
    }

    /**
     * {@inheritDoc}
     */
    public function escapeSQL($mysqli, $include_quotes = false): bool|null
    {
        return Validation::parseBoolean($this->value);
    }

    /**
     * {@inheritDoc}
     */
    public function formatValueMarkup(): string
    {
        if ($this->value === null) {
            return '';
        }
        $v = Validation::parseBoolean($this->value);
        return (($v === true) ? '1' : (($v === false) ? '0' : ''));
    }

    /**
     * {@inheritDoc}
     */
    public function hasData(): bool
    {
        return is_bool(Validation::parseBoolean($this->value));
    }

    /**
     * Render the form input element(s) in the DOM.
     * @param string $label If a value is provided, it will override the object's internal $label property value.
     * @param string $css_class CSS class name to apply to the form input element.
     */
    public function render(string $label = '', string $css_class = '', array $context = []): void
    {
        if (false === $this->isTemplateDefined()) {
            ContentUtils::printError("\"" . __METHOD__ . "\" not implemented.");
        }

        if (!$label) {
            $label = $this->label;
        }
        $selection_state = ((true === $this->value) ? (' checked="checked"') : (''));
        $required_str = (($this->required) ? ($this::getRequiredIndicator()) : (''));

        ContentUtils::renderTemplateWithErrors($this::getTemplatePath(),
            [
                'input' => &$this,
                'label' => $label,
                'css_class' => $css_class,
                'selection_state' => $selection_state,
                'required_field_indication' => $required_str
            ]);
    }

    /**
     * Assigns a value to the object. Checks that passed value is boolean.
     * @inheritDoc
     */
    public function setInputValue(mixed $value): BooleanInput
    {
        $this->value = Validation::parseBoolean($value);
        return $this;
    }

    /**
     * Validates the collected value as a non-empty string within its size limit.
     * @throws ContentValidationException
     */
    public function validate(): void
    {
        if ($this->required) {
            if ($this->value === null) {
                $this->throwValidationError(ucfirst($this->label) . ' is required.');
            }
        }
        if ($this->value !== null && $this->value !== true && $this->value !== false) {
            $this->throwValidationError(ucfirst($this->label) . ' is in unrecognized format.');
        }
    }
}