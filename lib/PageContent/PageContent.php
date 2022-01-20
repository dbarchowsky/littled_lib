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
 * @package Littled\Content
 */
class PageContent extends MySQLConnection
{
    /** @var string Token representing the current action to take on the page. */
    public $action;
    /** @var object Page content. */
    public $content;
    /** @var object Content filters. */
    public $filters;
    /** @var string @var */
    public $label = '';
    /** @var string Query string to attach to page links. */
    protected $query_string = '';
    /** @var string Query string containing variables defining page state. */
    public $qs = '';
	/** @var string URL to use for redirects. */
	public $redirect_url = '';
	/** @var string Path to template file. */
	public $template_path = '';

	const CANCEL_ACTION = "cancel";
	const COMMIT_ACTION = "commit";

    /**
     * class constructor
     * @return PageContent
     */
    function __construct()
    {
        parent::__construct();
        return $this;
    }

	/**
	 * Sets the id property value of the object's content from request variable values, e.g. GET, POST, etc.
	 * First checks if a variable named "id" is present. 2nd, checks for a variable corresponding to the content
	 * object's id's internal parameter name.
     * @todo Consider moving this method to dedicated cms page content class
	 * @return ?int Record id value that was found, or null if no valid integer value was found for the content id.
	 */
	public function collectContentId(): ?int
	{
		$this->content->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY);
		if ($this->content->id->value===null) {
			if ( $this->content->id instanceof RequestInput) {
				$this->content->id->collectValue();
			}
		}
		return ($this->content->id->value);
	}

    /**
     * @todo Consider moving this method to dedicated cms page content class
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
     * @todo Consider moving this method to dedicated cms page content class
     */
    public function formatPageStateQueryString()
    {
        $this->qs = $this->filters->format_query_string();
    }

    /**
     * Returns array containing the variables and their values to be injected into
     * the template when rendering page content.
     * @return array
     */
    public function getTemplateContext(): array
    {
        return array();
    }

    /**
     * Sets $qs property value to preserve initial GET variable values.
     * @param RequestInput[] $page_vars Array of input_class objects used to collect page variable values
     * to store in query string.
     * @throws NotImplementedException
     */
    protected function preservePageVariables(array $page_vars)
    {
        $qs_vars = array();
        foreach($page_vars as $input) {
            $input->collectRequestData();
            if ($input->value===true) {
                $qs_vars[] = "$input->key=1";
            }
            elseif(strlen($input->value) > 0) {
                $qs_vars[] = "$input->key=".urlencode($input->value);
            }
        }
        if (count($qs_vars) > 0) {
            $this->qs = '?'.implode('&', $qs_vars);
        }
    }

    /**
     * Render the page content using template file.
     * @param array|null $context
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    public function render(?array $context=null)
    {
        if ($this->template_path==='') {
            throw new ConfigurationUndefinedException("Page template not configured.");
        }
        ContentUtils::renderTemplate($this->template_path, $context);
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
        if ($template_path) {
            $this->setTemplatePath($template_path);
        }
        $context = $context ?: $this->getTemplateContext();
        $this->render($context);
    }

    /**
     * Sets the error message to display on a page.
     * @param string $error_msg string
     */
    public function setPageError(string $error_msg ) {
        $this->content->validationErrors[] = $error_msg;
    }

    /**
     * Sets page properties.
     */
    public function setPageState() { }

    /**
     * Template path setter.
     * @param $path
     * @return void
     */
    public function setTemplatePath($path)
    {
        $this->template_path = $path;
    }
}
