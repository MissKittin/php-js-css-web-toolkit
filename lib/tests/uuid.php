<?php
	/*
	 * uuid.php library test
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

		echo ' -> Mocking functions';
			function microtime($as_float=false, $reset=false)
			{
				static $index=0;

				if($reset)
				{
					$index=0;
					return null;
				}

				if(!isset($GLOBALS['_microtime_mock'][$index]))
					throw new Exception('microtime mock: out of bounds');

				$output=$GLOBALS['_microtime_mock'][$index];
				++$index;

				if($as_float)
					return (float)$output;

				return (string)$output;
			}
			function random_bytes($bytes, $reset=false)
			{
				static $index=0;

				if($reset)
				{
					$index=0;
					return null;
				}

				if(!isset($GLOBALS['_random_bytes_mock'][$index]))
					throw new Exception('random_bytes mock: out of bounds');

				$output=$GLOBALS['_random_bytes_mock'][$index];
				++$index;

				return hex2bin($output);
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Mocking classes';
			class DateTimeImmutable extends \DateTimeImmutable {}
			class Exception extends \Exception {}
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

		$failed=false;

		echo ' -> Testing generate_uuid_v1';
			$GLOBALS['_random_bytes_mock']=['0538', '6284af1e4a25'];
			$GLOBALS['_microtime_mock']=[1697127475.7568];
			$uuidv1=generate_uuid_v1();
			//echo $uuidv1;
			if(
				($uuidv1 === 'e6d0b600-0000-1000-8538-6384af1e4a25') || // windows
				($uuidv1 === 'e6d0b600-691a-11ee-8538-6384af1e4a25') // unix
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			microtime(false, true);
			random_bytes(0, true);

		echo ' -> Testing generate_uuid_v3';
			if(generate_uuid_v3('5f6384bfec4ca0b2d4114a13aa2a5435', 'delftstack') === '7269a0c8-f91a-34b9-80c2-6a90b1bbf856')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(generate_uuid_v3('591531f16f581b69a390980eb282ba83', 'this is delftstack!') === '3bf89731-20f0-3d78-b06a-3f70c6fff898')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing generate_uuid_v4';
			if(generate_uuid_v4(hex2bin('e9d1d0cbee26761d918d83ec1f29f7e7')) === 'e9d1d0cb-ee26-461d-918d-83ec1f29f7e7')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing generate_uuid_v5';
			if(generate_uuid_v5('8fc990b07418d5826d98de952cfb268dee4a23a3', 'delftstack') === 'd5ca61e8-ef53-5916-9bf2-2809bd81c832')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(generate_uuid_v5('24316ec81e3bea40286b986249a41e29924d35bf', 'this is delftstack!') === '7e506c50-b7fc-5180-9f9f-2bf28fdd946d')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		echo ' -> Testing generate_uuid_ordered';
			$GLOBALS['_random_bytes_mock']=['4e06fa244f', 'e98350'];
			$GLOBALS['_microtime_mock']=['0.30126400 1697129318'];
			if(generate_uuid_ordered() === '060787b627be50-004e-06fa-244f-e98350')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			microtime(false, true);
			random_bytes(0, true);

		echo ' -> Testing is_uuid';
			if(is_uuid('e9d1d0cb-ee26-461d-918d-83ec1f29f7e7'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(is_uuid('not-uuid'))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing is_uuid_v1';
			if(is_uuid_v1('e6d0b600-0000-1000-8538-6384af1e4a25'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(is_uuid_v1('e9d1d0cb-ee26-461d-918d-83ec1f29f7e7'))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing is_uuid_v2';
			if(is_uuid_v2('000003e8-6922-21ee-bf00-325096b39f47'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(is_uuid_v2('e9d1d0cb-ee26-461d-918d-83ec1f29f7e7'))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing is_uuid_v3';
			if(is_uuid_v3('7269a0c8-f91a-34b9-80c2-6a90b1bbf856'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(is_uuid_v3('e9d1d0cb-ee26-461d-918d-83ec1f29f7e7'))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing is_uuid_v4';
			if(is_uuid_v4('e9d1d0cb-ee26-461d-918d-83ec1f29f7e7'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(is_uuid_v4('7269a0c8-f91a-34b9-80c2-6a90b1bbf856'))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing is_uuid_v5';
			if(is_uuid_v5('d5ca61e8-ef53-5916-9bf2-2809bd81c832'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(is_uuid_v5('e9d1d0cb-ee26-461d-918d-83ec1f29f7e7'))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing is_uuid_ordered';
			if(is_uuid_ordered('060787b627be50-004e-06fa-244f-e98350'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(is_uuid_ordered('e9d1d0cb-ee26-461d-918d-83ec1f29f7e7'))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;

		echo ' -> Testing decode_uuid_ordered';
			if(var_export_contains(
				decode_uuid_ordered('0564a6553c9fbe-003a-46b0-28db-456107'),
				"array('microtime'=>'1518040440938430','datetime'=>Test\DateTimeImmutable::__set_state(array('date'=>'2018-02-0721:54:00.000000','timezone_type'=>1,'timezone'=>'+00:00',)),'identifier'=>'','rand'=>'3a46b028db456107',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		if($failed)
			exit(1);
	}
?>