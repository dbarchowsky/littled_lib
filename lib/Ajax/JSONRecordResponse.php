<?php
namespace Littled\Ajax;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;

/**
 * Class JSONResponse
 * @package Littled\PageContent\Ajax
 */
class JSONRecordResponse extends JSONResponse
{
	/** @var JSONField Record id. */
	public JSONField $id;
	/** @var JSONField Name of the element in the DOM to update. */
	public JSONField $containerID;
	/** @var JSONField Page content to be inserted into the DOM. */
	public JSONField $content;
	/** @var JSONField Element label value. */
	public JSONField $label;

	/**
	 * Class constructor.
     * @param string $key
	 */
	function __construct (string $key='')
	{
        parent::__construct($key);
		$this->id = new JSONField('id');
		$this->content = new JSONField('content');
		$this->label = new JSONField('label');
		$this->containerID = new JSONField('container_id');
	}

    /**
     * Inserts data into a template file and stores the resulting content in the object's $content property.
     * @param string $template_path Path to content template file.
     * @param ?array $context Array containing data to insert into the template.
     * @throws ResourceNotFoundException
     */
    public function loadContentFromTemplate( string $template_path, ?array $context=null)
    {
        if (is_array($context)) {
            foreach($context as $key => $val) {
                ${$key} = $val;
            }
        }
        $this->content->value = ContentUtils::loadTemplateContent($template_path, $context);
    }
}