<?php

namespace Littled\PageContent;

use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\InvalidValueException;
use Littled\Request\StringInput;


abstract class CSRFPageContent extends PageContent
{
    public StringInput $csrf;

    /**
     * Class constructor.
     */
    function __construct()
    {
        $this->csrf = new StringInput('CSRF token', LittledGlobals::CSRF_TOKEN_KEY, true, $this::getCSRFToken(), 500);
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
    public function setPageState(): void
    {
        PageConfig::addPageMetadata(
            attribute: 'name',
            value: 'csrf-token',
            content: AppBase::getCSRFToken());
    }
}