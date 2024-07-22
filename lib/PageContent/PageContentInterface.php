<?php

namespace Littled\PageContent;


interface PageContentInterface
{
    const CANCEL_ACTION = 'cancel';
    const COMMIT_ACTION = 'commit';

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
     * Apply any needed work to the request data. Use the request data to prepare a response.
     * @return PageContentBase
     */
    public function processRequest(): PageContentBase;

    /**
     * Injects content into template to generate markup to send as http response matching a client request.
     * @param string $template_path Optional template path that will override the instance's internal template values.
     * @param ?array $context Optional data to inject in the template to use in place of instance's internal property values.
     */
    public function sendResponse(string $template_path = '', ?array $context = null);
}