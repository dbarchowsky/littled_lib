<?php

namespace Littled\PageContent\Navigation;


use InvalidArgumentException;

class RoutePlaceholder
{
    public string $pattern = '';
    public string $type = 'int';
    public string $wildcard = '';
    protected const array ALLOWED_TYPES = ['int', 'str'];

    /**
     * Replacement pattern setter.
     * @param string $pattern
     * @return $this
     */
    public function setPattern(string $pattern): static
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * Data type setter.
     * @param string $type
     * @return $this
     */
    public function setType(string $type): static
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new InvalidArgumentException('Type must be one of: ' . implode(', ', self::ALLOWED_TYPES));
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Wildcard setter.
     * @param string $wildcard
     * @return $this
     */
    public function setWildcard(string $wildcard): static
    {
        $this->wildcard = $wildcard;
        return $this;
    }
}