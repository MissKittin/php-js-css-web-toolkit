<?php
	/*
	 * pf_get_debug_type.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 */

	namespace Test
	{
		use stdClass;

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

		echo ' -> Mocking functions';
			function function_exists()
			{
				return false;
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach([
			'has_php_close_tag.php',
			'include_into_namespace.php'
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

		$failed=false;

		echo ' -> Testing library'.PHP_EOL;
			class incomplete_class_test {}
			$test_set=[
				'null'=>null,
				'bool'=>true,
				'int'=>1,
				'float'=>0.1,
				'string'=>'foo',
				'array'=>[],
				'resource (stream)'=>fopen(__FILE__, 'r'),
				'resource (closed)'=>fopen(__FILE__, 'r'),
				'stdClass'=>new stdClass(),
				'class@anonymous'=>new class {},
				'Closure'=>function(){},
				'stdClass@anonymous'=>new class extends stdClass {},
				'__PHP_Incomplete_Class'=>unserialize(serialize(new incomplete_class_test()), ['allowed_classes'=>false])
			];
			fclose($test_set['resource (closed)']);
			foreach($test_set as $return_value=>$param){
				echo '  -> '.$return_value;
				//echo ' ['.get_debug_type($param).']';

				switch($return_value)
				{
					case '__PHP_Incomplete_Class':
						if(get_debug_type($param) === $return_value)
							echo ' [ OK ]'.PHP_EOL;
						else
							echo ' [FAIL]'.PHP_EOL;
					break;
					default:
						if(get_debug_type($param) === $return_value)
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							echo ' [FAIL]'.PHP_EOL;
							$failed=true;
						}
				}
			}

		if($failed)
			exit(1);
	}
?>