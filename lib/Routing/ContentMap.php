<?php

namespace Littled\Routing;


class ContentMap
{
    public int          $id;
    public string       $slug;
    public string       $class;

    function __construct(?int $id=null, string $name='', string $class='')
    {
        if ($id) {
            $this->id = $id;
        }
        if ($name) {
            $this->slug = $name;
        }
        if ($class) {
            $this->class = $class;
        }
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }
}