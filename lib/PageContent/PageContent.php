<?php

namespace Littled\PageContent;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\ContentFilters;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;

/**
 * Handles requests for page content by retrieving data and using it to render content using content templates.
 */
abstract class PageContent extends PageContentBase
{
    /** @var string         Token representing the current action to take on the page. */
    public string $edit_action = '';
    /** @var SectionContent Page content. */
    public SectionContent $content;
    /** @var ContentFilters Content filters. */
    public ContentFilters $filters;
    /**
     * @var string          Label used to identify the content type in content templates.
     * @todo Audit this property. Consider using $content->content_properties->name or $filters->content_properties->name in its place.
     */
    public string $label = '';
    /** @var string         URL to use for redirects. */
    public string $redirect_url = '';

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
        if ($this->content->id->value === null) {
            if ($this->content->id instanceof RequestInput) {
                $this->content->id->collectRequestData();
            }
        }
        return ($this->content->id->value);
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
     * Content label getter.
     * @return string
     */
    public function getLabel(): string
    {
        if (isset($this->content)) {
            return $this->content->getContentLabel();
        }
        return '';
    }

    /**
     * Returns array containing the variables and their values to be injected into
     * the template when rendering page content.
     * @return array
     */
    abstract public function getTemplateContext(): array;

    /**
     * Sets $qs property value to preserve initial GET variable values.
     * @param RequestInput[] $page_vars Array of input_class objects used to collect page variable values
     * to store in query string.
     */
    protected function preservePageVariables(array $page_vars): void
    {
        $qs_vars = array();
        foreach ($page_vars as $input) {
            $input->collectRequestData();
            if ($input->value === true) {
                $qs_vars[] = "$input->key=1";
            } elseif (strlen($input->value) > 0) {
                $qs_vars[] = "$input->key=" . urlencode($input->value);
            }
        }
        if (count($qs_vars) > 0) {
            $this->query_string = '?' . implode('&', $qs_vars);
        }
    }

    /**
     * Render the page content using template file.
     * @param array|null $context
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    public function render(?array $context = null): void
    {
        if (static::getTemplatePath() === '') {
            throw new ConfigurationUndefinedException('Page template not configured.');
        }
        ContentUtils::renderTemplate(static::getTemplatePath(), $context);
    }

    /**
     * Prevents any variable values that were previously cached from being passed along to subsequent pages.
     */
    public function resetPageVariables(): void
    {
        $this->query_string = '';
    }

    /**
     * Injects content into template to generate markup to send as http response matching a client request.
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    public function sendResponse(string $template_path = '', ?array $context = null): void
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
    public function setPageError(string $error_msg): void
    {
        $this->content->addValidationError($error_msg);
    }

    /**
     * Sets page properties.
     */
    abstract public function setPageState();
}
