<?php
	class preprocessed_cache
	{
		private $cache_file_handler;

		public function __construct($output_file)
		{
			$this->cache_file_handler=fopen($output_file, 'w');
			fwrite($this->cache_file_handler, '<?php ');
		}
		public function __destruct()
		{
			fwrite($this->cache_file_handler, ' ?>');
			fclose($this->cache_file_handler);
		}

		public function push($input)
		{
			fwrite($this->cache_file_handler, $input);
		}
	}

	require './app/shared/samples/default_http_headers.php';

	// will be refreshed hourly ("Cache file was created" will disappear in an hour)
	require './app/shared/samples/ob_cache.php';
	ob_cache(ob_url2file(), 3600);

	require './app/templates/samples/default/default_template.php';
	$view=new default_template();

	// ./var/lib/preprocessing-test.php won't be refreshed
	$view['cache_created']=false;
	if(!file_exists('./var/lib/preprocessing-test.php'))
	{
		@mkdir('./var/lib', 0777, true);

		$cache_object=new preprocessed_cache('./var/lib/preprocessing-test.php');

		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			$cache_object->push('$view[\'windows\']=true;');
		else
			$cache_object->push('$view[\'windows\']=false;');

		if(php_sapi_name() == 'cli-server')
			$cache_object->push('$view[\'builtin_server\']=true;');
		else
			$cache_object->push('$view[\'builtin_server\']=false;');

		$view['cache_created']=true;
		unset($cache_object);
	}
	require './var/lib/preprocessing-test.php';

	$view->view('./app/views/samples/preprocessing-test');
?>