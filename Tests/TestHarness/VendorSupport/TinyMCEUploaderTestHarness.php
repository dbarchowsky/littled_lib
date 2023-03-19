<?php
namespace Littled\Tests\TestHarness\VendorSupport;

use Littled\VendorSupport\TinyMCEUploader;


class TinyMCEUploaderTestHarness extends TinyMCEUploader
{
    protected array $allowed_origins = array('http://localhost', 'https://www.mysite.com');
    protected string $upload_path = '/var/www/html/images';

    /**
     * @inheritDoc
     * Public interface for tests
     */
    public function checkCrossOrigin(): bool
    {
        return parent::checkCrossOrigin();
    }

    /**
     * @inheritDoc
     * Public interface for tests
     */
    public static function filterOptionsRequests(): bool
    {
        return parent::filterOptionsRequests();
    }

    /**
     * @inheritDoc
     * Public interface for tests
     */
    public function formatUploadPath(): string
    {
        return parent::formatUploadPath();
    }

    /**
     * @inheritDoc
     * Public interface for tests
     */
    public static function validateUploadName(string $temp_name): bool
    {
        return parent::validateUploadName($temp_name);
    }

    /**
     * @inheritDoc
     * Public interface for tests
     */
    public function validateRequest(): bool
    {
        return parent::validateRequest();
    }
}