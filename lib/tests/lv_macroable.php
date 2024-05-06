<?php
	/*
	 * lv_macroable.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 */

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
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

	$failed=false;

	echo ' -> Testing macro';
		class macro_test_class extends lv_macroable
		{
			public static $sinput='no';
			public $input='no';
		}
		macro_test_class::macro('macro_test', function($arg){
			$this->input=$arg;
		});
		$macro_test_class=new macro_test_class();
		$macro_test_class->macro_test('ok');
		if($macro_test_class->input === 'ok')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		macro_test_class::macro('macro_static', function($arg){
			static::$sinput=$arg;
		});
		macro_test_class::macro_static('ok');
		if(macro_test_class::$sinput === 'ok')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing mixin';
		class mixin_test_class_content
		{
			public function mixin_static_test()
			{
				return function($arg){
					static::$sinput=$arg;
				};
			}
			public function mixin_test()
			{
				return function($arg){
					$this->input=$arg;
				};
			}
		}
		class mixin_test_class extends lv_macroable
		{
			public static $sinput='no';
			public $input='no';
		}
		mixin_test_class::mixin(new mixin_test_class_content());
		$mixin_test_class=new mixin_test_class();
		$mixin_test_class->mixin_test('ok');
		if($mixin_test_class->input === 'ok')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		mixin_test_class::mixin_static_test('ok');
		if(mixin_test_class::$sinput === 'ok')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing has_macro';
		if(macro_test_class::has_macro('macro_test'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(macro_test_class::has_macro('macro_nonexistent_test'))
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;

	echo ' -> Testing flush_macros';
		macro_test_class::flush_macros();
		if(macro_test_class::has_macro('macro_test'))
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;

	if($failed)
		exit(1);
?>