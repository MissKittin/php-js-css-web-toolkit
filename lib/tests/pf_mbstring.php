<?php
	/*
	 * pf_mbstring.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  mbstring extension is required
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

		if(!extension_loaded('mbstring'))
		{
			echo 'mbstring extension is not loaded'.PHP_EOL;
			exit(1);
		}

		echo ' -> Mocking functions';
			function function_exists()
			{
				return false;
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

		$failed=false;

		echo ' -> Testing mb_chr/mb_ord';
			foreach([65, 63, 0x20AC, 128024] as $value)
				if(mb_ord(mb_chr($value, 'UTF-8'), 'UTF-8') === $value)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
			echo PHP_EOL;
		echo ' -> Testing mb_ord';
			if(mb_ord('A', 'UTF-8') === 65)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(mb_ord('🐘', 'UTF-8') === 128024)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(mb_ord("\x80", 'ISO-8859-1') === 128)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo ' -> Testing mb_scrub';
			if(mb_scrub('Hello World!') === 'Hello World!')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(base64_encode(mb_scrub("H\xC3\xA9llo W\xf2rld!")) === 'SMOpbGxvIFc/cmxkIQ==')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo ' -> Testing mb_str_split';
			if(var_export_contains(
				mb_str_split('Hello 🐘🐘🐘', 2),
				"array(0=>'He',1=>'ll',2=>'o',3=>'🐘🐘',4=>'🐘',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo ' -> Testing mb_str_pad';
			if(mb_str_pad('▶▶', 6, '❤❓❇', STR_PAD_RIGHT) === '▶▶❤❓❇❤')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(mb_str_pad('▶▶', 6, '❤❓❇', STR_PAD_LEFT) === '❤❓❇❤▶▶')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(mb_str_pad('▶▶', 6, '❤❓❇', STR_PAD_BOTH) === '❤❓▶▶❤❓')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(mb_str_pad('🎉', 3, '祝', STR_PAD_LEFT) === '祝祝🎉')
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