<?php
namespace Littled\Tests\TestHarness\Ajax;

use Littled\Ajax\AjaxPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\PageContent\PageContent;

class AjaxPageTestHarness extends AjaxPage
{
    public static function publicGetAjaxClientRequestData(): ?array
    {
        return parent::getAjaxClientRequestData();
    }

    /**
     * @return PageContent
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     * @throws InvalidQueryException
     */
    public function publicNewRoutedPageContentTemplateInstance(): PageContent
    {
        return $this->newRoutedPageContentInstance();
    }
}