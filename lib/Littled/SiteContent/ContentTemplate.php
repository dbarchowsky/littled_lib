<?php
namespace Littled\SiteContent;


use Littled\Exception\ContentValidationException;
use Littled\Request\IntegerInput;
use Littled\Request\StringSelect;
use Littled\Request\StringTextField;

/**
 * Class ContentTemplate
 * @package Littled\SiteContent
 */
class ContentTemplate extends ContentOperations
{
	/** @var string Root directory of the content type, as specified in the parent site_section table. */
	public $rootPath;
	/** @var IntegerInput Record id. */
	public $id;
	/** @var StringSelect Location of the template, e.g. local vs. shared. */
	public $location;
	/** @var StringTextField Template name. */
	public $name;
	/** @var StringTextField Relative path to the content template. */
	public $path;
	/** @var IntegerInput Pointer to $site_section_id property */
	public $parent_id;
	/** @var IntegerInput Content type id. */
	public $site_section_id;

	Const SECTION_ID = 33;
	public static function TABLE_NAME() { return ("content_template"); }

	/**
	 * ContentTemplate constructor.
	 *
	 * @param int|null $id Initial record id
	 * @param int|null $parent_id Initial parent id value
	 * @param string $name Initial name value
	 * @param string $root_path Initial base directory value
	 * @param string $path Initial template path value
	 * @param string $location Initial template location value
	 */
	function __construct( $id=null, $parent_id=null, $name='', $root_path='', $path='', $location='')
	{
		parent::__construct();

		$this->id = new IntegerInput("Template id", "templateID", $id, false);
		$this->site_section_id = new IntegerInput("templateParentid", "contentTypeID", $parent_id, true);
		$this->name = new StringTextField("Name", "templateName", $name, true, 45);
		$this->path = new StringTextField("Path", "templatePath", $path, true, 255);
		$this->location = new StringTextField("Location", "templateLocation", $location, false, 20);

		/* pointer to site section id for the benefit of editing these
		 * records in the CMS */
		$this->parent_id = &$this->site_section_id;

		/* ensure this has a trailing slash */
		$this->rootPath = rtrim($root_path, '/') . '/';
	}

	/**
	 * Format full path to template file as a convenience, pointing it at
	 * either the common directory or local depending on the "location" value.
	 * @return string Full path to template file, taking into account if the location
	 * is set to the shared location or the local location.
	 */
	public function fullPath()
	{
		$shared_template_dir = $this->getAppSetting('LITTED_TEMPLATE_DIR');
		$app_template_dir = $this->getAppSetting('APP_TEMPLATE_DIR');
		
		$path = $this->path->value;
		if ($path) {
			switch ($this->location->value)
			{
				case 'shared':
					$path = $shared_template_dir.$path;
					break;
				default:
					if ($this->rootPath) {
						$path = $app_template_dir.ltrim($this->rootPath, '/') . ltrim($path,'/');
					}
					break;
			}
		}
		return ($path);
	}

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return bool Returns TRUE if editing an existing record, a title has been entered, or if any gallery images have been uploaded. Most likely should be overridden in derived classes.
	 */
	public function hasData()
	{
		return ($this->id->value > 0 || $this->path->value || $this->name->value);
	}

	/**
	 * Validates form data collected by the object.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function validateInput()
	{
		$form_errors = '';
		try {
			parent::validateInput();
		}
		catch (ContentValidationException$ex) {
			$form_errors .= $ex->getMessage();
		}
		if ($this->id->value===null && $this->site_section_id->value > 0 && $this->name->value) {
			$this->connectToDatabase();
			$table = self::TABLE_NAME();
			$escaped_name = $this->name->escapeSQL($this->mysqli);
			$query = <<<SQL
SELECT t.`id`, s.`name` as `section`
FROM `{$table}` t 
INNER JOIN `site_section` s on t.site_section_id = s.id  
WHERE (t.site_section_id = {$this->site_section_id->value}) 
AND (t.`name` = {$escaped_name});
SQL;
			$id = 0;
			$data = $this->fetchRecords($query);
			if (count($data) > 0) {
				$id = $data[0]->id;
				$section = $data[0]->section;
			}
			if ($id > 0) {
				$form_errors .= "A template named \"{$this->name->value}\" already exists under {$section}. \n";
			}
		}
		if ($form_errors) {
			throw new ContentValidationException($form_errors);
		}
	}
}