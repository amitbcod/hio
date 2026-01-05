<?php

use Intervention\Image\ImageManager;

class Image_upload
{
	private const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'gif'];
	public const ALLOWED_MIME_TYPES = ['image/gif' => 'gif', 'image/jpeg' => 'jpg', 'image/png' => 'png'];

	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('S3_filesystem');
	}

	public function upload_image(array $file, string $image_path, string $image_filename, array $options = [], array $extra_sizes = [])
	{
		$imageFileType = strtolower(pathinfo($image_filename,PATHINFO_EXTENSION));

		if(!$this->is_allowed_image($imageFileType)) {
			return false;
		}


		if(($options['store_main'] ?? true) !== false){
			// Resize main file (?)
			$imageContents = $this->resizeImage($file['tmp_name'], $options);

			// store on s3
			$this->CI->s3_filesystem->put($imageContents, $image_path . '/' . $image_filename);
		}

		// create extra sizes
		foreach($extra_sizes as $subdir => $size_options){
			$imageContents = $this->resizeImage($file['tmp_name'], $size_options);

			// store on s3
			$this->CI->s3_filesystem->put($imageContents,   $image_path . '/' .$subdir . '/' .$image_filename);
		}

		return true;
	}

	private function resizeImage($source_image, $options)
	{
		$manager = new ImageManager();

		$image = $manager->make($source_image);

		if((!empty($options['width']) && is_numeric($options['width'])) ||
			(!empty($options['height']) && is_numeric($options['height']))){
			$image->resize(
				is_numeric($options['width']) ? $options['width'] : null,
				is_numeric($options['height']) ? $options['height'] : null,
				function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				}
			);
		}
		$result = $image->encode();
		$image->destroy();

		return $result;
	}

	public function is_allowed_image(string $imageFileType): bool
	{
		return in_array($imageFileType, self::ALLOWED_FILE_TYPES);
	}
}
