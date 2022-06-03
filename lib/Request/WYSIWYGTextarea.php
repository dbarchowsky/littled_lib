<?php

namespace Littled\Request;

use Littled\Validation\Validation;

class WYSIWYGTextarea extends StringTextarea
{
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
}