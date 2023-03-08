<?php

namespace Littled\Request;

use Littled\Validation\Validation;

class WYSIWYGTextarea extends StringTextarea
{
    const DEFAULT_SIZE_LIMIT = 10000;
    protected static string $editor_css_class='mce-editor';

    /**
     * @inheritDoc
     * Assigns default editor class to textarea element.
     */
    function __construct(string $label, string $key, bool $required = false, $value = null, int $size_limit=self::DEFAULT_SIZE_LIMIT, ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
        $this->input_css_class=static::$editor_css_class;
    }

    /**
     * @inheritDoc
     * Sets filters to allow html tags.
     */
    public function collectRequestData(?array $src = null, ?int $filters = null, ?string $key = null)
    {
        if (true===$this->bypass_collect_request_data) {
            return;
        }
        $key = $key ?: $this->key;
        $allowed = ['p', 'div', 'span', 'a', 'img', 'b', 'i', 'strong', 'em', 'ul', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'article', 'section'];
        $this->value = Validation::stripTags($key, $allowed, $this->index, $src);
    }

    /**
     * Default editor DOM element css class getter.
     * @return string
     */
    public static function getEditorClass(): string
    {
        return static::$editor_css_class;
    }

    /**
     * @inheritDoc
     */
    function render(string $label = '', string $css_class = '', array $context=[])
    {
        $this->setInputCSSClass(static::$editor_css_class);
        parent::render($label, $css_class);
    }

    /**
     * Default editor element css class setter.
     * @param string $class
     * @return void
     */
    public static function setEditorClass(string $class)
    {
        static::$editor_css_class = $class;
    }

    /**
     * Overrides parent method to inject the editor class onto the editor DOM element.
     * @param string $class
     * @return WYSIWYGTextarea
     */
    public function setInputCSSClass(string $class): WYSIWYGTextarea
    {
        $this->input_css_class = $class;
        if (strpos($this->input_css_class, static::$editor_css_class)===false) {
            $this->input_css_class .= " ".static::$editor_css_class;
        }
        return $this;
    }
}