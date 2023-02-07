<?php
namespace Littled\Tests\DataProvider\PageContent\SiteSection;

use Littled\PageContent\SiteSection\ContentProperties;


class ContentPropertiesTestData
{
    public ContentProperties    $obj;
    public string               $msg              = '';

    function __construct(string $msg='')
    {
        $this->msg = $msg;
    }

    public static function newInstance(
        int $id,
        string $name,
        string $slug,
        string $root_dir,
        string $msg=''
    ): ContentPropertiesTestData
    {
        $o = new ContentPropertiesTestData($msg);
        $o->setObjectProperties(
            $id,
            $name,
            $slug,
            $root_dir
        );
        return $o;
    }

    public function setObjectProperties(
        int $id,
        string $name,
        string $slug,
        string $root_dir
    ): ContentPropertiesTestData
    {
        $this->obj = new ContentProperties($id);
        $this->obj->name->setInputValue($name);
        $this->obj->slug->setInputValue($slug);
        $this->obj->root_dir->setInputValue($root_dir);
        return $this;
    }
}