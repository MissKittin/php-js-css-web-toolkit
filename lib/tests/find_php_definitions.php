<?php
	/*
	 * find_php_definitions.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  tokenizer extension is required
	 */

	if(!function_exists('token_get_all'))
	{
		echo 'tokenizer extension is not loaded'.PHP_EOL;
		exit(1);
	}

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

	$source='<?php
		function testfunction()
		{
			echo "output";
		}
		function testfunctionB()
		{
			echo "output";
		}
		$testclosure=function()
		{
			echo "output";
		};
		$testclosureB=function()
		{
			echo "output";
		};
		interface testinterface
		{
			public function testmethod();
		}
		interface testinterfaceB
		{
			public function testmethod();
		}
		trait testtrait
		{
			public function traitmethod()
			{
				echo "output";
			}
			public function traitmethodB()
			{
				echo "output";
			}
		}
		trait testtraitB
		{
			public function traitmethod()
			{
				echo "output";
			}
			public function traitmethodB()
			{
				echo "output";
			}
		}
		class testclass implements testinterface
		{
			use testtrait;

			public function testmethod()
			{
				echo "output";
			}
			public function testmethodB()
			{
				echo "output";
			}
		}
		class testclassB implements testinterfaceB
		{
			use testtraitB;

			public function testmethod()
			{
				echo "output";
			}
			public function testmethodB()
			{
				echo "output";
			}
		}
	?>';
	$failed=false;
	$result=find_php_definitions($source);

	echo ' -> Testing classes';
		if(isset($result['classes'][0]) && ($result['classes'][0] === 'testclass'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(isset($result['classes'][1]) && ($result['classes'][1] === 'testclassB'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing functions';
		if(isset($result['functions'][0]) && ($result['functions'][0] === 'testfunction'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(isset($result['functions'][1]) && ($result['functions'][1] === 'testfunctionB'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing interfaces';
		if(isset($result['interfaces'][0]) && ($result['interfaces'][0] === 'testinterface'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(isset($result['interfaces'][1]) && ($result['interfaces'][1] === 'testinterfaceB'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing traits';
		if(isset($result['traits'][0]) && ($result['traits'][0] === 'testtrait'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(isset($result['traits'][1]) && ($result['traits'][1] === 'testtraitB'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>