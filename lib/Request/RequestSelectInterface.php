<?php
namespace Littled\Request;


interface RequestSelectInterface
{
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
     * @param mixed $value
     * @return bool
     */
    public function lookupValueInSelectedValues(mixed $value): bool;

    /**
     * Options setter
     * @param array $options
     * @return RequestInput
     */
    public function setOptions(array $options): RequestInput;

    /**
     * Options length setter. If this value is set, the number of options displayed will be limited to length value.
     * @param int $len
     * @return void
     */
    public function setOptionsLength(int $len): void;
}