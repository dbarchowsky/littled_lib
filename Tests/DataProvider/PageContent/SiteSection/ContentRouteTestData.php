<?php
namespace Littled\Tests\DataProvider\PageContent\SiteSection;

use Littled\PageContent\SiteSection\ContentRoute;


class ContentRouteTestData
{
    public ContentRoute     $obj;
    public ?int             $record_id;
    public ?int             $site_section_id;
    public ?string          $operation          ='';
    public ?string          $route              ='';
    public ?string          $url                ='';
    public bool             $bool_expectation;
    public ?string          $msg                ='';

    /**
     * @param string|null $msg
     */
    function __construct(?string $msg='')
    {
        $this->obj = new ContentRoute();
        $this->msg = $msg;
        return $this;
    }

    public function mapHasDataTestData(): array
    {
        return array(
            $this->bool_expectation,
            $this->obj,
            $this->msg
        );
    }

    public function mapFetchRecordTestData(): array
    {
        return array(
            $this->obj,
            $this->record_id,
            $this->site_section_id,
            $this->operation,
            $this->route,
            $this->url,
            $this->msg
        );
    }

    /**
     * @param int|null $id
     * @param int|null $site_section_id
     * @param string $operation
     * @param string $route
     * @param string $url
     * @param string $msg
     * @return ContentRouteTestData
     */
    public static function newInstance(
        ?int $id=null,
        ?int $site_section_id=null,
        string $operation='',
        string $route='',
        string $url='',
        string $msg=''
    ): ContentRouteTestData
    {
        $o = new ContentRouteTestData($msg);
        $o->setObjectProperties($id, $site_section_id, $operation, $route, $url);
        return $o;
    }

    /**
     * @param bool $expectation
     * @return $this
     */
    public function setBoolExpectation(bool $expectation): ContentRouteTestData
    {
        $this->bool_expectation = $expectation;
        return $this;
    }

    /**
     * @param int $record_id
     * @return $this
     */
    public function setInputRecordId(int $record_id): ContentRouteTestData
    {
        $this->record_id = $record_id;
        return $this;
    }

    /**
     * @param int $record_id
     * @return $this
     */
    public function setRecordId(int $record_id): ContentRouteTestData
    {
        $this->obj->id->setInputValue($record_id);
        return $this;
    }

    /**
     * @param int|null $id
     * @param int|null $site_section_id
     * @param string|null $operation
     * @param string|null $route
     * @param string|null $url
     * @return $this
     */
    public function setExpectations(
        ?int     $id,
        ?int    $site_section_id=null,
        ?string  $operation='',
        ?string $route='',
        ?string $url=''
    ): ContentRouteTestData
    {
        $this->record_id = $id;
        $this->site_section_id = $site_section_id;
        $this->operation = $operation;
        $this->route = $route;
        $this->url = $url;
        return $this;
    }

    /**
     * @param ?int $id
     * @param int|null $site_section_id
     * @param string $operation
     * @param string|null $route
     * @param string|null $url
     * @return $this
     */
    public function setObjectProperties(
        ?int    $id,
        ?int    $site_section_id=null,
        string  $operation='',
        ?string $route='',
        ?string $url=''
    ): ContentRouteTestData
    {
        $this->obj->id->setInputValue($id);
        $this->obj->site_section_id->setInputValue($site_section_id);
        $this->obj->operation->setInputValue($operation);
        $this->obj->route->setInputValue($route);
        $this->obj->api_route->setInputValue($url);
        return $this;
    }
}