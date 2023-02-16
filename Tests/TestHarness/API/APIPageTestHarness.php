<?php
namespace Littled\Tests\TestHarness\API;

use Littled\API\APIPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\PageContent\PageContent;


class APIPageTestHarness extends APIPage
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