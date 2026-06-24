<?php

namespace Littled\PageContent\Templates;

use Littled\Exception\LittledException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Exception\TemplateOutputException;


readonly class TemplateRenderer
{
    private function __construct(
        private string          $template_path = '',
        private array           $context = [],
        private ErrorTemplate   $errorTemplate = new ErrorTemplate()
    ) {}

    public static function create(): self {
        return new self();
    }

    /**
     * Renders the template with the provided context.
     * @return void
     * @throws ResourceNotFoundException
     * @throws TemplateOutputException
     */
    public function render(): void {
        if (!file_exists($this->template_path)) {
            if (empty($this->template_path)) {
                throw new ResourceNotFoundException('Template file not provided.');
            } else {
                throw new ResourceNotFoundException("Template file \"$this->template_path\" not found.")
                    ->setFrontendError('Template file not found.');
            }
        }
        // shadow copy due to "readonly" nature of the class and property
        $localContext = $this->context;
        extract($localContext);
        include($this->template_path);
    }

    /**
     * Renders the template and returns the rendered markup as a string.
     * @return string
     * @throws ResourceNotFoundException
     * @throws TemplateOutputException
     */
    public function renderAsMarkup(): string
    {
        ob_start();
        try {
            $this->render();
            return ob_get_clean();
        }
        catch (ResourceNotFoundException $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Catch errors thrown by the render method and render an error message in place of the template content.
     * @return void
     */
    public function renderWithErrors(): void
    {
        try {
            $this->render();
        }
        catch (LittledException $e) {
            $this->errorTemplate->renderError($e->getMessage());
        }
    }

    /**
     * Creates a new instance of TemplateRenderer with the provided context.
     * @param array $context
     * @return self
     */
    public function withContext(array $context): self {
        return new self($this->template_path, $context, $this->errorTemplate);
    }

    /**
     * Creates a new instance of TemplateRenderer with the provided error CSS class.
     * @param ErrorTemplate $template
     * @return self
     */
    public function withErrorTemplate(ErrorTemplate $template): self {
        return new self($this->template_path, $this->context, $template);
    }

    /**
     * Creates a new instance of TemplateRenderer with the provided template path.
     * @param string $path
     * @return self
     */
    public function withTemplate(string $path): self {
        return new self($path, $this->context, $this->errorTemplate);
    }
}