<?php
namespace Littled\PageContent;

use Littled\App\LittledGlobals;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\ContentFilters;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

/**
 * Intended as a base utility class for managing and rendering content for different types of pages.
 */
class PageContent extends MySQLConnection
{
    /** @var string Token representing the current action to take on the page. */
    public string $edit_action='';
    /** @var SectionContent Page content. */
    public SectionContent $content;
    /** @var ContentFilters Content filters. */
    public ContentFilters $filters;
    /** @var string @var */
    public string $label = '';
    /** @var string Query string to attach to page links. */
    protected string $query_string = '';
	/** @var string URL to use for redirects. */
	public string $redirect_url = '';
	/** @var string Path to template file. */
	public string $template_path = '';

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
				$this->content->id->collectRequestData();
			}
		}
		return ($this->content->id->value);
	}

    /**
     * @todo Consider moving this method to dedicated cms page content class
     * @param array|null $src Optional array of variables to use in place of POST data.
     * Sets the value of the object's $action property based on action variables in POST data.
     */
    public function collectEditAction( ?array $src=null )
    {
		$keys = array(
			LittledGlobals::CANCEL_KEY => self::CANCEL_ACTION,
	        LittledGlobals::COMMIT_KEY => self::COMMIT_ACTION);
        if (null===$src) {
            $src = $_POST;
        }
		foreach ($keys as $key => $value) {
			if (array_key_exists($key, $src)) {
				$action = filter_var($src[$key], FILTER_UNSAFE_RAW);
				if ($action && $action !== '0') {
					// internally, make the action value consistent regardless of how it's set in request data
					$this->edit_action = $value;
					return;
				}
			}
		}
    }

	/**
	 * Formats and stores query string from current filter property values.
     * @param string[]|null $exclude
	 * @return string
	 */
	public function formatQueryString(?array $exclude=null): string
	{
		$this->query_string = $this->filters->formatQueryString($exclude);
		return $this->query_string;
	}

    /**
     * Uses current filter values to generate a query string that
     * will preserver the current page state. The query string value is
     * stored as the value of the object's $qs property.
     * @todo Consider moving this method to dedicated cms page content class
     * @returns string
     */
    public function formatPageStateQueryString(): string
    {
        $this->query_string = $this->filters->formatQueryString();
		return $this->query_string;
    }

    /**
     * Content label getter.
     * @return string
     */
    public function getContentLabel(): string
    {
        if (isset($this->content)) {
            return $this->content->getContentLabel();
        }
        return '';
    }

    /**
     * Returns URI of the page containing record details with filter
     * @throws NotImplementedException
     */
    public function getDetailsURI(?int $record_id=null): string
    {
        throw new NotImplementedException(Log::getShortMethodName().' not implemented.');
    }

    /**
     * Content label getter.
     * @return string
     */
    public function getLabel(): string
    {
        if (isset($this->content)) {
            return $this->content->getLabel();
        }
        return '';
    }

    /**
     * Query string getter
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->query_string;
    }

    /**
     * Record id getter.
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        if (isset($this->content) && $this->content->id->value) {
            return $this->content->id->value;
        }
        return null;
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
            $this->query_string = '?'.implode('&', $qs_vars);
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
		$this->query_string = '';
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
