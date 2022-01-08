<?php
namespace Littled\Tests\Filters\Samples;

class ContentFiltersProcedureSample extends ContentFiltersSample
{
    public function __construct(int $content_type_id)
    {
        parent::__construct($content_type_id);
        $this->page->value = 1;
        $this->listings_length->value = 4;
    }

    protected function formatListingsQuery(): array
    {
        $title_filter = $text_filter = $source_filter = $pub_after = $pub_before = $keyword_filter = null;
        return array(
            "CALL articleListingsSelect(?,?,?,?,?,?,?,?,@total_matches)",
            'iissssss',
            $this->page->value,
            $this->listings_length->value,
            $title_filter,
            $text_filter,
            $source_filter,
            $pub_after,
            $pub_before,
            $keyword_filter);
    }
}