<?php

namespace Littled\Routing;


use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Validation\Validation;

class RouteMap
{
    public string $pattern;
    public string $slug;
    public string $class;

    function __construct(string $pattern='', string $class='', string $slug='')
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

    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    public function matches(string $route): bool
    {
        preg_match($this->pattern, $route, $matches);
        if (count($matches) > 1) {
            $this->slug = $matches[1];
        }
        return count($matches) > 0;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function setPattern(string $pattern): static
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }
}