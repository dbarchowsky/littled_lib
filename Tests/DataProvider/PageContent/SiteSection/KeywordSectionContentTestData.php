<?php

namespace LittledTests\DataProvider\PageContent\SiteSection;


use Littled\Keyword\Keyword;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use Exception;

class KeywordSectionContentTestData
{
    public bool         $expected;
    public ?string      $input_value;
    public ?string      $term;
    public ?int         $id;
    public ?int         $content_properties_id;
    public Keyword      $keyword;

    public function __construct(
        bool $expected,
        ?string $input_value=null,
        ?string $term=null,
        ?int $id=null,
        ?int $content_properties_id=null)
    {
        $this->expected = $expected;
        $this->input_value = $input_value;
        $this->term = $term;
        $this->id = $id;
        $this->content_properties_id = $content_properties_id;
    }

    public function addKeyword(string $term, int $content_type_id, int $parent_id): KeywordSectionContentTestData
    {
        $this->keyword = new Keyword($term, $content_type_id, $parent_id);
        return $this;
    }

    /**
     * @throws Exception
     */
    public function copy(KeywordSectionContent $o)
    {
        if ($this->input_value!=='[use defaults]') {
            $o->keyword_input->setInputValue($this->input_value);
        }
        if ($this->id > 0) {
            $o->id->setInputValue($this->id);
        }
        if ($this->content_properties_id > 0) {
            $o->content_properties->id->setInputValue($this->content_properties_id);
        }
        if ($this->term !== null) {
            $o->addKeyword($this->term);
        }
        if (isset($this->keyword)) {
            $o->keywords[] = $this->keyword;
        }
    }
}