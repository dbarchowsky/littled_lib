<?php

namespace Littled\PageContent;


interface RouteInterface
{
    const string CANCEL_ACTION = 'cancel';
    const string COMMIT_ACTION = 'commit';

    /**
     * Collect client request data.
     * @param array|null $src
     * @return mixed
     */
    public function collectRequestData(?array $src = null): mixed;

    /**
     * Template context getter
     * @return array
     */
    public function getTemplateContext(): array;

    /**
     * Content label getter
     * @return string
     */
    public function getContentLabel(): string;

    /**
     * Apply any necessary work to the request data. Use the request data to prepare a response.
     * @return RouteBase
     */
    public function processRequest(): RouteBase;

    /**
     * Injects content into a template to generate markup to send as http response matching a client request.
     * @param string $template_path Optional template path that will override the instance's internal template values.
     * @param ?array $context Optional data to inject in the template to use in place of the instance's internal property values.
     */
    public function sendResponse(string $template_path = '', ?array $context = null);
}