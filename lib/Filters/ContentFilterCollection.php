<?php
namespace Littled\Filters;

use Littled\SiteContent\ContentProperties;
use Littled\SiteContent\ContentAjaxProperties;
use mysqli_result;
use Exception;

class ContentFilterCollection extends FilterCollection
{
    /** @var ContentProperties Content properties. */
    public $site_section;
    /** @var ContentAjaxProperties Extended content properties. */
    public $section_operations;
    /** @var integer Pointer to $site_section->id->value for convenience. */
    public $content_type_id;

    /**
     * @param int $content_type_id
     * @throws Exception
     */
    function __construct( int $content_type_id )
    {
        parent::__construct();
        $this->site_section = new ContentProperties($content_type_id);
        $this->section_operations = new ContentAjaxProperties();
        $this->section_operations->section_id->value = $content_type_id;
        $this->content_type_id = &$this->site_section->id->value;
        $this->site_section->read();
        $this->section_operations->retrieveContentProperties();
    }

    /**
     * Retrieves listings using sql in $query argument. Stores the total
     * number of matches and updates internal values of total number of pages
     * and current page number.
     * @param string $query SQL query to execute.
     * @return mysqli_result
     * @throws Exception
     */
    protected function executeListingsQuery( string $query ): mysqli_result
    {
        $this->connectToDatabase();

        if (!$this->mysqli->multi_query($query)) {
            throw new Exception("[".__METHOD__."] Error retrieving listings: {$this->mysqli->error}");
        }
        $result = $this->mysqli->store_result();
        if (!$result) {
            throw new Exception("[".__METHOD__."] Error retrieving listings: {$this->mysqli->error}");
        }
        /* get record count from procedure results */
        $this->getSprocPageCount();
        return $result;
    }
}
