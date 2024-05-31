<?php

namespace Littled\Filters;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Albums\Album;
use Littled\PageContent\Albums\SocialXPostAlbum;
use Littled\PageContent\Images\ImageLink;
use Littled\Request\StringInput;
use Littled\Validation\Validation;

/**
 * Class AlbumViewer
 * @package Littled\Filters
 */
class AlbumViewer extends SocialXPostAlbum
{
    public const PAGE_FORWARD = "fwd";
    public const PAGE_BACK = "back";
    public const ID_PARAM = "id";
    public const BOOK_PARAM = "b";
    public const PAGE_PARAM = "p";
    /** @var ImageLink[] Pointer to $gallery->list property. */
    public array $pages;
    /** @var StringInput Direction of navigation, e.g. "fwd" or "back". */
    public StringInput $direction;
    /** @var StringInput Used to allow AJAX scripts to specify a "category" value in calls to Google Analytics. */
    public StringInput $albumType;
    /** @var int Value of the content identifier for the gallery. Pointer to $gallery->site_section->id->value property. */
    public int $pageContentTypeID;
    /** @var string Image directory path. */
    public string $imagePath;
    public static string $viewer_uri = '';
    public static string $one_page_layout = '1-up';
    public static string $two_page_layout = '2-up';

    /**
     * AlbumViewer constructor
     * @param int $content_type_id
     * @param int $page_content_type_id
     * @param string $image_dir
     * @throws ContentValidationException
     * @throws \Littled\Exception\ConfigurationUndefinedException
     * @throws \Littled\Exception\ConnectionException
     * @throws \Littled\Exception\InvalidQueryException
     * @throws \Littled\Exception\InvalidTypeException
     * @throws \Littled\Exception\NotImplementedException
     * @throws \Littled\Exception\RecordNotFoundException
     */
    function __construct($content_type_id, $page_content_type_id, $image_dir)
    {
        parent::__construct($content_type_id, $page_content_type_id);
        $this->pageContentTypeID = &$this->gallery->content_properties->id->value;
        $this->imagePath = $image_dir;
        $this->id->key = $this::ID_PARAM;
        $this->direction = new StringInput("Direction", "op", false, "", 10);

        /* storage for albums's content type name used by ajax scripts to specify "category" in Google Analytics calls */
        $this->albumType = new StringInput("Album Type", "abtp", "", 50, false);
        $this->albumType->is_database_field = false;
        $this->albumType->value = &$this->content_properties->name->value;

        $this->direction->is_database_field = false;

        /* make space for two pages */
        $this->gallery->list = array();
        $this->pages = &$this->gallery->list;

        /* spread to display */
        $this->pages[0] = new ImageLink($this->imagePath, "", $this->pageContentTypeID);
        $this->pages[1] = new ImageLink($this->imagePath, "", $this->pageContentTypeID);

        /* spread for pre-load */
        $this->pages[2] = new ImageLink($this->imagePath, "", $this->pageContentTypeID);
        $this->pages[3] = new ImageLink($this->imagePath, "", $this->pageContentTypeID);

        /* first page id is used to determine position within the book */
        $this->pages[0]->id->param = "p";

        $this->id->label = "Sketchbook";
        $this->pages[0]->id->label = "Page";

        $this->id->required = true;
        $this->pages[0]->id->required = true;
        $this->direction->required = false;
    }

    /**
     * Strips all values from page objects' properties.
     */
    protected function clearPages()
    {
        foreach ($this->pages as &$image) {
            $image->clearValues();
        }
    }

    /**
     * Overrides parent to fill only:
     *   - the id (of the sketchbook)
     *   - id (of the current left-hand page)
     *   - the direction of navigation
     * @param array|null[optional] $src Array of variables to use instead of POST data.
     */
    public function collectRequestData($src = null)
    {
        $this->id->collectRequestData($src);
        if ($this->id->value === null) {
            $this->id->value = Validation::collectIntegerRequestVar($this::BOOK_PARAM, null, $src);
        }
        $this->pages[0]->id->collectRequestData($src);
        $this->direction->collectFromInput($src);
    }

    /**
     * @param Album $album
     * @param ImageLink $page
     * @return string
     */
    public function formatURI(&$album, &$page = null)
    {
        if (strlen($this::$viewer_uri) < 1) {
            return ('');
        }
        $uri = $this::$viewer_uri . ((substr($this::$viewer_uri, -1) == "/") ? ("") : ("/")) . $album->slug->value;
        if (is_object($page)) {
            $uri .= "/p/{$page->id->value}";
        }
        return ($uri);
    }

    /**
     * Get the id of the first available book on the stack.
     * @throws RecordNotFoundException
     * @throws \Littled\Exception\InvalidQueryException
     */
    public function getDefaultBook()
    {
        $query = <<<SQL
SELECT 
	a.id 
FROM album a 
INNER JOIN image_link il ON (a.id = il.parent_id AND il.type_id = {$this->pageContentTypeID}) 
WHERE (a.section_id = {$this->content_properties->id->value}) 
AND (a.access = 'public') 
AND (il.access = 'public') 
AND (DATEDIFF(il.`release_date`, NOW())<=0) 
GROUP BY a.id 
HAVING COUNT(1) > 0  
ORDER BY a.slot ASC, a.id ASC 
LIMIT 1
SQL;
        $data = $this->fetchRecords($query);

        if (count($data) < 1) {
            throw new RecordNotFoundException("A default book is not available.");
        }

        /* save book id */
        $this->id->value = $data[0]->id;
    }

    /**
     * Gets the first two pages in the sequence to display.
     * @throws RecordNotFoundException
     * @throws \Littled\Exception\InvalidQueryException
     */
    public function getDefaultPages()
    {
        $query = <<<SQL
SELECT 
	il.id, 
	il.title, 
	il.description, 
	il.slot, 
	il.page_number, 
	f.path full_path, 
	f.width full_width, 
	f.height full_height 
FROM image_link il 
INNER JOIN images f ON il.fullres_id = f.id 
WHERE (il.parent_id = {$this->id->value}) 
AND (il.type_id = {$this->pageContentTypeID}) 
AND (il.access = 'public') 
AND (DATEDIFF(il.`release_date`, NOW())<=0) 
ORDER BY IFNULL(il.page_number,999999) ASC, il.slot ASC, il.id ASC 
LIMIT 4
SQL;
        $this->hydrateFromQuery($query);
        $this->markLimits();
    }

    /**
     * Given a page position (image_link.id) and a direction, retrieves the next two pages in the sequence if available.
     *   - Skips ahead to the next available spread what would be the next spread in the physical book is unavailable.
     *   - If either the left or right page is unavailable, one page is returned and the adjacent page will have no properties.
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws \Littled\Exception\InvalidQueryException
     */
    public function getNextPublicPage()
    {
        $this->getPagePosition();

        $query = <<<SQL
SELECT 
	il.id, 
	il.title, 
	il.description, 
	il.slot, 
	il.page_number, 
	f.path full_path, 
	f.width full_width, 
	f.height full_height 
FROM image_link il 
INNER JOIN images f ON il.fullres_id = f.id 
WHERE (il.id = {$this->pages[0]->id->value}) 
SQL;
        $data = $this->fetchRecords($query);
        if (count($data) > 0) {
            $this->hydratePageFromQuery($this->pages[0], $data[0]);
            $this->markLimits();
        }
    }

    /**
     * Given a page position (image_link.id) and a direction, retrieves the next two pages in the sequence if available.
     *   - Skips ahead to the next available spread what would be the next spread in the physical book is unavailable.
     *   - If either the left or right page is unavailable, one page is returned and the adjacent page will have no properties.
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws \Littled\Exception\InvalidQueryException
     */
    public function getNextPublicPageset()
    {
        $this->getPagePosition();

        $query = <<<SQL
SELECT 
	il.id, 
	il.title, 
	il.description, 
	il.slot, 
	il.page_number, 
	f.path full_path, 
	f.width full_width, 
	f.height full_height 
FROM image_link il 
INNER JOIN images f ON il.fullres_id = f.id 
WHERE (il.parent_id = {$this->id->value}) 
AND (il.type_id = {$this->pageContentTypeID}) 
AND (il.access = 'public') 
AND (DATEDIFF(il.`release_date`, NOW())<=0) 
AND
(
	(il.id = {$this->pages[0]->id->value}) 
	OR (IFNULL(il.page_number,0) > {$this->pages[0]->page_number->value}) 
	OR 
	(
		IFNULL(il.page_number,0) = {$this->pages[0]->page_number->value} 
		AND il.slot > {$this->pages[0]->slot->value}
	) 
	OR 
	(
		IFNULL(il.page_number,0) = {$this->pages[0]->page_number->value} 
		AND il.slot = {$this->pages[0]->slot->value} 
		AND il.id > {$this->pages[0]->id->value}
	)
) 
ORDER BY IFNULL(il.page_number,999999) ASC, il.slot ASC, il.id ASC 
SQL;
        if ($this->direction->value == $this::PAGE_BACK) {
            /* going backwards no need to preload extra images */
            $query .= "LIMIT 2 ";
        } else {
            /* going forward send next two images for preload */
            $query .= "LIMIT 4 ";
        }
        $this->hydrateFromQuery($query);
        $this->markLimits();
    }

    /**
     * Formats and returns as a string the title of the page spread to be used as the title of the page the spread is displayed in.
     * @return string Page spread title.
     */
    public function getPageSpreadTitle()
    {
        $title = "";
        if ($this->layout->value == $this::$one_page_layout) {
            return ("{$this->title->value} {$this->pages[0]->title->value}");
        }
        if ($this->pages[0]->title->value) {
            $title = $this->pages[0]->title->value;
        }
        if (count($this->pages) > 1) {
            if ($title) {
                $p2 = preg_replace("/\D/", "", $this->pages[1]->title->value);
                if ($p2) {
                    $title .= "/{$p2}";
                }
            } else {
                $title = $this->pages[1]->title->value;
            }
        }
        if ($this->title->value) {
            $title = "{$this->title->value} {$title}";
        }
        return ($title);
    }

    /**
     * Returns the page id to use in navigaiton links to the previous spread in
     * the album. N.B. that this value is passed along with the "back" operation
     * parameter to query for the actual previous page id when navigation is invoked.
     * So really, this is the id of the first page currently being displayed.
     * @return integer Page id to use in navigation links to the previous spread
     * in an album.
     */
    public function getFirstPageID()
    {
        return ($this->pages[0]->id->value);
    }

    /**
     * Returns the page id to use in navigaiton links to the next spread in
     * the album. N.B. that this value is passed along with the "fwd" operation
     * parameter to query for the actual next page id when navigation is invoked.
     * So really, this is the id of the last page currently being displayed.
     * @return integer Page id to use in navigation links to the next spread
     * in an album.
     */
    public function getLastPageID()
    {
        if ($this->layout->value == $this::$two_page_layout) {
            if (count($this->pages) > 1) {
                return (($this->pages[1]->id->value > 0) ? ($this->pages[1]->id->value) : ($this->pages[0]->id->value));
            } else {
                return ($this->pages[0]->id->value);
            }
        } else {
            return ($this->pages[0]->id->value);
        }
    }

    /**
     * Gets the page number and slot of the current left page.
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws \Littled\Exception\InvalidQueryException
     */
    protected function getPagePosition()
    {
        if ($this->pages[0]->id->value === null || $this->pages[0]->id->value < 1) {
            throw new ConfigurationUndefinedException("Page id not set.");
        }

        $query = <<<SQL
SELECT
	il.page_number,  
	il.slot 
FROM image_link il 
WHERE id = {$this->pages[0]->id->value}
AND (il.access = 'public') 
AND (DATEDIFF(il.release_date, NOW())<=0) 
SQL;
        $data = $this->fetchRecords($query);
        if (count($data) < 1) {
            throw new RecordNotFoundException("Page not found.");
        }

        $page = $data[0]->page_number;
        $slot = $data[0]->slot;

        if ($page === null) {
            $page = 0;
        }
        if ($slot === null) {
            $slot = 0;
        }
        if ($this->direction->value == $this::PAGE_BACK) {
            $this->loadPreviousPageSet($page, $slot);
        } elseif ($this->direction->value == $this::PAGE_FORWARD) {
            $this->loadNextPageSet($page, $slot);
        } else {
            /* request was for this pageset. done. */
            $this->pages[0]->page_number->value = $page;
            $this->pages[0]->slot->value = $slot;
        }

        if ($this->isPagingBackInTwoPageLayout()) {
            $this->loadPreviousOddPageSet();
        }
    }

    /**
     * Fills the page members of the class with the current recordset.
     * @param string $query SQL SELECT statement to use to hydrate object property values.
     * @throws RecordNotFoundException
     * @throws \Littled\Exception\InvalidQueryException
     */
    protected function hydrateFromQuery($query)
    {
        $data = $this->fetchRecords($query);
        if (count($data) < 1) {
            throw new RecordNotFoundException("Pages not found.");
        }

        $index = 0;
        $row = $data[$index];
        if (
            ($row->page_number === null && $row->slot === null) ||
            ($row->page_number === null && ($row->slot % 2) == 0) ||
            ($row->page_number % 2) == 0) {
            /* load first record into left-hand page */
            $this->hydratePageFromQuery($this->pages[0], $row);

            /* load next record into right-hand page only if its page number is adjacent */
            $index += 1;
            $row = $data[$index];
            if ($row && (
                    ($row->page_number === null && $row->slot == $this->pages[0]->slot->value + 1) ||
                    ($row->page_number == $this->pages[0]->page_number->value + 1))) {
                $this->hydratePageFromQuery($this->pages[1], $row);
            }
        } else {
            if ($this->layout->value == $this::$one_page_layout) {
                /* single-page spread. load the page properties into the
                 * first page in the array.
                 */
                $this->hydratePageFromQuery($this->pages[0], $row);
            } else {
                /*
                 * left-hand page should be marked as unavailable if it isn't even
                 * load current page properties into right-hand page
                 */
                $this->pages[0]->id->value = null;
                $this->pages[0]->page_number->value = null;
                $this->pages[0]->slot->value = null;
                $this->hydratePageFromQuery($this->pages[1], $row);
            }
        }

        /* advance to get images to pre-load, if available */
        $index += 1;
        if (count($data) <= $index) {
            return;
        }
        $this->hydratePageFromQuery($this->pages[2], $data[$index]);

        $index += 1;
        if (count($data) <= $index) {
            return;
        }
        $this->hydratePageFromQuery($this->pages[3], $data[$index]);
    }

    /**
     * Fills a single page (ImageLink) object with data from a single row in a recordset.
     * @param ImageLink $page Page object that will be filled with page properties.
     * @param object $row Object containing data to assign the object property values.
     */
    protected function hydratePageFromQuery(&$page, &$row)
    {
        $page->id->value = $row->id;
        $page->title->value = $row->title;
        $page->description->value = $row->description;
        $page->slot->value = $row->slot;

        $page->full->path->value = $row->full_path;
        $page->full->width->value = $row->full_width;
        $page->full->height->value = $row->full_height;
    }

    /**
     * Returns TRUE/FALSE depending on whether another page spread exists in the album.
     * @return bool TRUE/FALSE indicating there is another spread in the album.
     */
    public function isAtFirstSpread()
    {
        switch ($this->layout->value) {
            case $this::$two_page_layout:
                return ($this->pages[0]->is_first_page->value == true || (count($this->pages) > 1 && $this->pages[1]->is_first_page->value == true));
            default:
                return ($this->pages[0]->is_first_page->value == true);
        }
    }

    /**
     * Returns TRUE/FALSE depending on whether another page spread exists in the album.
     * @return bool TRUE/FALSE indicating there is another spread in the album.
     */
    public function isAtLastSpread()
    {
        switch ($this->layout->value) {
            case $this::$two_page_layout:
                return ($this->pages[0]->is_last_page->value == true || (count($this->pages) > 1 && $this->pages[1]->is_last_page->value == true));
            default:
                return ($this->pages[0]->is_last_page->value == true);
        }
    }

    /**
     * Tests if current layout is a two-page layout, and if the user is navigating backwards through the pages, and the
     * user is not currently at the first page in the sequence, e.g. there is a page exists to navigate back to.
     * @return bool True if conditions are met.
     */
    protected function isPagingBackInTwoPageLayout()
    {
        return ($this->layout->value == $this::$two_page_layout &&
            ($this->direction->value == $this::PAGE_BACK || $this->direction->value == "") &&
            (($this->pages[0]->page_number->value > 1 && ($this->pages[0]->page_number->value % 2) == 1)) ||
            (empty($this->pages[0]->page_number->value) && empty($prev_page) && ($this->pages[0]->slot->value % 2) == 0));
    }

    /**
     * @param $page
     * @param $slot
     * @throws \Littled\Exception\InvalidQueryException
     */
    protected function loadNextPageSet(&$page, $slot)
    {
        $query = <<<SQL
SELECT 
	il.id, 
	il.page_number,
	il.slot
FROM image_link il
INNER JOIN images f ON il.fullres_id = f.id 
WHERE (il.parent_id = {$this->id->value}) 
AND (il.type_id = {$this->pageContentTypeID}) 
AND (il.access = 'public') 
AND (DATEDIFF(il.release_date, NOW())<=0) 
AND 
(
	(IFNULL(il.page_number,0) > {$page}) 
	OR
	(
		IFNULL(il.page_number,0) = {$page} 
		AND il.slot > {$slot} 
	)
	OR
	(
		IFNULL(il.page_number,0) = {$page} 
		AND il.slot = {$slot} 
		AND il.id > {$this->pages[0]->id->value} 
	)
) 
ORDER BY IFNULL(il.page_number,999999) ASC, il.slot ASC, il.id ASC 
LIMIT 1 
SQL;
        $data = $this->fetchRecords($query);
        if (count($data) < 1) {
            return;
        }
        $id = $data[0]->id;
        $next_page = $data[0]->page_number;
        $next_slot = $data[0]->slot;

        if ($id > 0) {
            $this->pages[0]->id->value = $id;
            $this->pages[0]->page_number->value = (($next_page !== null) ? ($next_page) : (0));
            $this->pages[0]->slot->value = (($next_slot !== null) ? ($next_slot) : (0));
        } else {
            $this->pages[0]->page_number->value = $page;
            $this->pages[0]->slot->value = $slot;
        }
    }

    /**
     * @throws \Littled\Exception\InvalidQueryException
     */
    protected function loadPreviousOddPageSet()
    {
        /**
         * when paging back, or loading a page directly
         * the left-hand page number should be even
         * or if the page number is unavailable the slot should be odd
         */
        $adjacent_slot = $this->pages[0]->slot->value - 1;
        $adjacent_page = $this->pages[0]->page_number->value - 1;
        $query = <<<QUERY
SELECT 
	il.id, 
	il.page_number,
	il.slot
FROM image_link il
WHERE (il.parent_id = {$this->id->value}) 
AND (il.type_id = {$this->pageContentTypeID}) 
AND (il.access = 'public') 
AND (DATEDIFF(il.release_date, NOW())<=0) 
QUERY;
        if (empty($this->pages[0]->page_number->value) && empty($prev_page)) {
            $query .= "AND (il.slot = {$adjacent_slot}) ";
        } else {
            $query .= "AND (il.page_number = {$adjacent_page}) ";
        }
        $query .= <<<QUERY
ORDER BY il.slot DESC, il.id DESC 
LIMIT 1 
QUERY;
        $data = $this->fetchRecords($query);
        if (count($data) < 1) {
            return;
        }
        $id = $data[0]->id;
        $page = $data[0]->page_number;
        $slot = $data[0]->slot;

        if ($id > 0) {
            $this->pages[0]->id->value = $id;
            $this->pages[0]->page_number->value = (($page !== null) ? ($page) : (0));
            $this->pages[0]->slot->value = (($slot !== null) ? ($slot) : (0));
        }
    }

    /**
     * @param $page
     * @param $slot
     * @throws \Littled\Exception\InvalidQueryException
     */
    protected function loadPreviousPageSet(&$page, $slot)
    {
        /* request was for previous pageset back in the sequence */
        $query = <<<SQL
SELECT 
	il.id, 
	il.page_number,
	il.slot
FROM image_link il
INNER JOIN images f ON il.fullres_id = f.id 
WHERE (il.parent_id = {$this->id->value}) 
AND (il.type_id = {$this->pageContentTypeID}) 
AND (il.access = 'public') 
AND (DATEDIFF(il.release_date, NOW())<=0) 
AND 
(
	(IFNULL(il.page_number,0) < {$page}) 
	OR
	(
		IFNULL(il.page_number,0) = {$page} 
		AND il.slot < {$slot} 
	)
	OR
	(
		IFNULL(il.page_number,0) = {$page} 
		AND il.slot = {$slot} 
		AND il.id < {$this->pages[0]->id->value} 
	)
) 
ORDER BY IFNULL(il.page_number,999999) DESC, il.slot DESC, il.id DESC 
LIMIT 1 
SQL;
        $data = $this->fetchRecords($query);
        if (count($data) < 1) {
            return;
        }

        $id = $data[0]->id;
        $prev_page = $data[0]->page_number;
        $prev_slot = $data[0]->slot;

        if ($id > 0) {
            $this->pages[0]->id->value = $id;
            $this->pages[0]->page_number->value = (($prev_page !== null) ? ($prev_page) : (0));
            $this->pages[0]->slot->value = (($prev_slot !== null) ? ($prev_slot) : (0));
        } else {
            $this->pages[0]->page_number->value = $page;
            $this->pages[0]->slot->value = $slot;
        }
    }

    /**
     * Populates the core properties of the sketchbook object with data from database.
     * @param bool[optional] $read_images Ignored. Here for compatibility with parent class function definition.
     * @param bool[optional] $read_image_keywords Ignored. Here for compatibility with parent class function definition.
     * @throws ContentValidationException
     * @throws \Littled\Exception\ConfigurationUndefinedException
     * @throws \Littled\Exception\ConnectionException
     * @throws \Littled\Exception\InvalidQueryException
     * @throws \Littled\Exception\InvalidTypeException
     * @throws \Littled\Exception\NotImplementedException
     * @throws \Littled\Exception\RecordNotFoundException
     */
    function read($read_images = true, $read_image_keywords = false)
    {
        /* don't retrieve page images */
        parent::read(false, false);
    }

    /**
     * Overrides parent to check only for book id, page id, and direction.
     * @param array[optional] $exclude_properties
     * @throws ContentValidationException
     */
    public function validateInput($exclude_properties = array())
    {
        try {
            $this->id->validate();
        } catch (ContentValidationException $ex) {
            array_push($this->validationErrors, $ex->getMessage());
        }
        try {
            $this->pages[0]->id->validate();
        } catch (ContentValidationException $ex) {
            array_push($this->validationErrors, $ex->getMessage());
        }
        try {
            $this->direction->validate();
        } catch (ContentValidationException $ex) {
            array_push($this->validationErrors, $ex->getMessage());
        }
        if ($this->hasValidationErrors()) {
            throw new ContentValidationException("Error validating album viewer data.");
        }
    }
}