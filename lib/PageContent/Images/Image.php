<?php
namespace Littled\PageContent\Images;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;


class Image extends ImageOperations
{
	/**
	 * Create a separate image file containing a thumbnail version of an existing image.
	 * @param string $sub_dir Directory within the images directory where the thumbnail image is to be stored.
	 * @param int $target_length Target size in pixels of the longest edge of the thumbnail image.
	 * @param string $column_name Column in the database that links to the record of the thumbnail.
	 * @return int Record id of the new thumbnail image record.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 */
	public function generateThumbnail(string $sub_dir, int $target_length, string $column_name): int
	{
		/* resize the image to make a thumbnail */
		$path = $this->getSiteRoot($this->path->value).$this->path->value;
		$tn_path = preg_replace('/^(.*\/)/', "$1$sub_dir/", $path);

		/* get new dimensions */
		if ($target_length<1) {
			throw new ConfigurationUndefinedException('Image target dimension size not set.');
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
		$query = "SELECT `$column_name` FROM `image_link` WHERE image_id = ?";
		$data = $this->fetchRecords($query, 'i', $this->id->value);
		if (count($data)> 0) {
			$tn_id = $data[0]->$column_name;
		}

		$tn_path = preg_replace('/^(.*\/)/', "$1$sub_dir/", $this->path->value);
		if ($tn_id>0) {
			$this->connectToDatabase();
            $query = 'UPDATE `images` SET path = ?, width = ?, height = ?, alt= ? WHERE id = ?';
			$this->query($query, 'siisi', $tn_path, $new_w, $new_h, $this->alt->value, $tn_id);
		}
		else {
			$query = 'INSERT INTO `images` (path,width,height,alt) VALUES (?,?,?,?)';
			$this->query($query, 'siis', $tn_path, $new_w, $new_h, $this->alt->value);
			$tn_id = $this->retrieveInsertID();
		}
		return ($tn_id);
	}
}