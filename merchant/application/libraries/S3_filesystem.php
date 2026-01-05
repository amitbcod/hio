<?php

class S3_filesystem
{
	/**
	 * @var \League\Flysystem\Filesystem
	 */
	public $filesystem;

	/**
	 * @var \Aws\S3\S3Client
	 */
	public $client;

	public function __construct($config = [])
	{
		$this->client = new Aws\S3\S3Client([
			'version'     => 'latest',
			'region'      => AWS_DEFAULT_REGION,
			'credentials' => [
				'key'    => AWS_ACCESS_KEY_ID,
				'secret' => AWS_SECRET_ACCESS_KEY,
			],
		]);
		$adapter = new League\Flysystem\AwsS3V3\AwsS3V3Adapter(
			$this->client,
			$config['bucket'] ?? get_s3_bucket()
		);

		$this->filesystem = new League\Flysystem\Filesystem($adapter);
	}


	public function put($file_contents, $target_path){
		$this->filesystem->write($target_path, $file_contents, ['visibility' => 'public']);
	}

	public function putFile($source_file, $target_path){
		$this->put(file_get_contents($source_file), $target_path);
	}

	public function __call($method, $arguments)
	{
		return call_user_func([$this->filesystem, $method], ...$arguments);
	}
}
