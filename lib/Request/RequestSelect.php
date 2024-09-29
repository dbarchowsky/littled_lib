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
     * Sets options to be displayed in select dropdown menu.
     * @param int[] $options
     * @return $this
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;
        if ($this->getIncludeNullOption()) {
            $this->options = ['' => ''] + $this->options;
        }
        return $this;
    }

    /**
     * Options length setter. If this value is set, the number of options displayed will be limited to length value.
     * @param int $len
     * @return void
     */
    public function setOptionsLength(int $len): void
    {
        $this->options_length = $len;
    }

    /**
     * Set flag to suppress a default blank option on dropdown options.
     * @return $this
     */
    public function suppressDefaultToNull(): static
    {
        $this->include_null_option = false;
        return $this;
    }
}