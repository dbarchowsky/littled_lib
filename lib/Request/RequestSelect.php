<?php

namespace Littled\Request;

trait RequestSelect
{
    protected   bool            $include_null_option = true;
    /** @var array              List of available options to include in dropdown menus */
    public      array           $options;
    public      ?int            $options_length = null;

    /**
     * Options length getter.
     * @return int|null
     */
    public function getOptionsLength(): ?int
    {
        return $this->options_length;
    }

    /**
     * Include null option flag value getter.
     * @return bool
     */
    public function getIncludeNullOption(): bool
    {
        return $this->include_null_option;
    }

    /**
     * Dropdown menu options.
     * @return int[]
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    /**
     * {@inheritDoc}
     * @param string|string[] $label
     * @param string $css_class
     * @param array $context
     */
    public function render(string|array $label = '', string $css_class = '', array $context = []): void
    {
        if (!array_key_exists('options', $context)) {
            if (count($context) > 0) {
                $context = ['options' => $context];
            }
        }
        if (array_key_exists('options', $context) && $this->getIncludeNullOption() === true) {
            if (!array_key_exists('', $context['options']) || $context['options'][''] !== '') {
                $context['options'] = ['' => ''] + $context['options'];
            }
        }
        parent::render($label, $css_class, $context);
    }

    /**
     * Sets options to be displayed in a select dropdown menu.
     * @param int[] $options
     * @return $this
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;
        if ($this->getIncludeNullOption()) {
            if (!array_key_exists('', $this->options) || '' !== $this->options['']) {
                $this->options = ['' => ''] + $this->options;
            }
        }
        return $this;
    }

    /**
     * Options length setter. If this value is set, the number of options displayed will be limited to the length value.
     * @param int $len
     * @return void
     */
    public function setOptionsLength(int $len): void
    {
        $this->options_length = $len;
    }

    /**
     * Sets the value of a flag to suppress a default blank option on dropdown options.
     * @return $this
     */
    public function suppressDefaultToNull(): static
    {
        $this->include_null_option = false;
        return $this;
    }
}