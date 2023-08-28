<?php
	/*
	 * directoryIterator_sort.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 *  var_export_contains.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		echo ' -> Mocking classes';
			class directoryIterator implements \Iterator
			{
				private $position=0;
				private $files=[
					['hd', 2873, true],
					['ku', 6262, true],
					['ue', 4142, false],
					['zz', 14173, true],
					['aa', 538, false]
				];

				public function current()
				{
					return $this;
				}
				public function next()
				{
					++$this->position;
				}
				public function key() {}
				public function valid()
				{
					if($this->position < 5)
						return true;

					return false;
				}
				public function rewind() {}

				public function isDot()
				{
					return false;
				}

				public function get_filename()
				{
					return $this->files[$this->position][0];
				}
				public function get_filesize()
				{
					return $this->files[$this->position][1];
				}
				public function is_compressed()
				{
					return $this->files[$this->position][2];
				}
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach([
			'has_php_close_tag.php',
			'include_into_namespace.php',
			'var_export_contains.php'
		] as $library){
			echo ' -> Including '.$library;
				if(is_file(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(is_file(__DIR__.'/../'.$library))
				{
					if(@(include __DIR__.'/../'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;
		}

		echo ' -> Including '.basename(__FILE__);
			if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../lib/'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else if(is_file(__DIR__.'/../'.basename(__FILE__)))
			{
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../'.basename(__FILE__)
				)){
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing library';
			if(var_export_contains(
				directoryIterator_sort('none', ['get_filesize', 'is_compressed'], 'get_filename'),
				"array('aa'=>array('get_filesize'=>538,'is_compressed'=>false,),'hd'=>array('get_filesize'=>2873,'is_compressed'=>true,),'ku'=>array('get_filesize'=>6262,'is_compressed'=>true,),'ue'=>array('get_filesize'=>4142,'is_compressed'=>false,),'zz'=>array('get_filesize'=>14173,'is_compressed'=>true,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
	}
?>