<?php

namespace Littled\API;

use Littled\Exception\ResourceNotFoundException;
use Littled\Exception\TemplateOutputException;
use Littled\PageContent\Templates\TemplateRenderer;

class JSONRecordResponse extends JSONResponse
{
    public JSONField $id;
    public JSONField $container_id;
    public JSONField $content;
    public JSONField $label;
    public JSONField $record_label;
    public JSONField $content_label;


    /**
     * Class constructor.
     * @param string $key
     */
    function __construct(string $key = '')
    {
        parent::__construct($key);
        $this->id = new JSONField()->setName('id')->setSendWhenEmpty(false);
        $this->content = new JSONField('content')->setName('content')->setSendWhenEmpty(false);
        $this->label = new JSONField()->setName('label')->setSendWhenEmpty(false);
        $this->record_label = new JSONField()->setName('record_label')->setSendWhenEmpty(false);
        $this->content_label = new JSONField()->setName('content_label')->setSendWhenEmpty(false);
        $this->container_id = new JSONField()->setName('container_id')->setSendWhenEmpty(false);
        $this->status->setSendWhenEmpty(false);
        $this->error->setSendWhenEmpty(false);
    }

    /**
     * Inserts data into a template file and stores the resulting content in the object's $content property.
     * @param string $template_path Path to the content template file.
     * @param ?array $context Array containing data to insert into the template.
     * @throws ResourceNotFoundException
     * @throws TemplateOutputException
     */
    public function loadContentFromTemplate(string $template_path, ?array $context = null): void
    {
        if (is_array($context)) {
            foreach ($context as $key => $val) {
                ${$key} = $val;
            }
        }
        $this->content->value = TemplateRenderer::create()
            ->withTemplate($template_path)
            ->withContext($context ?? [])
            ->renderAsMarkup();
    }

    /**
     * Chainable response container id value setter.
     * @param string $container_id
     * @return $this
     */
    public function setResponseContainerId(string $container_id): JSONRecordResponse
    {
        $this->container_id->value = $container_id;
        return $this;
    }

    /**
     * Chainable response content value setter.
     * @param string $content
     * @return $this
     */
    public function setResponseContent(string $content): JSONRecordResponse
    {
        $this->content->value = $content;
        return $this;
    }

    /**
     * Assign response properties after successfully processing an api request.
     * @param string $content
     * @param string $status
     * @param string $container_id
     * @return $this
     */
    public function setResponseData(string $content, string $status, string $container_id): JSONRecordResponse
    {
        return $this->setResponseContent($content)
            ->setResponseContainerId($container_id)
            ->setResponseStatus($status);
    }

    public function setResponseId(int $id): JSONRecordResponse
    {
        $this->id->value = $id;
        return $this;
    }

    public function setResponseLabel(string $label): JSONRecordResponse
    {
        $this->label->value = $label;
        return $this;
    }

    /**
     * Chainable response status value setter.
     * @param string $status
     * @return $this
     */
    public function setResponseStatus(string $status): JSONRecordResponse
    {
        $this->status->value = $status;
        return $this;
    }
}