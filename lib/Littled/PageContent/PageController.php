<?php
namespace Littled\PageContent;


use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;

class PageController extends MySQLConnection
{
	/** @var string Original URL before any RewriteRules */
	public $original_uri;
	/** @var string Section slug as extracted from the original url */
	public $section_slug;
	/** @var string Album slug as extracted from the original url */
	public $album_slug;
	/** @var integer Site section id matching the section slug value and record in the site_section table */
	public $section_id;
	/** @var integer Album id matching the section slug value, album slug value, and record in the album table */
	public $album_id;
	/** @var string Name of the table storing the album slug. */
	public $table;
	/** @var string Path to the content's root directory, relative to the web root. */
	public $section_base_path;

	/**
	 * Class constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->section_id = null;
		$this->album_id = null;
		$this->original_uri = "";
		$this->section_slug = "";
		$this->album_slug = "";
		$this->table = "";
		$this->section_base_path = "";
	}

	/**
	 * Tests the object's "original_uri" property value against a string to
	 * see if they match. Test is case-insenstive. Intended to be used as a
	 * callback for PHP's built-in array_filter() routine.
	 * @param string $value String to test against the original uri value.
	 * @return boolean TRUE/FALSE if the value matches the original uri value.
	 */
	protected function testURIMatch($value) {
		return ($this->original_uri == strtolower($value));
	}

	/**
	 * Retrieves the URI entered into the browser before any RewriteRules, to be
	 * used to determine how to serve the response.
	 * Stores the URI in the object's "original_uri" property.
	 */
	public function collectOriginalURI()
	{
		/* mod_php */
		$this->original_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
		if (!$this->original_uri) {
			/* CGI/FastCGI */
			$this->original_uri = filter_input(INPUT_SERVER, 'REDIRECT_URL', FILTER_SANITIZE_STRING);
		}
		if (($q_pos = strpos($this->original_uri, '?')) !== false) {
			$this->original_uri = substr($this->original_uri, 0, $q_pos);
		}
	}

	/**
	 * Extracts an album slug from the current request URI.
	 * @param array $exclude List of paths that will not trigger a redirect.
	 */
	public function collectAlbumSlug( $exclude )
	{
		$this->collectOriginalURI();
		$uri_filter = array($this, 'uri_filter');
		if (count(array_filter($exclude, $uri_filter)) == 0) {
			list($this->section_slug, $this->album_slug) = explode('/', trim($this->original_uri, '/'));
		}
	}

	/**
	 * Retrieves album properties (album slug & id) using the referring uri.
	 * - If a matching album record is found the slug and id values will be stored
	 * in the object's $album_slug and $album_id properties, and the section
	 * id and slug will be stored in the object's $section_id and $section_slug properties.
	 * @param array $exclude List of paths that will not trigger a redirect.
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function collectAlbumProperties( $exclude )
	{
		$this->collectAlbumSlug($exclude);
		if ($this->section_slug && $this->album_slug) {
			$this->lookupSectionProperties();
			if ($this->section_id > 0) {
				$this->lookupAlbumProperties();
			}
		}
	}

	/**
	 * Looks up matching site section records using the $slug value.
	 * - Returns path to the site section content on the server.
	 * - Stores the site section record id in the object's "section_id" property.
	 * - Stores the path to the section base directory in the object's $section_base_path property.
	 * @param string[optional] $slug Sets the object's internal $section_slug
	 * property and uses it to look up the site section record. If not provided,
	 * the current value of the object's $section_slug property will be used.
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function lookupSectionProperties( $slug='' )
	{
		if ($slug) {
			$this->section_slug = $slug;
		}
		if (!$this->section_slug) {
			throw new ContentValidationException("Section slug not provided.");
		}

		/* test if the slug matches any existing site sections */
		$query = "SELECT `id`,`table`,`root_dir`,`sub_dir` FROM `site_section` WHERE `slug` LIKE ".$this->escapeSQLValue($this->section_slug);
		$data = $this->fetchRecords($query);
		if (count($data) < 0) {
			throw new RecordNotFoundException("Error retrieving content properties.");
		}
		$this->section_id = $data[0]->id;
		$this->table = $data[0]->table;
		$root_dir = $data[0]->root_dir;
		$sub_dir = $data[0]->sub_dir;

		$this->setSectionBasePath($root_dir, $sub_dir);
	}

	/**
	 * Looks up album record using the slug value.
	 * - Stores album record id in the object's $album_id property.
	 * @param string[optional] $slug Sets the object's internal $album_slug property
	 * to this value if provided. If not provided the current $album_slug value
	 * is used to look up the album record.
	 * @param int[optional] $section_id Content type id used to search album records.
	 * If a value is not provided, then the object's internal "section_id" property
	 * value is used to lookup the album content type.
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function lookupAlbumProperties( $slug='', $section_id=null )
	{
		if ($section_id > 0) {
			$this->section_id = $section_id;
		}
		if ($this->section_id === null || $this->section_id < 1) {
			throw new ContentValidationException("Content properties not set. ");
		}
		if ($slug) {
			$this->album_slug = $slug;
		}

		$this->connectToDatabase();
		$escaped_slug = $this->escapeSQLValue($this->album_slug);
		$query = "SEL"."ECT `id` FROM `{$this->table}` WHERE (`slug` LIKE '{$escaped_slug}')";
		if ($this->columnExists('section_id', $this->table)) {
			$query .= "AND (section_id = {$this->section_id})";
		}

		$data = $this->fetchRecords($query);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Error retrieving album properties.");
		}
		$this->album_id = $data[0]->id;
	}

	/**
	 * - Retrieve original URL before any RewriteRules.
	 * - Extract section and page slugs from the original URL.
	 * - Redirect to either the section or the page within the section using the
	 * section and page slugs.
	 * @param array $exclude Array of values representing the URL of the
	 * current page. These values are matched against the orininal url value and
	 * if they don't match a redirect to the requested content will be attempted.
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function testForRedirect( $exclude )
	{
		$this->collectAlbumProperties($exclude);
		if ($this->section_base_path && $this->album_id > 0) {
			/* redirect to the matching URI */
			$this->doRedirect($this->formatAlbumURI());
		}
		elseif ($this->album_slug && ($this->album_id===null || $this->album_id < 1)) {
			/* album not found */
			$this->send404();
		}
		elseif ($this->section_slug && ($this->section_id===null || $this->section_id < 1)) {
			/* content type not found */
			$this->send404();
		}
	}

	/**
	 * Redirects to the requested URI.
	 * @param string $uri URI to redirect to.
	 */
	public function doRedirect( $uri )
	{
		/* redirect to the requested page */
		header("Location: {$uri}\n\n");
		exit;
	}

	/**
	 * Sends 404 error response.
	 */
	public function send404()
	{
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	/**
	 * Formats path to content root directory, relative to the web root directory.
	 * Stores the result in the object's $section_base_path property.
	 * @param string $base_path Path to the content section's base directory,
	 * relative to the web root.
	 * @param string $subdirectory (Optional) subdirectory name.
	 */
	public function setSectionBasePath( $base_path, $subdirectory='' )
	{
		$this->section_base_path = "";
		if ($base_path) {
			$this->section_base_path = '/'.trim($base_path, '/').'/';
			if ($subdirectory) {
				$this->section_base_path .= trim($subdirectory).'/';
			}
		}
	}

	/**
	 * Formats URI to album details page using internal path and album id property values.
	 * @return string Album details URI.
	 * @throws NotImplementedException
	 */
	public function formatAlbumURI ()
	{
		/** TODO Uncomment the return statement when AlbumViewer class is available. */
		throw new NotImplementedException("PageController::formatAlbumURI not implemented.");
		/* return("{$this->section_base_path}?".AlbumViewer::BOOK_PARAM."={$this->album_id}"); */
	}
}