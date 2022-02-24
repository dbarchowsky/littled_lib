<?php
namespace Littled\Tests\PageContent\Navigation\TestHarness;

use Littled\PageContent\Navigation\RoutedPageContent;

class RoutedPageContentTestHarness extends RoutedPageContent
{
    public function instantiateProperties(?int $record_id=null)
    {
        parent::instantiateProperties();
        $content_class = static::getContentClassName();
        if ($content_class) {
            $this->content = new $content_class();
            if ($record_id > 0) {
                $this->content->id->setInputValue($record_id);
            }
        }
    }
}