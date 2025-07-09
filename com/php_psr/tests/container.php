<?php
	/*
	 * container.php test
	 */

	function test_container($container, &$failed)
	{
		$container->set('class_test', function(){
			return true;
		});
		if($container->has('class_test'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($container->has('class_test_nonexistent'))
		{
			echo ' [FAIL]';
			$failed=true;
		}
		else
			echo ' [ OK ]';
		try {
			if($container->get('class_test') === true)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
		} catch(Psr\Container\NotFoundExceptionInterface $error) {
			echo ' [FAIL] (caught)';
			$failed=true;
		}
		try {
			$container->get('class_test_nonexistent');
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		} catch(Psr\Container\NotFoundExceptionInterface $error) {
			echo ' [ OK ]'.PHP_EOL;
		}
	}

	if(!file_exists(
		__DIR__.'/tmp/.composer/vendor/psr/container'
	)){
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/.composer');

		if(file_exists(__DIR__.'/../bin/composer.phar'))
			$_composer_binary=__DIR__.'/../bin/composer.phar';
		else if(file_exists(__DIR__.'/../../../bin/composer.phar'))
			$_composer_binary=__DIR__.'/../../../bin/composer.phar';
		else if(file_exists(__DIR__.'/tmp/.composer/composer.phar'))
			$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
		else if(
			file_exists(__DIR__.'/../bin/get-composer.php') ||
			file_exists(__DIR__.'/../../../bin/get-composer.php')
		){
			echo ' -> Downloading composer'.PHP_EOL;

			$_composer_binary=__DIR__.'/../bin/get-composer.php';

			if(file_exists(__DIR__.'/../../../bin/get-composer.php'))
				$_composer_binary=__DIR__.'/../../../bin/get-composer.php';

			system(''
			.	'"'.PHP_BINARY.'" '
			.	'"'.$_composer_binary.'" '
			.	'"'.__DIR__.'/tmp/.composer"'
			);

			if(!file_exists(__DIR__.'/tmp/.composer/composer.phar'))
			{
				echo ' <- composer download failed [FAIL]'.PHP_EOL;
				exit(1);
			}

			$_composer_binary=__DIR__.'/tmp/.composer/composer.phar';
		}
		else
		{
			echo 'Error: get-composer.php tool not found'.PHP_EOL;
			exit(1);
		}

		echo ' -> Installing psr/container'.PHP_EOL;

		system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
		.	'--no-cache '
		.	'"--working-dir='.__DIR__.'/tmp/.composer" '
		.	'require psr/container'
		);
	}

	if(is_file(__DIR__.'/tmp/.composer/vendor/autoload.php'))
	{
		echo ' -> Including composer autoloader';
			if(@(include __DIR__.'/tmp/.composer/vendor/autoload.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;
	}
	else
		echo ' -> Including composer autoloader [SKIP]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		try {
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		} catch(Throwable $error) {
			echo ' [FAIL]'
			.	PHP_EOL.PHP_EOL
			.	'Caught: '.$error->getMessage()
			.	PHP_EOL;

			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing ioc_closure_container_psr';
		test_container(new ioc_closure_container_psr(), $failed);

	echo ' -> Testing ioc_autowired_container_psr';
		test_container(new ioc_autowired_container_psr(), $failed);

	if($failed)
		exit(1);
?>