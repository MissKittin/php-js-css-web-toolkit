<?php
	/*
	 * Usage:
		ob_adapter
			::add(new ob_adapter_module1())
			->add(new ob_adapter_module2())
			->start();
	 *
	 * Modules:
	 *  ob_adapter_obminifier - adapter for ob_minifier.php library
	 *  ob_adapter_obsfucator - adapter for ob_sfucator.php library
	 *  ob_adapter_gzip - ob_gzhandler replacement
	 *  ob_adapter_filecache - basic file cache
	 *  ob_adapter_gunzip - decompress if browser does not support gzip
	 *
	 * See:
	 *  controllers/samples/404.php
	 *  controllers/samples/home.php
	 *  controllers/samples/obsfucate-html.php
	 */

	class ob_adapter
	{
		private static $instances=[];

		public static function add($instance)
		{
			(__CLASS__)::$instances[]=$instance;
			return new static();
		}
		public static function start()
		{
			ob_start(__CLASS__.'::exec');
		}
		public static function exec($buffer, $phase)
		{
			foreach((__CLASS__)::$instances as $instance)
				$buffer=$instance->exec($buffer, $phase);

			return $buffer;
		}
	}

	class ob_adapter_obminifier
	{
		public function __construct()
		{
			if(!function_exists('ob_minifier'))
				require './lib/ob_minifier.php';
		}

		public function exec($buffer)
		{
			return ob_minifier($buffer);
		}
	}
	class ob_adapter_obsfucator
	{
		public function __construct()
		{
			if(!function_exists('ob_sfucator'))
				require './lib/ob_sfucator.php';
		}

		public function exec($buffer)
		{
			return ob_sfucator(
				[
					'title'=>'ob_sfucator',
					'label'=>'<h1>Enable javascript to view content</h1>'
				],
				true, $buffer
			);
		}
	}
	class ob_adapter_gzip
	{
		public function __construct()
		{
			header('Content-Encoding: gzip');
		}

		public function exec($buffer)
		{
			return gzencode($buffer);
		}
	}
	class ob_adapter_filecache
	{
		protected $output_file;

		public function __construct($output_file)
		{
			if(file_exists($output_file))
			{
				if(
					(!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ||
					(!str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
				){
					if(in_array('Content-Encoding: gzip', headers_list()))
						header_remove('Content-Encoding');

					readgzfile($output_file);
				}
				else
					readfile($output_file);

				exit();
			}

			file_put_contents($output_file, '');
			$this->output_file=realpath($output_file);
		}

		public function exec($buffer)
		{
			file_put_contents($this->output_file, $buffer, FILE_APPEND);
			return $buffer;
		}
	}
	class ob_adapter_gunzip
	{
		public function exec($buffer)
		{
			if(
				(!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ||
				(!str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
			){
				if(in_array('Content-Encoding: gzip', headers_list()))
					header_remove('Content-Encoding');

				$raw_buffer=gzdecode($buffer);
				if($raw_buffer !== false)
					return $raw_buffer;
			}

			return $buffer;
		}
	}

	class ob_adapter_filecache_mod extends ob_adapter_filecache
	{
		// I have such a fantasy

		public function __construct($output_file)
		{
			@mkdir('./var/cache', 0777, true);

			parent::__construct('./var/cache/'.$output_file);
		}
	}
?>