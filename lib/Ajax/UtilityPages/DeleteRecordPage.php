<?php
namespace Littled\Ajax\UtilityPages;

use Exception;
use Littled\Ajax\AjaxPage;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Filters\FilterCollection;
use Littled\Validation\Validation;

class DeleteRecordPage extends AjaxPage
{
    /** @var array Array of record ids to delete. */
    public $record_ids;

    /**
     * Class constructor
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     * @throws Exception;
     */
    public function __construct()
    {
        parent::__construct();

        /* CSRF prevention */
        if (!Validation::validateCSRF()) {
            throw new Exception("Bad request.");
        }

        /* retrieve content properties */
        $this->setContentProperties($this->content_properties->id->key);
        if (!$this->content_properties->id_param) {
            throw new Exception("Content properties not set. ");
        }

        /* retrieve ids of records to be deleted */
        $this->record_ids = Validation::collectIntegerRequestVar($this->content_properties->id_param);
        if (count($this->record_ids)==1) {
            $this->record_id->value = $this->record_ids[0];
        }

        /* retrieve listings filters to refresh page content after the record(s) are deleted */
        $this->filters = call_user_func_array([static::getCacheClass(), '::setFilters'], array($this->content_properties->id->value));
        if ($this->filters instanceof FilterCollection) {
            $this->filters->collectFilterValues();
        }

        /* action to perform */
        if (strlen(trim(filter_input(INPUT_POST, LittledGlobals::P_COMMIT, FILTER_SANITIZE_STRING))) > 0) {
            $this->action = self::COMMIT_ACTION;
        }

        // if ($this::COMMIT_ACTION === $this->action) {
        //    throw new Exception("page: {$this->filters->page->value}, page size: {$this->filters->page_len->value}");
        // }
    }

    /**
     * Retrieves content objects representing the content to be deleted. Uses the objects' internal methods to delete the content.
     * @throws Exception
     */
    public function deleteRecords()
    {
        try {
            $strStatus = "";
            if (is_array($this->record_ids)) {
                $content = call_user_func_array([static::getControllerClass(), '::getContentObject'], array($this->content_properties->id->value));
                if (is_object($content)) {
                    foreach($this->record_ids as $id) {
                        $this->record_id->value = $content->id->value = $id;
                        if (method_exists('cache_class', 'load_content')) {
                            call_user_func_array([static::getCacheClass(), '::loadContent'], array($content, $this->filters, $this));
                        }
                        else {
                            $content->read();
                        }
                        $strStatus .= $content->delete();
                    }
                    /*
                     * Update of the cache should happen within delete() method of the object being operated on
                     */
                    call_user_func_array([static::getCacheClass(), '::refreshContentAfterEdit'], array($content, $this->filters, $this->json));
                    unset($content);
                }
                else {
                    throw new Exception("Unknown content type.");
                }
            }
            else {
                throw new Exception("No records specified.");
            }
            $this->json->status->value = $strStatus;
        }
        catch(Exception $ex) {
            if (isset($content)) { unset($content); }
            $strError = $ex->getMessage();
            if (strpos($strError, "Record not found.")!==false) {
                throw new Exception("Record not found.");
            }
            else {
                throw new Exception("[".__METHOD__."] $strError");
            }
        }
    }
}