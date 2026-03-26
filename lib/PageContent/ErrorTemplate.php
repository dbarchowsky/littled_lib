<?php

namespace Littled\PageContent;


class ErrorTemplate
{
    public string       $css_class      = 'alert alert-error';
    public string       $format         = '<div class="%s">%s</div>';
    public string       $encoding       = 'UTF-8';

    public function renderError(string $error_message): void
    {
        printf($this->format, $this->css_class, htmlspecialchars($error_message, ENT_QUOTES, $this->encoding));
    }

    public function setCssClass(string $css_class): static
    {
        $this->css_class = $css_class;
        return $this;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function setEncoding(string $encoding): static
    {
        $this->encoding = $encoding;
        return $this;
    }
}