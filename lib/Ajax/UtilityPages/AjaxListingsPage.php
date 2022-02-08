<?php
namespace Littled\Ajax\UtilityPages;

use Littled\Ajax\AjaxPage;
use Exception;
use Littled\Exception\NotImplementedException;

class AjaxListingsPage extends AjaxPage
{
    /**
     * Class constructor
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

         // content properties from page parameters
        $this->setContentProperties();
        if (!$this->getContentTypeId()) {
            throw new Exception("Content properties not set. ");
        }

        // retrieve content containers
        $content = call_user_func_array([static::getControllerClass(), '::getContentObject'], array($this->getContentTypeId()));
        $this->filters= call_user_func_array([static::getControllerClass(), '::getContentFiltersObject'], array($this->getContentTypeId()));
        $this->collectFiltersRequestData();

        // inject content into template to generate markup
        $listings_template = $this->content_properties->getContentTemplateByName('cms listings');
        if ($listings_template) {
            $this->loadContent($listings_template->formatFullPath(), $content, $this->filters);
        }
        else {
            throw new Exception("Content template not found.");
        }
    }

    /**
     * @return void
     * @throws NotImplementedException
     */
    public function collectFiltersRequestData()
    {
        $this->filters->collectFilterValues();
        $this->filters->display_listings->value = true;
    }
}