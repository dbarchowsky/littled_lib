<?php

namespace Littled\API;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;

/**
 * Class JSONResponse
 * @package Littled\PageContent\API
 */
class JSONRecordResponse extends JSONResponse
{
    /** @var JSONField Record id. */
    public JSONField $id;
    /** @var JSONField Name of the element in the DOM to update. */
    public JSONField $container_id;
    /** @var JSONField Page content to be inserted into the DOM. */
    public JSONField $content;
    /** @var JSONField Element label value. */
    public JSONField $label;

    /**
     * Class constructor.
     * @param string $key
     */
    function __construct(string $key = '')
    {
        parent::__construct($key);
        $this->id = new JSONField('id');
        $this->content = new JSONField('content');
        $this->label = new JSONField('label');
        $this->container_id = new JSONField('container_id');
    }

    /**
     * Inserts data into a template file and stores the resulting content in the object's $content property.
     * @param string $template_path Path to content template file.
     * @param ?array $context Array containing data to insert into the template.
     * @throws ResourceNotFoundException
     */
    public function loadContentFromTemplate(string $template_path, ?array $context = null)
    {
        if (is_array($context)) {
            foreach ($context as $key => $val) {
                ${$key} = $val;
            }
        }
        $this->content->value = ContentUtils::loadTemplateContent($template_path, $context);
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