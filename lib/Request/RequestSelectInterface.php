<?php
namespace Littled\Request;


interface RequestSelectInterface
{
    /**
     * Allow multiple setter. If set to true, multiple choices can be selected from the drop-down options.
     * @param bool $allow Flag indicating if multiple values are allowed or not.
     * @return void
     */
    public function allowMultiple(bool $allow=true);

    /**
     * Allow multiple flag getter.
     * @return bool
     */
    public function doesAllowMultiple(): bool;

    /**
     * Dropdown menu options.
     * @return array
     */
    public function getOptions(): array;

    /**
     * Options length getter.
     * @return int|null
     */
    public function getOptionsLength(): ?int;

    /**
     * Test if the supplied value matches any of the current internal selected category values. Returns TRUE if
     * $value matches a selected category value.
     * @param null|bool|int|float|string $value
     * @return bool
     */
    public function lookupValueInSelectedValues($value): bool;

    /**
     * Options setter
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): RequestInput;

    /**
     * Options length setter. If this value is set, the number of options displayed will be limited to length value.
     * @param int $len
     * @return void
     */
    public function setOptionsLength(int $len);
}