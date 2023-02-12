<?php
namespace Littled\PageContent;

interface PageContentInterface
{
    const CANCEL_ACTION = "cancel";
    const COMMIT_ACTION = "commit";

    /**
     * Render the page content using template file.
     * @param array|null $context
     * @return void
     */
    public function render(?array $context=null);

    /**
     * Inserts data into a template file and renders the result. Alias for class's render() method.
     * @param string $template_path Path to template to render.
     * @param ?array $context Data to insert into the template.
     */
    public function sendResponse( string $template_path='', ?array $context=null);
}