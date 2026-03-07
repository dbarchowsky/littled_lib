<?php

namespace Littled\PageContent;

class PageScript
{
    public string $script;
    public string $type;

    public function getScript(): string
    {
        return $this->script ?? '';
    }

    public function getType(): string
    {
        return $this->type ?? '';
    }

    public function setScript(string $script): static
    {
        $this->script = $script;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
}