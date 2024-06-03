<?php

namespace Littled\PageContent\Serialized;

use Littled\Validation\Validation;

class RecordsetPrefix
{
    /** @var string|string[] */
    protected string|array $prefix;

    public function hasValue(): bool
    {
        return isset($this->prefix) &&
            ((is_array($this->prefix) && count($this->prefix) > 0) ||
                (is_string($this->prefix) && !Validation::isStringBlank($this->prefix)));
    }

    /**
     * Prefix setter.
     * @return string|string[]
     */
    public function getPrefix(): array|string
    {
        return $this->prefix ?? '';
    }

    /**
     * Searches for a property on an object ($o) that matches the base property name passed ($property) along with
     * any of the prefixes stored in the object's $prefix property. Returns empty string if no matches are found.
     * @param object $o
     * @param string $property
     * @return string
     */
    public function lookupPrefixProperty(object $o, string $property): string
    {
        if (!$this->hasValue()) {
            return '';
        }
        $options = is_array($this->prefix) ? $this->prefix : [$this->prefix];
        foreach($options as $prefix) {
            if (property_exists($o, $prefix . $property)) {
                return $prefix . $property;
            }
        }
        return '';
    }

    /**
     * Prefix setter.
     * @param $prefix
     * @return $this
     */
    public function setPrefix($prefix): RecordsetPrefix
    {
        $this->prefix = $prefix;
        return $this;
    }
}