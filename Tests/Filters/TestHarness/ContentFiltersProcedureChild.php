<?php
namespace Littled\Tests\Filters\TestHarness;

use Littled\Filters\DateContentFilter;
use Littled\Filters\StringContentFilter;

class ContentFiltersProcedureChild extends ContentFiltersChild
{
    /** @var StringContentFilter */
    public StringContentFilter $title;
    /** @var StringContentFilter */
    public StringContentFilter $text;
    /** @var StringContentFilter */
    public StringContentFilter $source;
    /** @var DateContentFilter */
    public DateContentFilter $published_after;
    /** @var DateContentFilter */
    public DateContentFilter $published_before;
    /** @var StringContentFilter */
    public StringContentFilter $keyword;

    public function __construct()
    {
        parent::__construct();
        $this->page->value = 1;

        // overrides the default value
        $this->listings_length->value = 4;

        $this->title = new StringContentFilter('title', 'titleFilter', null, 50);
        $this->text = new StringContentFilter('text', 'textFilter', null, 50);
        $this->source = new StringContentFilter('source', 'sourceFilter', null, 50);
        $this->published_after = new DateContentFilter('published after', 'pubAfter', null);
        $this->published_before = new DateContentFilter('published before', 'pubBefore', null);
        $this->keyword = new StringContentFilter('keyword', 'keywordFilter', null, 50);
    }

    protected function formatListingsQuery(): array
    {
        return array(
            "CALL articleListingsSelect(?,?,?,?,?,?,?,?,@total_matches)",
            'iissssss',
            &$this->page->value,
            &$this->listings_length->value,
            &$this->title->value,
            &$this->text->value,
            &$this->source->value,
            &$this->published_after->value,
            &$this->published_before->value,
            &$this->keyword->value);
    }
}