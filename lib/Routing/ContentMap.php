<?php

namespace Littled\Routing;


class ContentMap
{
    public int              $id;
    /** @var string|string[] */
    public string|array     $slug;
    public string           $class;

    /**
     * @param int|null $id
     * @param string|string[] $slug
     * @param string $class
     */
    function __construct(?int $id=null, string|array $slug='', string $class='')
    {
        if ($id) {
            $this->id = $id;
        }
        if ($slug) {
            $this->slug = $slug;
        }
        if ($class) {
            $this->class = $class;
        }
    }

    /**
     * Id property value setter.
     */
    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Slug property value setter.
     * @param string|string[] $slug
     * @return $this
     */
    public function setSlug(string|array $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Alias for setSlug()
     * @param string|array $slug
     * @return $this
     */
    public function withSlug(string|array $slug): static
    {
        return $this->setSlug($slug);
    }

    /**
     * Content class property value setter.
     * @param string $class
     * @return $this
     */
    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }
}