<?php
	/*
	 * Facade for Maxime Bouroumeau-Fuseau's DebugBar
	 *
	 * WARNING!!!
	 *  NEVER EVER ENABLE THIS LIBRARY ON PRODUCTION!!!
	 *
	 * Warning:
	 *  php-debugbar/php-debugbar >=1.17.0 package is recommended
	 *
	 * Note:
	 *  throws an maximebf_debugbar_exception on error
	 *
	 * Starting the bar:
	 *  for the library to work, you need to activate it,
	 *  add the path(s) to the vendor directory
	 *  and add routing rules for styles and scripts:
		if(maximebf_debugbar
		::	enable(($app_env === 'dev'))
		::	custom_debug_bar(function(){ // optional, replace DebugBar\DebugBar with another one, must be after enable()
				return new DebugBar\StandardDebugBar();
			})
		::	set_vendor_dir('./vendor') // must be after enable()
		::	set_vendor_dir('phar://./vendor.phar/vendor') // you can hit and miss, the one above has priority if hit
		::	set_storage( // optional, see https://php-debugbar.com/docs/storage/
				(class_exists('\DebugBar\Storage\FileStorage')) ?
				new DebugBar\Storage\FileStorage(
					'./logs/maximebf_debugbar'
				) :
				new maximebf_debugbar_dummy()
			)
		::	collectors([ // optional
				'pdo'=>(class_exists('\DebugBar\DataCollector\PDO\PDOCollector')) ? new DebugBar\DataCollector\PDO\PDOCollector() : new maximebf_debugbar_dummy()
			])
		::	set_csp_nonce('phpdebugbar') // optional
		::	add_csp_header('script-src', 'sha256-hash') // optional
		::	set_base_url('/__PHPDEBUGBAR__')
		::	route(strtok($_SERVER['REQUEST_URI'], '?'))
			exit();
	 *  warning: you have to define collectors at the start, otherwise JavaScript will throw an error
	 *
	 * Content Security Policy (optional):
	 *  if you used method set_csp_nonce and/or add_csp_header
	 *  you can generate an array of CSP rules using get_csp_headers method
	 *  and print them in the <head> section:
		echo '<meta http-equiv="Content-Security-Policy" content="';
			foreach(maximebf_debugbar::get_csp_headers() as $csp_param=>$csp_values)
			{
				echo $csp_param;

				foreach($csp_values as $csp_value)
					echo ' '.$csp_value;

				echo ';';
			}
		echo '">';
	 *
	 * Injecting debug bar code into the page:
	 *  add debugging bar headers to the <head> section (after the CSP rules):
		<?php echo maximebf_debugbar::get_html_headers(); ?>
	 *  at the end of the <body> add the PHP code:
		<?php echo maximebf_debugbar::get_page_content(); ?>
	 *
	 * DebugBar instance:
	 *  to get an instance of the DebugBar\DebugBar object
	 *  (or another object defined with the custom_debug_bar method),
	 *  use the get_instance method:
		if(maximebf_debugbar::is_enabled()) // this if optional
			maximebf_debugbar
			::	get_instance()
			->	stackData();
	 *
	 * Access to collectors:
	 *  to call a method of a specific collector, use the get_collector method:
		$pdo_handle=new PDO('sqlite::memory:');
		if(maximebf_debugbar::is_collector_defined('pdo')) // this if is optional
			maximebf_debugbar
			::	get_collector('pdo')
			->	addConnection($pdo_handle);
	 *
	 * Data collection:
	 *  to add data to a collection, use the collector name as the method (maximebf_debugbar::COLLECTORNAME()):
		maximebf_debugbar::messages()->addMessage('IT WORKS');
		try {
			//
		} catch(Exception $error) {
			maximebf_debugbar::exceptions()->addException($error);
		}
	 *
	 * Production environment:
	 *  if the extension is disabled, you do not need to remove the code
	 *  all operations will be completed successfully
	 *  and data will go to /dev/null using the maximebf_debugbar_dummy class
	 *
	 * Finding the path to an asset:
	 *  by default the route method sets the Content-Type header
	 *   and prints the file using the readfile function
	 *  if you want to get only the path to the resource, add a second argument:
		$asset_path=maximebf_debugbar::route(
			strtok($_SERVER['REQUEST_URI'], '?'),
			true
		); // returns string on success or false on failure
	 */

	class maximebf_debugbar_exception extends Exception {}

	if(PHP_VERSION_ID < 80000) // compatibility bridge for maximebf_debugbar_dummy class
	{
		trait maximebf_debugbar_dummy_arrayaccess
		{
			public function offsetExists($offset)
			{
				return $this->_offsetExists($offset);
			}
			public function offsetGet($offset)
			{
				return $this->_offsetGet($offset);
			}
			public function offsetSet($offset, $value)
			{
				$this->_offsetSet($offset, $value);
			}
			public function offsetUnset($offset)
			{
				$this->_offsetUnset($offset);
			}
		}
	}
	else
	{
		trait maximebf_debugbar_dummy_arrayaccess
		{
			public function offsetExists(mixed $offset): bool
			{
				return $this->_offsetExists($offset);
			}
			public function offsetGet(mixed $offset): mixed
			{
				return $this->_offsetGet($offset);
			}
			public function offsetSet(mixed $offset, mixed $value): void
			{
				$this->_offsetSet($offset, $value);
			}
			public function offsetUnset(mixed $offset): void
			{
				$this->_offsetUnset($offset);
			}
		}
	}

	class maximebf_debugbar
	{
		protected static $base_url=null;
		protected static $csp_headers=[];
		protected static $csp_nonce=null;
		protected static $enabled=false;
		protected static $custom_debug_bar=null;
		protected static $storage=null;
		protected static $collectors=[];
		protected static $instance=null;
		protected static $vendor_dir=null;

		public static function __callStatic($name, $arguments)
		{
			return static::get_instance()[$name];
		}

		public static function set_vendor_dir(string $vendor_dir)
		{
			if(!static::$enabled)
				return static::class;

			if(
				(static::$vendor_dir === null) &&
				file_exists($vendor_dir)
			)
				static::$vendor_dir=$vendor_dir;

			return static::class;
		}
		public static function enable(bool $condition)
		{
			if(!$condition)
				return static::class;

			if(class_exists('\DebugBar\DebugBar'))
				static::$enabled=true;

			return static::class;
		}
		public static function is_enabled()
		{
			return static::$enabled;
		}

		public static function custom_debug_bar(Closure $callback)
		{
			if(!static::$enabled)
				return static::class;

			static::$custom_debug_bar[0]=$callback;

			return static::class;
		}
		public static function set_storage($storage)
		{
			if(!static::$enabled)
				return static::class;

			if(!is_object($storage))
				throw new maximebf_debugbar_exception(
					'$storage is not an object'
				);

			static::$storage=$storage;

			return static::class;
		}
		public static function collectors(array $collectors)
		{
			static::$collectors=$collectors;
			return static::class;
		}
		public static function is_collector_defined(string $collector)
		{
			return isset(static::$collectors[
				$collector
			]);
		}
		public static function get_collector(string $collector)
		{
			if(!isset(static::$collectors[
				$collector
			]))
				throw new maximebf_debugbar_exception(
					'PHP DebugBar '.$collector.' collector is not defined'
				);

			return static::$collectors[
				$collector
			];
		}

		public static function get_instance()
		{
			if(!static::$enabled)
				return new maximebf_debugbar_dummy();

			if(static::$instance === null)
			{
				if(static::$base_url === null)
					throw new maximebf_debugbar_exception(
						'Base URL is not set - use set_base_url() before get_instance()'
					);

				if(static::$custom_debug_bar === null)
					static::$instance=new DebugBar\DebugBar();
				else
					static::$instance=static::$custom_debug_bar[0]();

				if(static::$storage !== null)
					static::$instance->setStorage(
						static::$storage
					);

				foreach(static::$collectors as $collector)
					static::$instance->addCollector($collector);

				static
				::	$instance
				->	getJavascriptRenderer()
				->	setBaseUrl(
						static::$base_url
					);
			}

			return static::$instance;
		}

		public static function set_base_url(string $url)
		{
			static::$base_url=$url;
			return static::class;
		}
		public static function route(
			string $path,
			bool $return_path=false
		){
			if(!static::$enabled)
				return false;

			if(static::$base_url === null)
				throw new maximebf_debugbar_exception(
					'Base URL is not set - use set_base_url() before route()'
				);

			if(static::$vendor_dir === null)
				throw new maximebf_debugbar_exception(
					'vendor directory path is not set - use set_vendor_dir() before enable()'
				);

			$base_path_length=strlen(static::$base_url)+1;

			if(substr($path, 0, $base_path_length) !== static::$base_url.'/')
				return false;

			if(strpos($path, '..') !== false)
				return false;

			$package_dir='/maximebf/debugbar';

			if(is_dir(''
			.	static::$vendor_dir
			.	'/php-debugbar/php-debugbar'
			))
				$package_dir='/php-debugbar/php-debugbar';

			$asset=''
			.	static::$vendor_dir
			.	$package_dir.'/src/DebugBar/Resources/'
			.	substr(
					$path,
					$base_path_length
				);

			if(!is_file($asset))
				return false;

			if($return_path)
				return $asset;

			switch(pathinfo($asset, PATHINFO_EXTENSION))
			{
				case 'css':
					header('Content-Type: text/css');
				break;
				case 'js':
					header('Content-Type: text/javascript');
				break;
				case 'otf':
					header('Content-Type: application/x-font-opentype');
				break;
				case 'woff':
					header('Content-Type: application/font-woff');
				break;
				case 'woff2':
					header('Content-Type: application/font-woff2');
				break;
				default:
					header('Content-Type: '.mime_content_type($asset));
			}

			readfile($asset);

			return true;
		}

		public static function add_csp_header(string $section, string $value)
		{
			static::$csp_headers[$section][]=$value;
			return static::class;
		}
		public static function set_csp_nonce(string $nonce_value)
		{
			if(static::$csp_nonce !== null)
				return static::class;

			static::$csp_nonce=$nonce_value;

			return static
			::	add_csp_header('script-src', '\'nonce-'.$nonce_value.'\'')
			::	add_csp_header('img-src', 'data:')
			::	add_csp_header('font-src', '\'self\'')
			::	add_csp_header('font-src', 'data:');
		}
		public static function get_csp_headers()
		{
			return static::$csp_headers;
		}
		public static function get_html_headers()
		{
			if(!static::$enabled)
				return '';

			$renderer=static
			::	get_instance()
			->	getJavascriptRenderer();

			if(static::$csp_nonce !== null)
				$renderer->setCspNonce(
					static::$csp_nonce
				);

			return $renderer->renderHead();
		}
		public static function get_page_content()
		{
			if(!static::$enabled)
				return '';

			return static
			::	get_instance()
			->	getJavascriptRenderer()
			->	render();
		}
	}
	class maximebf_debugbar_dummy implements ArrayAccess
	{
		use maximebf_debugbar_dummy_arrayaccess;

		public function __call($name, $arguments)
		{
			return new self();
		}
		public function __toString()
		{
			return '';
		}

		protected function _offsetExists($offset)
		{
			return true;
		}
		protected function _offsetGet($offset)
		{
			return new self();
		}
		protected function _offsetSet($offset, $value) {}
		protected function _offsetUnset($offset) {}
	}
?>