<?php
	/*
	 * directoryIterator_sort.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	namespace Test
	{
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

		echo ' -> Including '.basename(__FILE__);
			if(!file_exists(__DIR__.'/../lib/'.basename(__FILE__)))
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

			eval(
				'namespace Test { ?>'
					.file_get_contents(__DIR__.'/../lib/'.basename(__FILE__))
				.'<?php }'
			);
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing library';
			if(str_replace(["\n", ' '], '', var_export(directoryIterator_sort('none', ['get_filesize', 'is_compressed'], 'get_filename'), true)) === "array('aa'=>array('get_filesize'=>538,'is_compressed'=>false,),'hd'=>array('get_filesize'=>2873,'is_compressed'=>true,),'ku'=>array('get_filesize'=>6262,'is_compressed'=>true,),'ue'=>array('get_filesize'=>4142,'is_compressed'=>false,),'zz'=>array('get_filesize'=>14173,'is_compressed'=>true,),)")
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
	}
?>