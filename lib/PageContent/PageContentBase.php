<?php
namespace Littled\PageContent;

use Littled\App\LittledGlobals;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;
use LittledCommon\FormData\input_class;

/**
 * Class PageContentBase
 * Intended as a base utility class for managing and rendering content for different types of pages.
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
	public $redirect_url;
	/** @var string Path to template file. */
	public $template_path;
	/** @var string Query string to attach to page links. */
	protected $query_string;

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
	    $this->template_path = '';
	    $this->action = '';
	    $this->redirect_url = '';
	    $this->query_string = '';
    }

	/**
	 * Sets the id property value of the object's content from request variable values, e.g. GET, POST, etc.
	 * First checks if a variable named "id" is present. 2nd, checks for a variable corresponding to the content
	 * object's id's internal parameter name.
	 * @return ?int Record id value that was found, or null if no valid integer value was found for the content id.
	 */
	public function collectContentId(): ?int
	{
		$this->content->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_PARAM);
		if ($this->content->id->value===null) {
			if ( $this->content->id instanceof RequestInput) {
				$this->content->id->collectValue();
			}
			elseif ($this->content->id instanceof input_class){
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
	 * Assigns JSON request data values to object properties.
	 * @param object $data
	 */
	public function collectJsonRequestData(object $data)
	{
		foreach($this as $item) {
			if (is_object($item) && method_exists($item, 'collectJsonRequestData')) {
				/** @var RequestInput $item */
				$item->collectJsonRequestData($data);
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
	protected function preservePageVariables(array $page_vars)
	{
		$qs_vars = array();
		foreach($page_vars as $input) {
			if ( $input instanceof RequestInput) {
				$input->collectRequestData();
			}
			elseif ($input instanceof input_class) {
				/* @todo remove this after common_lib is removed from all projects */
				$input->fill_from_input();
			}
			if ($input->value===true) {
				array_push($qs_vars, "$input->key=1");
			}
			elseif(strlen($input->value) > 0) {
				array_push($qs_vars, "$input->key=" . urlencode($input->value));
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
	 * @param bool $bypass_on_dev Flag to skip this in dev environment.
	 */
	public static function requireSSL( bool $bypass_on_dev=true )
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
     * @param ?string $p_template_path Path to template to render.
     * @param ?array $context Data to insert into the template.
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
	public function render( ?string $p_template_path=null, ?array $context=null )
	{
		if ($p_template_path===null || $p_template_path==='') {
		    if ($this->template_path===null || strlen(trim($this->template_path)) < 1) {
		        throw new ConfigurationUndefinedException("Template not set.");
		    }
			$p_template_path = $this->template_path;
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
	 * @param ?string $template_path Path to template to render.
	 * @param ?array $context Data to insert into the template.
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
	 */
	public function sendResponse( ?string $template_path=null, ?array $context=null )
	{
		$this->render($template_path, $context);
	}

    /**
     * Sets page properties.
     */
    public function setPageProperties(): void { }
}
