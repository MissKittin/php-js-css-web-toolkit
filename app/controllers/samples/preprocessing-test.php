<?php
	// wrapper for file functions
	class preprocessed_cache
	{
		private $cache_file_handle;
		public function __construct($output_file)
		{
			$this->cache_file_handle=fopen($output_file, 'w');
			fwrite($this->cache_file_handle, '<?php ');
		}
		public function push($input)
		{
			fwrite($this->cache_file_handle, $input);
		}
		public function __destruct()
		{
			fwrite($this->cache_file_handle, ' ?>');
			fclose($this->cache_file_handle);
		}
	}

	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	// will be refreshed hourly ("Cache file was created" will disappear in an hour)
	include './lib/ob_cache.php';
	if(ob_file_cache('./tmp/cache_'.str_replace('/', '___', strtok($_SERVER['REQUEST_URI'], '?'))) === 0)
		exit();

	$view['title']='Preprocessing test';
	$view['cache-created']=false;

	// won't be refreshed
	$cache_file='preprocessing-test.php';
	if(!file_exists('./tmp/' . $cache_file))
	{
		if(!file_exists('./tmp')) mkdir('./tmp');
		$cache_object=new preprocessed_cache('./tmp/' . $cache_file);

		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$cache_object->push('$view[\'windows\']=true;');
		else
			$cache_object->push('$view[\'windows\']=false;');

		if(php_sapi_name() == 'cli-server')
			$cache_object->push('$view[\'builtin_server\']=true;');
		else
			$cache_object->push('$view[\'builtin_server\']=false;');


		$view['cache-created']=true;

		unset($cache_object);
	}
	include './tmp/' . $cache_file;

	include './app/models/samples/preprocessing-test.php';
	include './app/views/samples/default/default.php';
?>