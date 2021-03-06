<?php
namespace Littled\PageContent;

use Littled\App\LittledGlobals;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

/**
 * Class PageContentBase
 * Intended as a base utility class for managing and rendering content for different types of pages.
 * @todo have it inherit from base database connection class
 * @package Littled\Content
 */
class PageContentBase extends MySQLConnection
{
    /** @var object Page content. */
    public $content;
    /** @var object Content filters. */
    public $filters;
    /** @var string Query string containing variables defining page state. */
    public $qs;
    /** @var string Token representing the current action to take on the page. */
    public $action;
	/** @var string URL to use for redirects. */
	public $redirectURL;
	/** @var string Path to template file. */
	public $templatePath;
	/** @var string Query string to attach to page links. */
	protected $queryString;

	const CANCEL_ACTION = "cancel";
	const COMMIT_ACTION = "commit";

    /**
     * class constructor
     */
    function __construct()
    {
    	parent::__construct();
	    $this->content = null;
	    $this->filters = null;
	    $this->qs = '';
	    $this->templatePath = '';
	    $this->action = '';
	    $this->redirectURL = '';
	    $this->queryString = '';
    }

	/**
	 * Sets the id property value of the object's content from request variable values, e.g. GET, POST, etc.
	 * First checks if a variable named "id" is present. 2nd, checks for a variable corresponding to the content
	 * object's id's internal parameter name.
	 * @return int|null Id value that was found, or null if no valid integer value was found for the content id.
	 */
	public function collectContentId()
	{
		$this->content->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_PARAM);
		if ($this->content->id->value===null) {
			if ( $this->content->id instanceof RequestInput) {
				$this->content->id->collectValue();
			}
			else {
				/* @todo remove this call after older version of IntegerInput class is fully removed from all apps */
				$this->content->id->fill_from_input();
			}
		}
		return ($this->content->id->value);
	}

    /**
     * Sets the value of the object's $action property based on
     * action variables in POST data.
     */
    public function collectEditAction()
    {
        $this->action = filter_input(INPUT_POST, LittledGlobals::P_CANCEL, FILTER_SANITIZE_STRING);
        if ($this->action) {
            $this->action = $this::CANCEL_ACTION;
        }
        else {
            $this->action = filter_input(INPUT_POST, LittledGlobals::P_COMMIT, FILTER_SANITIZE_STRING);
            if ($this->action) {
                $this->action = $this::COMMIT_ACTION;
            }
        }
    }

    /**
     * Uses current filter values to generate a query string that
     * will preserver the current page state. The query string value is
     * stored as the value of the object's $qs property.
     */
    public function formatPageStateQueryString()
    {
        $this->qs = $this->filters->format_query_string();
    }

	/**
	 * Sets $qs property value to preserve initial GET variable values.
	 * @param array $page_vars Array of Input objects used to collect page variable values
	 * to store in query string.
	 * @throws NotImplementedException Parameter names not defined.
	 */
	protected function preservePageVariables($page_vars )
	{
		$qs_vars = array();
		foreach($page_vars as $input) {
			/** @var $input RequestInput */
			if ( $input instanceof RequestInput) {
				$input->collectPostData();
			}
			else {
				/* @todo remove this after common_lib is removed from all projects */
				$input->fill_from_input();
			}
			if ($input->value===true) {
				array_push($qs_vars, "{$input->key}=1");
			}
			elseif(strlen($input->value) > 0) {
				array_push($qs_vars, "{$input->key}=" . urlencode($input->value));
			}
		}
		if (count($qs_vars) > 0) {
			$this->qs = '?'.implode('&', $qs_vars);
		}
	}

	/**
     * Sets the error message to display on a page.
     * @param string $error_msg string
     */
    public function setPageError( string $error_msg )
    {
        $this->content->errorString = $error_msg;
    }

	/**
	 * Forces a page to use https protocol.
	 * @param boolean $bypass_on_dev Flag to skip this in dev environment.
	 */
	public static function requireSSL( $bypass_on_dev=true )
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
     * Inserts data into a template file and renders the result.
     * @param string|null $p_template_path Path to template to render.
     * @param array|null $context Data to insert into the template.
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
	public function render( $p_template_path=null, $context=null )
	{
		if ($p_template_path===null || $p_template_path==='') {
		    if ($this->templatePath===null || strlen(trim($this->templatePath)) < 1) {
		        throw new ConfigurationUndefinedException("Template not set.");
		    }
			$p_template_path = $this->templatePath;
		}
		if (!file_exists($p_template_path)) {
		    throw new ResourceNotFoundException("Template not found.");
        }
		if (!is_array($context)) {
			$context = array(
				'content' => &$this->content,
				'filters' => &$this->filters,
				'qs' => $this->qs
			);
			if (is_object($this->content)) {
				if (property_exists($this->content, 'errorString')) {
					$context['page_errors'] = $this->content->errorString;
				}
				elseif(property_exists($this->content, 'error_string')) {
					$context['page_errors'] = $this->content->error_string;
				}
			}
		}
		foreach($context as $key => $val) {
			${$key} = $val;
		}
		include ($p_template_path);
	}

	/**
	 * Prevents any variable values that were previously cached from being passed along to subsequent pages.
	 */
	public function resetPageVariables()
	{
		$this->qs = '';
	}

	/**
	 * Inserts data into a template file and renders the result. Alias for class's render() method.
	 * @param string|null[optional] $template_path Path to template to render.
	 * @param array|null[optional] $context Data to insert into the template.
	 */
	public function sendResponse( $template_path=null, $context=null )
	{
		$this->render($template_path, $context);
	}
}
