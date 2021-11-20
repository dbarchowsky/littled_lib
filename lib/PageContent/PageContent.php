<?php
namespace Littled\PageContent;


use Littled\App\LittledGlobals;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\FilterCollection;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\RequestInput;

/**
 * Class PageContent
 * Static utility routines for rendering page content.
 * @package Littled\PageContent
 */
class PageContent
{
	/** @var SerializedContent Page content. */
	public $content;
	/** @var FilterCollection Content filters. */
	public $filters;
	/** @var string Query string containing variables defining page state. */
	public $qs;
	/** @var string Token representing the current action to take on the page. */
	public $action;
	/** @var string URL to use for redirects. */
	public $redirectURL;
	/** @var string Path to template file. */
	public $templatePath;


	/**
	 * PageContent constructor
	 */
	function __construct()
	{
		$this->content = null;
		$this->filters = null;
		$this->qs = '';
		$this->templatePath = '';
		$this->action = '';
		$this->redirectURL = '';
	}

	/**
	 * @param array|null[optional] $src Array of variables to use in place of POST data.
	 * Sets the value of the object's $action property based on action variables in POST data.
	 */
	public function collectEditAction( $src=null )
	{
		if ($src===null) {
			$src = $_POST;
		}
		if(array_key_exists(LittledGlobals::P_CANCEL, $src)) {
			$this->action = filter_var($src[LittledGlobals::P_CANCEL], FILTER_SANITIZE_STRING);
		}
		if ($this->action) {
			$this->action = "cancel";
		}
		else {
			if(array_key_exists(LittledGlobals::P_COMMIT, $src)) {
				$this->action = filter_input(INPUT_POST, LittledGlobals::P_COMMIT, FILTER_SANITIZE_STRING);
			}
			if ($this->action) {
				$this->action = "commit";
			}
		}
	}

	/**
	 * Uses current filter values to generate a query string that
	 * will preserver the current page state. The query string value is
	 * stored as the value of the object's $qs property.
	 */
	public function formatPageStateQueryString() {
		$this->qs = $this->filters->formatQueryString();
	}

	/**
	 * Inserts data into a template file and stores the resulting content in the object's $content property.
	 * @param string $template_path Path to content template file.
	 * @param array|null $context Array containing data to insert into the template.
	 * @return string Markup with content inserted into it.
	 * @throws ResourceNotFoundException If the requested template file cannot be located.
	 */
	public static function loadTemplateContent(string $template_path, array $context=null ): string
    {
	    if (substr($template_path, 0, 1) == '/') {
	        if ($_SERVER['DOCUMENT_ROOT']) {
	            if (strpos($template_path, $_SERVER['DOCUMENT_ROOT']) !== 0) {
                    $template_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$template_path;
                }
            }
        }
		if (!file_exists($template_path)) {
			if ($template_path) {
				throw new ResourceNotFoundException("Template \"" . basename($template_path) . "\" not found.");
			}
			else {
				throw new ResourceNotFoundException("Template not found.");
			}
		}
		if (is_array($context)) {
			foreach($context as $key => $val) {
				${$key} = $val;
			}
		}
		ob_start();
		include($template_path);
		$markup = ob_get_contents();
		ob_end_clean();

		return ($markup);
	}

	/**
	 * Sets $qs property value to preserve initial GET variable values.
	 * @param array $page_vars Array of input_class objects used to collect page variable values
	 * to store in query string.
     * @throws NotImplementedException
     */
	protected function preservePageVariables(array $page_vars )
	{
		$qs_vars = array();
		foreach($page_vars as $input) {
			/** @var RequestInput $input */
			$input->collectRequestData();
			if ($input->value===true) {
				array_push($qs_vars, "$input->key=1");
			}
			elseif(strlen($input->value) > 0) {
				array_push($qs_vars, "$input->key=".urlencode($input->value));
			}
		}
		if (count($qs_vars) > 0) {
			$this->qs = '?'.implode('&', $qs_vars);
		}
	}

    /**
     * Inserts error message into DOM.
     * @param string $msg Error message to print out.
     * @param string[optional] $fmt Format to use to print out error message. Overrides the default format.
     * @param string[optional] $encoding Defaults to 'UTF-8'
     */
	public static function printError(string $msg, $fmt='', $encoding="UTF-8")
    {
        if (!$fmt) {
            $fmt = "<div class=\"alert alert-error\">%s</div>";
        }
        printf($fmt, htmlspecialchars($msg, ENT_QUOTES, $encoding));
    }

	/**
	 * Inserts data into a template file and renders the result.
	 * @param string $template_path Path to template to render.
	 * @param ?array $context Data to insert into the template.
	 * @throws ResourceNotFoundException If the requested template file cannot be located.
	 */
	public static function render( string $template_path, ?array $context=null )
	{
		if (!file_exists($template_path)) {
			if ($template_path) {
				throw new ResourceNotFoundException("Template \"" . basename($template_path) . "\" not found.");
			}
			else {
				throw new ResourceNotFoundException("Template not found.");
			}
		}
		if (is_array($context)) {
			foreach($context as $context_key => $context_value) {
				${$context_key} = $context_value;
			}
		}
		include ($template_path);
	}

	/**
	 * Inserts data into a template file and renders the result. Catches exceptions and prints error messages directly to the DOM.
	 * @param string $template_path Path to template to render.
	 * @param array|null $context Data to insert into the template.
	 * @param string[optional] $css_class CSS class to apply to the error message container element.
	 * @param string[optional] $encoding Defaults to 'UTF-8'
	 */
	public static function renderWithErrors(string $template_path, array $context=null, $css_class=null, $encoding="UTF-8")
	{
		try {
			PageContent::render($template_path, $context);
		}
		catch(ResourceNotFoundException $ex) {
			PageUtils::showError($ex->getMessage(), $css_class, $encoding);
		}
	}

	/**
     * @deprecated Use .htaccess directive instead.
	 * Forces a page to use https protocol.
	 * @param bool[optional] $bypass_on_dev Flag to skip this in dev environment.
	 */
	public static function require_ssl( $bypass_on_dev=true )
	{
		if ($bypass_on_dev===true && defined('IS_DEV') && IS_DEV===true) {
			return;
		}
		if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
			header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}

	/**
	 * Sets the error message to display on a page.
	 * @param string $error_msg string
	 */
	public function setPageError(string $error_msg ) {
		array_push($this->content->validationErrors, $error_msg);
	}
}
