<?php
namespace Littled\Tests\Filters\Samples;

use Cassandra\Date;
use Littled\Filters\DateContentFilter;
use Littled\Filters\StringContentFilter;

class ContentFiltersProcedureSample extends ContentFiltersSample
{
    /** @var StringContentFilter */
    public $title;
    /** @var StringContentFilter */
    public $text;
    /** @var StringContentFilter */
    public $source;
    /** @var DateContentFilter */
    public $published_after;
    /** @var DateContentFilter */
    public $published_before;
    /** @var StringContentFilter */
    public $keyword;

    public function __construct(int $content_type_id)
    {
        parent::__construct($content_type_id);
        $this->page->value = 1;
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