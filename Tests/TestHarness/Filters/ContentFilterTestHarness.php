<?php
namespace LittledTests\TestHarness\Filters;

use Littled\Filters\ContentFilter;


class ContentFilterTestHarness extends ContentFilter
{
    public function publicCollectRequestValue(?array $src=null)
    {
        parent::collectRequestValue($src);
    }
}