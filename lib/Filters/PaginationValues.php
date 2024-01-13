<?php

namespace Littled\Filters;


class PaginationValues
{
    public int $page;
    public int $listings_length;
    public int $page_count;

    function __construct(int $page, int $listings_length, int $page_count)
    {
        $this->page = $page;
        $this->listings_length = $listings_length;
        $this->page_count = $page_count;
    }
}