<?php
namespace Littled\PageContent;

/**
 * Class PageContentBase
 * Intended as a base utility class for managing and rendering content for different types of pages.
 * @todo have it inherit from base database connection class
 * @package Littled\Content
 */
class PageContentBase /* extends DBConnection */
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

    /**
     * class constructor
     */
    function __construct()
    {
	    $this->content = null;
	    $this->filters = null;
	    $this->qs = '';
	    $this->template_path = '';
	    $this->action = '';
	    $this->redirect_url = '';
    }

    /**
     * Sets the value of the object's $action property based on
     * action variables in POST data.
     */
    public function collectEditAction()
    {
        if (!defined('P_CANCEL') || !defined('P_COMMIT')) {
            return;
        }
        $this->action = filter_input(INPUT_POST, P_CANCEL, FILTER_SANITIZE_STRING);
        if ($this->action) {
            $this->action = "cancel";
        }
        else {
            $this->action = filter_input(INPUT_POST, P_COMMIT, FILTER_SANITIZE_STRING);
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
    public function formatPageStateQueryString()
    {
        $this->qs = $this->filters->format_query_string();
    }

	/**
	 * Sets $qs property value to preserve initial GET variable values.
	 * @param array $page_vars Array of Input objects used to collect page variable values
	 * to store in query string.
	 */
	protected function preserve_page_variables( $page_vars )
	{
		$qs_vars = array();
		foreach($page_vars as $input) {
			$input->fillFromInput();
			if ($input->value===true) {
				array_push($qs_vars, "{$input->param}=1");
			}
			elseif(strlen($input->value) > 0) {
				array_push($qs_vars, "{$input->param}=".urlencode($input->value));
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
    public function setPageError( $error_msg )
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
     * @param string $p_template_path Path to template to render.
     * @param array $context Data to insert into the template.
     */
	public function render( $p_template_path=null, $context=null )
	{
		if ($p_template_path===null || $p_template_path==='') {
			$p_template_path = $this->template_path;
		}
		if (is_array($context)) {
			foreach($context as $key => $val) {
				${$key} = $val;
			}
		}
		include ($p_template_path);
	}
}
