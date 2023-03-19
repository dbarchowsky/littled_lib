<?php
namespace Littled\VendorSupport;

use Littled\Exception\InvalidValueException;
use Littled\Utility\LittledUtility;

class TinyMCEUploader
{
    /** @var string[]       List of allowed file types by extension */
    protected static array  $allowed_extensions = array('gif', 'jpg', 'jpeg', 'png', 'webp');
    /** @var string[]       List of allowed origins for image uploads. */
    protected array         $allowed_origins = [];
    /** @var string         Path to images relative to server root. Used to format the href attribute value of img tags */
    protected string        $image_base_path;
    /** @var bool           If TRUE, image uploads will be organized by year and month under the upload directory  */
    protected bool          $organize_by_date = false;
    /** @var string         Full path on the filesystem to the location where uploads are stored. */
    protected string        $upload_path;

    /**
     * Tests for cross-origin requests.
     * @return bool FALSE if invalid request is detected.
     */
    protected function checkCrossOrigin(): bool
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // same-origin requests won't set an origin. If the origin is set, it must be valid.
            if (in_array($_SERVER['HTTP_ORIGIN'], $this->allowed_origins)) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            } else {
                header("HTTP/1.1 403 Origin Denied");
                return false;
            }
        }
        return true;
    }

    /**
     * Validates request method to filter out any 'OPTIONS' requests.
     * @return bool FALSE if invalid request is detected.
     */
    protected static function filterOptionsRequests(): bool
    {
        // Don't attempt to process the upload on an OPTIONS request
        if (!array_key_exists('REQUEST_METHOD', $_SERVER) || $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("Access-Control-Allow-Methods: POST, OPTIONS");
            return false;
        }
        return true;
    }

    /**
     * Returns the image base path with year and month directories added onto it if images are organized by date.
     * @param string $upload_path
     * @return string
     * @throws InvalidValueException
     */
    public function formatTargetPath(string $upload_path): string
    {
        // get location of image base path in full filesystem path
        $lp = strpos($upload_path, rtrim($this->image_base_path, '/'));
        if ($lp===false) {
            throw new InvalidValueException('Image base path not found in upload path.');
        }
        // anything in the upload path to the right of the image base path,
        // e.g. directories organizing assets by date and filename
        $right = substr($upload_path, $lp+strlen($this->image_base_path));
        // return image base + extras in upload path + filename
        return LittledUtility::joinPaths('/', $this->image_base_path, $right);
    }

    /**
     * Returns path to directory where the image upload will be stored. Directories with the current year and month
     * will be appended to the path if the object's $organize_by_date flag is set to TRUE.
     * @return string Path to location where images will be stored.
     */
    protected function formatUploadPath(): string
    {
        $path = rtrim($this->upload_path, '/ ').'/';
        if (!$this->organize_by_date) {
            return $path;
        }
        $year = date('Y');
        $month = date('m');
        if (!file_exists($path.$year)) {
            mkdir($path.$year);
            $path = $path.$year.'/';
        }
        if (!file_exists($path.$month)) {
            mkdir($path.$month);
            $path = $path.$month.'/';
        }
        return $path;
    }

    /**
     * Move the temporary upload file to its permanent location on the server. Return the path to the final location.
     * @param array $temp
     * @return string
     * @throws InvalidValueException
     */
    protected function moveUpload(array $temp): string
    {
        // Accept upload if there was no origin, or if it is an accepted origin
        $dest_path = $this->formatUploadPath() . $temp['name'];
        move_uploaded_file($temp['tmp_name'], $dest_path);
        return $this->formatTargetPath($dest_path);
    }

    /**
     * Perform checks on the upload. Move the upload to its final location. Return JSON expected by the editor to
     * confirm that the image was successfully uploaded.
     * @return false|array JSON string to send to editor.
     * @throws InvalidValueException
     */
    public function processImageUpload()
    {
        if (!$this->validateRequest()) {
            return false;
        }

        reset ($_FILES);
        $temp = current($_FILES);
        if (is_uploaded_file($temp['tmp_name'])){
            if(!static::validateUploadName($temp['name'])) {
                return false;
            }

            $location = $this->moveUpload($temp);

            // Respond to the successful upload with JSON.
            // Use a location key to specify the path to the saved image resource.
            // { location : '/your/uploaded/image/file'}
            return array('location' => $location);
        }
        else {
            // Notify editor that the upload failed
            header("HTTP/1.1 500 Server Error");
        }
        return false;
    }

    /**
     * Image base path setter.
     * @param string $path
     * @return void
     */
    public function setImageBasePath(string $path)
    {
        $this->image_base_path = $path;
    }

    /**
     * Organize by date flag setter.
     * @param bool $organize_by_date
     * @return void
     */
    public function setOrganizeByDate(bool $organize_by_date)
    {
        $this->organize_by_date = $organize_by_date;
    }

    /**
     * Upload path setter.
     * @param string $path
     * @return void
     */
    public function setUploadPath(string $path)
    {
        $this->upload_path = rtrim($path, '/ ').'/';
    }

    /**
     * Performs sanitation and validation to the names of files being uploaded to the server.
     * @param string $temp_name Name of the temporary upload.
     * @return bool FALSE if validation fails
     */
    protected static function validateUploadName(string $temp_name ): bool
    {
        // Sanitize input
        $pattern = "/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/";
        if (preg_match($pattern, $temp_name)) {
            header("HTTP/1.1 400 Invalid file name.");
            return false;
        }

        // Verify extension
        if (!in_array(strtolower(pathinfo($temp_name, PATHINFO_EXTENSION)), static::$allowed_extensions)) {
            header("HTTP/1.1 400 Invalid extension.");
            return false;
        }
        return true;
    }

    /**
     * Performs security validation on upload requests. Sends headers as needed.
     * @return bool FALSE if validation fails.
     */
    protected function validateRequest(): bool
    {
        if (!$this->checkCrossOrigin()) {
            return false;
        }
        if (!$this::filterOptionsRequests()) {
            return false;
        }
        return true;
    }
}