<?php
namespace Littled\Tests\Filters\Samples;

class ContentFiltersProcedureSample extends ContentFiltersSample
{
    public function __construct(int $content_type_id)
    {
        parent::__construct($content_type_id);
        $this->page = 1;
        $this->listings_length = 4;
    }

    public function formatListingsQuery(): string
    {
        return "CALL articleListingsSelect($this->page, $this->listings_length, ".
            "NULL, NULL, NULL, NULL, NULL, NULL, @total_matches);".
            "SELECT @total_matches as `total_matches`;";
    }
}