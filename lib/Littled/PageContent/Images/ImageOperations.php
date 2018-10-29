<?php
namespace Littled\PageContent\Images;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;

/**
 * Class ImageOperations
 * @package Littled\PageContent\Images
 */
class ImageOperations extends ImageFile
{
	/**
	 * Calculates practical target image dimensions based on the actual dimensions of a source image and requested final
	 * dimensions of the resized image. Returns the adjusted target dimensions.
	 * @param ImageDims $src_dims Original image dimensions.
	 * @param ImageDims $target_dims Target image dimensions.
	 * @return ImageDims Calculated target image dimensions.
	 */
	protected function calcTargetDimensions($src_dims, $target_dims)
	{
		if ($target_dims->width>0 && ($target_dims->height==0 || $target_dims->height===null))
		{
			$target_dims->height = (int)(($target_dims->width/$src_dims->width)*$src_dims->height);
		}
		elseif ($target_dims->height>0 && ($target_dims->width==0 || $target_dims->width===null))
		{
			$target_dims->width = (int)(($target_dims->height/$src_dims->height)*$src_dims->width);
		}
		return ($target_dims);
	}

	/**
	 * Saves raw image data as image file.
	 * @param resource $image_data Image data to save to file.
	 * @param string $target_ext File extension, which determines the file type of the image file.
	 * @param string $upload_path Path where the image file is to be saved.
	 * @throws InvalidTypeException Unsupported image type.
	 * @throws OperationAbortedException Error saving image file.
	 */
	protected function commitImageDataToDisk($image_data, $target_ext, $upload_path)
	{
		switch($target_ext)
		{
			case "png":
				if(!imagepng($image_data, $upload_path)) {
					throw new OperationAbortedException("Error saving resampled PNG image: {$upload_path}. ");
				}
				break;
			case "jpg":
			case "jpeg":
				if(!imagejpeg($image_data, $upload_path, 100)) {
					throw new OperationAbortedException("Error saving resampled JPEG image: {$upload_path}. ");
				}
				break;
			case "gif":
				if(!imagegif($image_data, $upload_path)) {
					throw new OperationAbortedException("Error saving resampled GIF image: {$upload_path}. ");
				}
				break;
			case "bmp":
				if(!imagewbmp($image_data, $upload_path)) {
					throw new OperationAbortedException("Error saving resampled BMP image: {$upload_path}. ");
				}
				break;
			default:
				throw new InvalidTypeException("Unsupported image file type for saving resampled image: {$target_ext}. ");
		}
	}

    /**
     * Crop and resize image data.
     * @param resource $image Raw image data to transform.
     * @param ImageDims $src_dims Width & height of the unaltered image.
     * @param ImageDims $dst_dims Target width & height.
     * @return resource Transformed image data.
     */
    protected function cropImage($image, $src_dims, $dst_dims)
    {
        $scale = min((float)($src_dims->width/$dst_dims->width),(float)($src_dims->height/$dst_dims->height));

        $crop = new ImageDims();
        $crop->x = (float)($src_dims->width-($scale*$dst_dims->width));
        $crop->y = (float)($src_dims->height-($scale*$dst_dims->height));

        $crop->width = (float)($src_dims->width - $crop->x);
        $crop->height = (float)($src_dims->height - $crop->y);

        $cropped_img = imagecreatetruecolor($crop->width, $crop->height);
        imagecopy($cropped_img, $image, 0, 0, (int)($crop->x/2), (int)($crop->y/2), $crop->width, $crop->height);

        $image_p = imagecreatetruecolor($dst_dims->width, $dst_dims->height);
        imagecopyresampled($image_p, $cropped_img, 0, 0, 0, 0, $dst_dims->width, $dst_dims->height, $crop->width, $crop->height);
        imagedestroy($cropped_img);
        return($image_p);
    }

    /**
     * Embeds list of keywords in an image file.
     * @param string[] &$terms List of keywords to embed.
     * image will be assumed to be a scaled down version of an original image.
     * @throws \Littled\Exception\ConfigurationUndefinedException
     */
	protected function embedKeywords( &$terms )
    {
        /* preserve keywords in new file, but only if it's the main image and not a thumbnail */
        $iptc_data = "";
        foreach ($terms as $term) {
            $iptc_data .= $this->makeIPTCTag(2, "025", $term);
        }
        $upload_path = $this->getSiteRoot($this->path->value).$this->path->value;
        $content = iptcembed($iptc_data, $upload_path);

        $f = fopen($upload_path, "wb");
        fwrite($f, $content);
        fclose($f);
    }

	/**
	 * Format the path to a new resized image file.
	 * @param string $root_path Root path of directory where images are stored.
	 * @param string $target_name Target filename of the new image.
	 * @param string $src_ext Extension of the source image file, indicating its file type.
	 * @param string[optional] $target_ext Target extension of the new image, indicating its file type.
	 * @param string[optional] $sub_dir Optional subdirectory path within the image root directory where the new image should be stored.
     * @return string Path for uploaded image.
	 */
	protected function formatUploadPath( $root_path, $target_name, $src_ext, $target_ext='', $sub_dir='' )
	{
		/* format destination image path */
		if ($target_ext=="") {
			$target_ext=$src_ext;
		}
		$target_name = substr($target_name, 0, strrpos($target_name,".")).".".$target_ext;
		return($root_path.$this->image_dir.$sub_dir.$target_name);
	}

	/**
	 * Create a separate image file containing a thumbnail version of an existing image.
	 * @param string $sub_dir Directory within the images directory where the thumbnail image is to be stored.
	 * @param int $target_length Target size in pixels of the longest edge of the thumbnail image.
	 * @param string $column_name Column in the database that links to the record of the thumbnail.
	 * @return int Id of the new thumbnail image record.
	 * @throws ConfigurationUndefinedException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function generateThumbnail($sub_dir, $target_length, $column_name)
	{
		/* resize the image to make a thumbnail */
		$path = $this->getSiteRoot($this->path->value).$this->path->value;
		$tn_path = preg_replace("/^(.*\/)/", "$1{$sub_dir}/", $path);

		/* get new dimensions */
		if ($target_length<1) {
			throw new ConfigurationUndefinedException("Image target dimension size not set.");
		}
		list($src_width, $src_height) = getimagesize($path);
		if ($src_width > $src_height) {
			$new_w = $target_length;
			$new_h = (int)(($new_w/$src_width)*$src_height);
		}
		else {
			$new_h = $target_length;
			$new_w = (int)(($new_h/$src_height)*$src_width);
		}

		/* resample */
		$image_p = imagecreatetruecolor($new_w, $new_h);
		$image = imagecreatefromjpeg($path);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_w, $new_h, $src_width, $src_height);
		imagejpeg($image_p, $tn_path, 100);

		/* save thumbnail image database record */
		$tn_id = null;
		$query = "SEL"."ECT `{$column_name}` FROM `image_link` WHERE image_id = {$this->id->value}";
		$data = $this->fetchRecords($query);
		if (count($data)> 0) {
			$tn_id = $data[0]->$column_name;
		}

		$tn_path = preg_replace("/^(.*\/)/", "$1{$sub_dir}/", $this->path->value);
		if ($tn_id>0) {
			$query = "UPDATE `images` SET ".
				"path = '{$tn_path}', ".
				"width = {$new_w}, ".
				"height = {$new_h}, ".
				"alt = ".$this->alt->escape_sql()." ".
				"WHERE id = {$tn_id}";
			$this->query($query);
		}
		else {
			$query = "INSERT INTO `images` (path,width,height,alt) VALUES (".
				"'{$tn_path}', {$new_w}, {$new_h}, ".$this->alt->escapeSQL($this->mysqli).")";
			$this->query($query);
			$tn_id = $this->retrieveInsertID();
		}
		return ($tn_id);
	}

    /**
     * Read raw image pixel data to manipulate.
     * @param string $path Path to image.
     * @param string $extension Extension of the image to indicate file type.
     * @return resource Image pixel data.
     * @throws InvalidTypeException Unsupported image file type.
     */
    protected function loadImageData($path, $extension)
    {
        switch(strtolower($extension)) {
            case "png":
                return(imagecreatefrompng($path));
                break;
            case "jpg":
            case "jpeg":
                return(imagecreatefromjpeg($path));
                break;
            case "gif":
                return(imagecreatefromgif($path));
                break;
            case "bmp":
                return(imagecreatefromwbmp($path));
                break;
            default:
                throw new InvalidTypeException("Unsupported image file type for resampling: {$extension}. ");
        }
    }

    /**
	 * Loads image properties for the image file specified with $src_path.
	 * @param string $src_path Path to source image file.
	 * @param string $target_name New filename of manipulated image.
	 * @return array Image properties: image data, file extension, and image dimensions.
	 * @throws InvalidTypeException Unsupported image file type.
	 * @throws ResourceNotFoundException Image file not found.
	 * @throws \Exception
	 */
	protected function loadImageProperties( $src_path, $target_name )
	{
		/* get file type of uploaded image */
		$ext = $this->getFileExtension($target_name);

		/* validate path */
		if (!file_exists($src_path)) {
			throw new ResourceNotFoundException("File not available for resampling: {$src_path}.");
		}

		/* load image pixel data and properties */
		$src_dims = new ImageDims();
		$image = $this->loadImageData($src_path, $ext);
		list($src_dims->width, $src_dims->height) = getimagesize($src_path);

		return(array($image, $ext, $src_dims));
	}

    /**
     * Converts keyword term into IPTC tag to embed in image file.
     * @param int $rec
     * @param string $data
     * @param string $value keyword term.
     * @return string IPTC tag.
     */
    protected function makeIPTCTag( $rec, $data, $value )
    {
        $length = strlen($value);
        $return = chr(0x1C) . chr($rec) . chr($data);

        if($length < 0x8000) {
            $return .= chr($length >> 8) .  chr($length & 0xFF);
        }
        else {
            $return .= chr(0x80) .
                chr(0x04) .
                chr(($length >> 24) & 0xFF) .
                chr(($length >> 16) & 0xFF) .
                chr(($length >> 8) & 0xFF) .
                chr($length & 0xFF);
        }

        return ($return.$value);
    }

	/**
	 * Copies a thumbnail version of the source image.
	 * @param string $target_name Target filename of the thumbnail image.
	 * @param ImageDims $target_dims Target dimensions of the thumbnail image.
	 * @param string $target_ext Target extension (file type) of the new image.
	 * @param string $sub_dir Path withing the image root directory where the new image will be saved.
	 * @param string $field_name Field within the database that stores the thumbnail id.
	 * @return int Id of the thumbnail record.
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function makeThumbnailCopy($target_name, $target_dims, $target_ext, $sub_dir, $field_name)
	{
		$this->connectToDatabase();
		$image_root = $this->getSiteRoot($this->image_dir);

		if (!file_exists($image_root.$this->path->value)) {
			throw new ResourceNotFoundException("Source file not found: {$image_root}{$this->path->value}.");
		}

		$this->resample($image_root.$this->path->value, $target_name, $target_dims, $target_ext, $sub_dir);

		$path = $this->formatUploadPath($target_name, $target_ext, $sub_dir);
		$src_dims = new ImageDims();
		list($src_dims->width, $src_dims->height) = getimagesize($image_root.$path);

		$thumbnail_id = 0;
		if ($this->id->value > 0) {
			$query = "SEL"."ECT `{$field_name}` FROM `image_link` WHERE `fullres_id` = ".$this->id->escapeSQL($this->mysqli);
			$data = $this->fetchRecords($query);
			if (count($data) > 0) {
				$thumbnail_id = $data[0]->$field_name;
			}
		}

		if ( $thumbnail_id > 0 ) {
			$query = "CALL imagesUpdateThumbnail(".
				$this->escapeSQLValue($thumbnail_id).",".
				$this->escapeSQLValue($path).",".
				$this->escapeSQLValue($src_dims->width).",".
				$this->escapeSQLValue($src_dims->height).",".
				$this->alt->escapeSQL($this->mysqli).");";
			$this->query($query);
		}
		else {
			$query = "CALL imagesInsertThumbnail(".
				$this->escapeSQLValue($path).",".
				$this->escapeSQLValue($src_dims->width).",".
				$this->escapeSQLValue($src_dims->height).",".
				$this->alt->escapeSQL($this->mysqli).");";
			$this->query($query);
			$thumbnail_id = $this->retrieveInsertID();
		}
		return ($thumbnail_id);
	}

	/**
	 * Moves image file upload to its final location on the file system.
	 * @param string $tmp_path Path to file upload.
	 * @param string $target_name New file name for image file.
	 * @param string $upload_dir Path to file uploads.
	 * @param ImageDims $target_dims Target width and height for the final image.
	 * @param string $target_ext Target extension, indicating image file type.
	 * @param string $sub_dir Path within the images directory where the image file should be stored.
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	protected function moveUploadToDestination($tmp_path, $target_name, $upload_dir, $target_dims=null, $target_ext='', $sub_dir='')
	{
		if ($target_dims===null) {
			$target_dims = new ImageDims();
		}
		if ($target_dims->width>0 || $target_dims->height>0) {
			/* move image to get at its contents */
			$tmp_upload_path = $upload_dir."tmp_".$target_name;
			if (!move_uploaded_file($tmp_path, $tmp_upload_path)) {
				throw new OperationAbortedException("Error moving uploaded file.");
			}

			$this->formatUniquePath($upload_dir, $target_name);
			$this->resample($tmp_upload_path, $target_name, $target_dims, $target_ext, $sub_dir, true);
		}
		else {
			parent::moveUploadToDestination($tmp_path, $target_name, $upload_dir);
		}
	}

	/**
	 * Processes an image upload, extracting keywords, resizing, cropping, etc. before saving the image
	 * upload in its final location.
	 * @param string[optional] $sub_dir Path within the images directory where the image should be stored.
	 * @param string[optional] $target_basename Target filename of the image file.
	 * @param bool[optional] $randomize Flag that indicates the image file name should be a random sequence of characters. Defaults to FALSE.
	 * @param ImageDims[optional] $target_dims Width and height of the final image.
	 * @param string[optional] $target_ext Target extension, indicating image file type.
	 * @throws InvalidTypeException
	 * @throws OperationAbortedException
	 * @throws ResourceNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function placeUploadFile( $sub_dir='', $target_basename='', $randomize=false, $target_dims=null, $target_ext='')
	{
		list($tmp_path, $upload_dir) = $this->processUpload($sub_dir, $target_basename, $randomize);
		$this->moveUploadToDestination($tmp_path, $this->target_name, $upload_dir, $target_dims, $target_ext, $sub_dir);
	}

    /**
	 * Resizes an image file.
	 * @param string $src_path Path to the source image file.
	 * @param string $target_name Filename of the new image file.
	 * @param ImageDims|null[optional] $target_dims Target width and height of the new file in pixels.
	 * @param string[optional] $target_ext Specify non-default extension for the destination file.
	 * @param string[optional] $sub_dir Subdirectory path for destination file. Defaults to no subdirectory.
	 * @param bool[optional] $do_cleanup Removes the source file from disk if set to TRUE. Defaults to FALSE.
	 * @throws InvalidTypeException Unsupported image file type.
	 * @throws OperationAbortedException Error saving resampled image file.
	 * @throws ResourceNotFoundException Source image file not found.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Exception
	 */
	public function resample($src_path, $target_name, $target_dims=null, $target_ext="", $sub_dir="", $do_cleanup=false)
	{
		$image_root = $this->getSiteRoot($this->image_dir);

		list($image, $ext, $src_dims) = $this->loadImageProperties($src_path, $target_name);

		$upload_path = $this->formatUploadPath($image_root, $target_name, $ext, $target_ext, $sub_dir);

		/* get target dimensions */
        if ($target_dims===null) {
            $target_dims = new ImageDims();
        }
		$target_dims = $this->calcTargetDimensions($src_dims, $target_dims);

		if ($target_dims->width>0 && $target_dims->height>0) {

			/* resize and save new image to disk */
			$image_p = $this->resizeImage($image, $src_dims, $target_dims);
			$this->commitImageDataToDisk($image_p, $target_ext, $upload_path);
		}

		/* clean up */
		if($do_cleanup) {
			@unlink($src_path);
		}
	}

	/**
	 * Resample and crop image data to fit target dimensions.
	 * @param resource $image Raw image data.
	 * @param ImageDims $src_dims Source image dimensions.
	 * @param ImageDims $dst_dims Target image dimensions.
	 * @return resource
	 */
	protected function resizeImage(&$image, $src_dims, $dst_dims)
	{
		/* original smaller than target; don't scale the image scale up */
		if ($src_dims->width<=$dst_dims->width && $src_dims->height<=$dst_dims->height)
		{
			return($image);
		}

		/* target dims don't match original dims; crop the image */
		elseif (($src_dims->width/$dst_dims->height) != ($src_dims->width/$src_dims->height))
		{
			return($this->cropImage($image, $src_dims, $dst_dims));
		}

		/* scale the image down evenly */
		else
		{
			$image_p = imagecreatetruecolor($dst_dims->width, $dst_dims->height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $dst_dims->width, $dst_dims->height, $src_dims->width, $src_dims->height);
			return($image_p);
		}
	}
}