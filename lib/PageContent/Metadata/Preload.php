<?php

namespace Littled\PageContent\Metadata;


use Littled\PageContent\ContentUtils;

class Preload
{
    /** @var string Tag type of the preload element */
    public string $tag;
    /** @var string Value to insert into the "rel" attribute of the element */
    public string $rel;
    /** @var string Value to insert into the "href" attribute of the element */
    public string $url;
    /** @var array Extra attributes to attach to the element */
    public array $extra_attributes;

    /**
     * @param string $tag
     * @param string $rel
     * @param string $url
     * @param array $extra_attributes []
     */
    function __construct(string $tag, string $rel = '', string $url = '', array $extra_attributes = [])
    {
        $this->tag = $tag;
        $this->rel = $rel;
        $this->url = $url;
        $this->extra_attributes = $extra_attributes;
    }

    /**
     * Injects property values as page markup.
     * @return void
     */
    public function render(): void
    {
        if ('' === $this->tag) {
            ContentUtils::printError('Missing required metadata tag.');
            return;
        }
        $href = (($this->url) ? (" href=\"$this->url\"") : (''));
        $extras_cb = function (&$value, $key) {
            $value = (($value) ? (" $key=\"$value\"") : (" $key"));
        };
        $extras = $this->extra_attributes;
        array_walk($extras, $extras_cb);
        $extras = implode('', $extras);
        ?>
        <<?= $this->tag ?> rel="<?= $this->rel ?>"<?= $href . $extras ?> />
        <?php
    }
}