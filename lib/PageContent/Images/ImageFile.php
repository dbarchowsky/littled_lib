<?php
namespace Littled\PageContent\Images;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\PageUtils;
use Littled\Utility\LittledUtility;

/**
 * Class ImageFile
 * @package Littled\PageContent\Images
 */
class ImageFile extends ImageBase
{
	/** @var string Target filename of image file that is saved to disk. */
	protected string $target_name = '';

	/**
	 * Renames image file on disk.
	 * @param string $target_basename New filename for the image file.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 */
	public function changeFilename( string $target_basename ): void
	{
		$this->connectToDatabase();
		$src = basename($this->path->value);
		$src = substr($src, 0, strrpos($src, '.'));
		if($target_basename==$src) {
			/* no change; not necessary to take any action */
			return;
		}

		$image_root = $this->getSiteRoot($this->path->value);
		$new_path = preg_replace('/(.*\/).*(\..*$)/', "\\1".$target_basename."\\2", $this->path->value);

		$this->formatUniquePath($image_root, $new_path);
		@rename($image_root.$this->path->value, $image_root.$new_path);
		$this->path->value = $new_path;

		$query = 'UPDATE `images` SET `path` = ' .$this->path->escapeSQL($this->mysqli)." WHERE `id` = {$this->id->value}";
		$this->query($query);
	}

    /**
     * Using an image id, look up the path to the image and delete the image file off the server.
     * @param ?int $img_id id of the image. (image.id) if set to null it will use the value of the id properties of the image object
     * @param bool $bypass_on_match If true, don't delete the file if the path in the database matches the current value image class object's path property. Default is false.
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws InvalidQueryException
     * @throws ConnectionException
     */
    public function deleteExistingImageFile( ?int $img_id=null, bool $bypass_on_match=false ): void
    {
        if ($img_id===null) {
            $img_id = $this->id->value;
        }
        if ($img_id===null || $img_id < 1) {
            throw new ContentValidationException('Image id not provided.');
        }

        /* Retrieve image path from database. */
        $query = "SELECT `path` FROM `images` WHERE `id` = $img_id";
        $result = $this->fetchRecords($query);
        if (count($result) < 1) {
            throw new RecordNotFoundException('The requested image properties could not be retrieved.');
        }
        $db_path = $result[0]->path;

        /* got a value from the database */
        if ($db_path) {
            /* don't delete on matching filename if that option is specified */
            if ($bypass_on_match && basename($db_path) != basename($this->path->value)) {
                /* make sure file exists before attempting to delete */
                if (file_exists($this->getSiteRoot($db_path).$db_path)) {
                    @unlink($this->getSiteRoot($db_path).$db_path);
                }
            }
        }
    }

	/**
	 * Extract the keywords from image file linked to the Image object.
	 * @param Keyword[] $keywords Array to use to store keyword data.
	 * @param int $parent_id Record id of the content object to which the image is linked.
	 * @param int $keyword_content_type_id Content type identifier for the images.
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
	public function extractKeywords(array &$keywords, int $parent_id, int $keyword_content_type_id ): void
	{
		$terms = [];
		$path = $this->getSiteRoot($this->path->value).$this->path->value;
		$this->extractKeywordsFromFile($path, $terms);

		if (is_array($terms)) {
			$keywords = [];
			foreach($terms as $term) {
				$i = count($keywords);
				$keywords[$i] = new Keyword($term, $parent_id, $keyword_content_type_id);
			}
		}
	}

	/**
	 * Reads keywords stored in an image file's metadata.
	 * @param string $path Path to image file containing keywords.
	 * @param Keyword[] $keywords Keyword list to be filled from source.
	 * @throws ResourceNotFoundException Image file not found at $path.
     */
	protected function extractKeywordsFromFile( string $path, array &$keywords ): void
    {
		$keywords = [];
		if (!file_exists($path)) {
			throw new ResourceNotFoundException("The requested image file \"$path\" was not found.");
		}
		getimagesize($path, $info);
		if (isset($info['APP13'])) {
			/* extract keywords from source */
			$iptc = iptcparse($info['APP13']);
			if (is_array($iptc) && array_key_exists('2#025', $iptc)) {
				$keywords = $iptc['2#025'];
			}
		}
	}

    protected function formatCommitQuery(): array
    {
        return [
            'CALL imagesUpdate(?,?,?,?,?,?,?,?)',
            'isiissss',
            $this->id->value,
            $this->path->value,
            $this->width->value,
            $this->height->value,
            $this->alt->value,
            $this->url->value,
            $this->target->value,
            $this->caption->value];
    }

	/**
	 * Renames an upload with a new name if specified with $target_basename, or with a random filename if $bRandomize is true. Also replaces whitespace in the filename with dashes.
	 * @param string $target_name Path to image file.
	 * @param string $target_basename Optional new name for the image file.
	 * @param boolean $randomize If set to true, the new upload file will be given a randomized name.
	 * @return string The new filename.
	 */
	public function formatUploadFilename(
        string $target_name,
        string $target_basename = '',
        bool $randomize=false ): string
	{
		if($target_basename) {
			/* change filename to new filename */
			$target_name = $target_basename.substr($target_name, strrpos($target_name, '.'));
		}
		elseif ($randomize) {
			/* change filename to the current date followed by a random string */
			$target_name = PageUtils::generateRandomFilename(8, preg_replace('/.*\.(.*)$/i',"\$1",$target_name));
		}
		/* remove whitespace */
		return(str_replace(' ', '-', $target_name));
	}

	/**
	 * Adds index to filename until a unique file name is found for the image. Also updates the value of the $filename parameter.
	 * @param string $dir_name Name of destination directory for the image.
	 * @param string $filename Target filename of the image.
	 * @return string New, unique path for the image.
	 */
	protected function formatUniquePath(string $dir_name, string &$filename ): string
	{
		$n = 1;
		$base = substr($filename, 0, strrpos($filename, '.'));
		$ext = substr($filename, strrpos($filename, '.'));
		while(file_exists($dir_name.$filename))
		{
			$filename = $base. '_' .$n.$ext;
			$n++;
		}
		return ($dir_name.$filename);
	}

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        return 'image';
    }

    /**
	 * @param string $path Path to use to determine file extension. If omitted, object's internal $path property value will be used.
	 * @return string File extension.
     * @throws InvalidValueException
     */
	public function getFileExtension(string $path = ''): string
	{
		if ($path=='') {
			$path = $this->path->value;
		}
		$parts = preg_split('/\./', $path);
		if (count($parts) < 2) {
			throw new InvalidValueException("Extension could not be determined for \"$path\".");
		}
		return(end($parts));
	}

	/**
	 * Return the absolute path to the site's root directory. If $path is supplied, append that path to the root path. The path that is returned will always have a forward slash at the end of it.
	 * @param string $sub_dir Path of any subdirectory within the site that should be appended to the root path.
	 * @return string The full local path, with a forward slash at the end of it, including the subdirectory if supplied.
	 * @throws ConfigurationUndefinedException APP_BASE_DIR constant not defined.
	 */
	public function getSiteRoot( string $sub_dir = '' ): string
	{
		if (!defined('APP_BASE_DIR')) {
			throw new ConfigurationUndefinedException('APP_BASE_DIR not defined.');
		}
		$path = APP_BASE_DIR;
		if ($path == '' && property_exists($this, 'path')) {
			$path = $this->path->value;
		}
		if (strlen($path) > 0) {
			return (rtrim($path, '/').'/'.((strlen($sub_dir)>0)?(rtrim($sub_dir, '/').'/'):('')));
		}
		return ('');
	}

	/**
	 * Returns the path to the temporary image upload file.
	 * @return string Path to the temporary image upload file.
	 */
	public function getTempPath(): string
    {
		if ($this->path->index===null) {
			return($_FILES[$this->path->key]['tmp_name']);
		}
		return ($_FILES[$this->path->key]['tmp_name'][(int)$this->path->index]);
	}

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->path->hasData() || $this->width->hasData() || $this->height->hasData() || $this->url->hasData();
    }

    /**
     * @param $tmp_path
     * @param $target_name
     * @param $upload_dir
     * @throws OperationAbortedException
     */
	protected function moveUploadToDestination($tmp_path, $target_name, $upload_dir): void
    {
        /* no resampling: move file to its directory */
        $upload_path = $this->formatUniquePath($upload_dir, $target_name);
        if (!move_uploaded_file($tmp_path, $upload_path)) {
            throw new OperationAbortedException('Error moving uploaded file.');
        }
    }

	/**
	 * @param string $sub_dir
	 * @param string $target_basename
	 * @param bool $randomize
     * @return string
	 * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 */
	public function placeUploadFile( string $sub_dir='', string $target_basename='', bool $randomize=false ): string
	{
		list($tmp_path, $upload_dir) = $this->processUpload($sub_dir, $target_basename, $randomize);
		$this->moveUploadToDestination($tmp_path, $this->target_name, $upload_dir);
        return LittledUtility::joinPaths($upload_dir, $this->target_name);
	}

	/**
	 * @param string $sub_dir Path within the images directory where the image file should be saved.
	 * @param string $target_basename Target filename for the final image file.
	 * @param bool $randomize Randomize the destination filename.
	 * @return array Path to temporary upload file. Path to upload directory.
	 * @throws ConfigurationUndefinedException
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 */
	protected function processUpload(string $sub_dir, string $target_basename, bool $randomize): array
	{
		/* Get local path to the destination directory for the new image file. */
		$image_root = $this->getSiteRoot($this->image_dir);

		/* Make sure there is valid data to work with. */
		$this->target_name = $tmp_path = '';
		if (!$this->validateUpload($tmp_path, $this->target_name)) {
			return ['',''];
		}

		/* Do any renaming of the destination file name. */
		$this->target_name = $this->formatUploadFilename($this->target_name, $target_basename, $randomize);

		$upload_dir = $image_root.$this->image_dir.$sub_dir;
		if ((!file_exists($upload_dir)) || (!is_dir($upload_dir))) {
			throw new ResourceNotFoundException("Destination directory \"$this->image_dir$sub_dir\" does not exist.");
		}

		if ($sub_dir=='') {
			/* extract keywords from original file for main image but not thumbnails */
			$keyword_array = [];
			$this->extractKeywordsFromFile($tmp_path, $keyword_array);
		}
		return [$tmp_path, $upload_dir];
	}

	/**
	 * Upload, resize, and place an image file on the server submitted through a form.
	 * Save the image properties in the database.
	 * If this image has already been saved in the database, update its properties, and if a new image file is uploaded, delete the existing image file of the server.
	 * @param ImageDims|null $target_dims Target image width and height for the final image file.
	 * @param string $target_ext optional file extension of the new image file, converts the image to this type if it's different from the original file type
	 * @param string $sub_dir optional name of the subdirectory in which to place the new image file. this is in addition to the object's interal image_dir property
	 * @param string $target_basename optional new name for the image file
	 * @param bool $randomize optional flag if set to true the new image file will be given a randomized filename
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws RecordNotFoundException
	 * @throws ResourceNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 */
	function save (
        ?ImageDims $target_dims = null,
        string $target_ext = '',
        string $sub_dir = '',
        string $target_basename = '',
        bool $randomize = false ): void
    {
		if (!$this->hasData()) {
			return;
		}

		/* upload, resize, rename, & extract keywords from the image file */
		$this->target_name = $this->placeUploadFile($sub_dir, $target_basename, $randomize);

		if ($this->id->value>0 && $this->target_name) {
			/* delete the old image file if uploading a replacement */
			$this->deleteExistingImageFile($this->id->value, true);
		}

		parent::save();
		$this->target_name = '';
	}

    /**
     * Check file uploaded through form. Make sure a file was uploaded and that it is an accepted file type. Throws Exception if there is anything upacceptable about the upload.
     * @param string $tmp_path Passed by reference. Set to the temporary name of the uploaded file after return.
     * @param string $target_name Passed by reference. Set to the original name of the file will be stored in this variable after return.
     * @return boolean Returns false if there is no upload to work with. Throws Exception if there is something unacceptable about the upload.
     * @throws InvalidTypeException
     * @throws OperationAbortedException
     */
    function validateUpload( string &$tmp_path, string &$target_name ): bool
    {
        /* do nothing if a file has not been uploaded */
        if (!isset($_FILES[$this->path->key]) || strlen($_FILES[$this->path->key]['name'])<1) {
            return (false);
        }

        /* get the original and temporary file names of the image upload */
        $target_name = $tmp_path = '';
        if ($this->path->index===null) {
            $target_name = $_FILES[$this->path->key]['name'];
            $tmp_path = $_FILES[$this->path->key]['tmp_name'];
        } else {
            $target_name = $_FILES[$this->path->key]['name'][(int)$this->path->index];
            $tmp_path = $_FILES[$this->path->key]['tmp_name'][(int)$this->path->index];
        }

        /* do nothing if the original file name is unavailable */
        if (!$target_name) {
            return(false);
        }

        /* check for invalid file types */
        if (!$this->validateFileType($target_name)) {
            throw new InvalidTypeException('File type not allowed.');
        }

        /* make sure there is a valid upload to work with */
        if (!is_uploaded_file($tmp_path)) {
            throw new OperationAbortedException("Error uploading image file to \"$tmp_path\".");
        }

        return (true);
    }
}