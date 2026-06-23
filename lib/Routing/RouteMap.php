<?php

namespace Littled\Routing;

use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Validation\Validation;


class RouteMap
{
    /** @var string|string[] */
    public string|array $pattern;
    public string $slug;
    public string $class;

    /**
     * @param string|string[] $pattern
     * @param string $class
     * @param string $slug
     */
    function __construct(string|array $pattern='', string $class='', string $slug='')
    {
        if ($pattern) {
            $this->pattern = $pattern;
        }
        if ($slug) {
            $this->slug = $slug;
        }
        if ($class) {
            $this->class = $class;
        }
    }

    /**
     * Collects the record ID from the route or request data.
     * @param string $route
     * @param array|null $request_data
     * @param string $id_key
     * @return int|null
     */
    public function collectRecordId(string $route='', ?array $request_data = null, string $id_key = LittledGlobals::ID_KEY): ?int
    {
        $route = $route ?: PageRouter::collectRoute(request_data: $request_data);

        preg_match('/\/([0-9]+)(?:\\/|$)/', $route, $matches);
        if (count($matches) > 1) {
            return (int)$matches[1];
        }

        // fall back to request data
        $request_data ??= AppBase::getAjaxRequestData() ?: $_POST ?: [];
        return Validation::collectIntegerRequestVar($id_key, null, $request_data);
    }

    /**
     * Slug property value getter.
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    protected function hasRegExPattern(): bool
    {
        return !empty($this->pattern) && is_string($this->pattern) && str_starts_with($this->pattern, '/') && str_ends_with($this->pattern, '/');
    }

    protected function hasNonRegExPattern(): bool
    {
        return isset($this->pattern) && is_string($this->pattern) &&
            (!str_starts_with($this->pattern, '/') || !str_ends_with($this->pattern, '/'));
    }

    /**
     * Test if the route value corresponds to this RouteMap instance.
     * @param string $route
     * @return bool
     */
    public function matches(string $route): bool
    {
        $patternList = is_array($this->pattern) ? $this->pattern : [$this->pattern];
        foreach($patternList as $pattern) {
            if ($this->hasRegExPattern()) {
                preg_match($pattern, $route, $matches);
                if (count($matches) > 1) {
                    $this->slug = $matches[1];
                }
                return count($matches) > 0;
            }

            if (ltrim($route, '/') === $pattern) {
                $this->slug = $pattern;
                return true;
            }
        }
        return false;
    }

    /**
     * Class property value setter.
     * @param string $class
     * @return $this
     */
    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Pattern property value setter.
     * @param string|string[] $pattern
     * @return $this
     */
    public function setPattern(string|array $pattern): static
    {
        $this->pattern = $pattern;
        if ($this->hasNonRegExPattern()) {
            $this->slug = trim((string)$pattern, '/');
        }
        return $this;
    }

    /**
     * Slug property value setter.
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Alias for setPattern().
     * @param string|array $pattern
     * @return $this
     */
    public function withPattern(string|array $pattern): static
    {
        return $this->setPattern($pattern);
    }

    /**
     * Alias for setSlug().
     * @param string $slug
     * @return $this
     */
    public function withSlug(string $slug): static
    {
        return $this->setSlug($slug);
    }
}