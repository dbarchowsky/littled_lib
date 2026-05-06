<?php

namespace Littled\PageContent;

use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\InvalidValueException;
use Littled\Request\StringInput;


trait CSRFPageTrait
{
    public StringInput $csrf;

    /**
     * Class constructor.
     */
    protected function initializeCsrfPageTrait(): void
    {
        $this->csrf = (new StringInput())
            ->setLabel('CSRF token')
            ->setKey(LittledGlobals::CSRF_TOKEN_KEY)
            ->setAsRequired()
            ->setSizeLimit(500)
            ->setInputValue(AppBase::getCSRFToken());
    }

    /**
     * Returns the current CSRF token value collected from request data.
     * @return string
     */
    public function getCSRFRequestValue(): string
    {
        return $this->csrf->value;
    }

    /**
     * @return void
     * @throws InvalidValueException
     */
    public function addCsrfMetadata(): void
    {
        PageConfig::addPageMetadata(
            attribute: 'name',
            value: 'csrf-token',
            content: $this->csrf->value);
    }
}