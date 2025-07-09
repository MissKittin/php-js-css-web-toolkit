<?php
	/*
	 * logger.php test
	 */

	if(!file_exists(
		__DIR__.'/tmp/.composer/vendor/psr/log'
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

		echo ' -> Installing psr/log'.PHP_EOL;

		system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
		.	'--no-cache '
		.	'"--working-dir='.__DIR__.'/tmp/.composer" '
		.	'require psr/log'
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

	echo ' -> Removing temporary files';
		@unlink(__DIR__.'/tmp/logger.txt');
	echo ' [ OK ]'.PHP_EOL;

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

	echo ' -> Testing log_to_psr';
		$logger=new log_to_psr(new log_to_txt([
			'app_name'=>'test',
			'file'=>__DIR__.'/tmp/logger.txt'
		]));
		$date=gmdate('Y-m-d H:i:s');
		$logger->emergency('emergency {message}', ['message'=>'interpolation']);
		$logger->emerg('emerg {message}', ['message'=>'interpolation']);
		$logger->alert('alert {message}', ['message'=>'interpolation']);
		$logger->critical('critical {message}', ['message'=>'interpolation']);
		$logger->crit('crit {message}', ['message'=>'interpolation']);
		$logger->error('error {message}', ['message'=>'interpolation']);
		$logger->warning('warning {message}', ['message'=>'interpolation']);
		$logger->warn('warn {message}', ['message'=>'interpolation']);
		$logger->notice('notice {message}', ['message'=>'interpolation']);
		$logger->info('info {message}', ['message'=>'interpolation']);
		$logger->debug('debug {message}', ['message'=>'interpolation']);
		$logger->log('debug', 'log {message}', ['message'=>'interpolation']);
		if(file_get_contents(__DIR__.'/tmp/logger.txt') === ''
		.	$date.' test [EMERGENCY] emergency interpolation'."\n"
		.	$date.' test [EMERGENCY] emerg interpolation'."\n"
		.	$date.' test [ALERT] alert interpolation'."\n"
		.	$date.' test [CRITICAL] critical interpolation'."\n"
		.	$date.' test [CRITICAL] crit interpolation'."\n"
		.	$date.' test [ERROR] error interpolation'."\n"
		.	$date.' test [WARN] warning interpolation'."\n"
		.	$date.' test [WARN] warn interpolation'."\n"
		.	$date.' test [NOTICE] notice interpolation'."\n"
		.	$date.' test [INFO] info interpolation'."\n"
		.	$date.' test [DEBUG] debug interpolation'."\n"
		.	$date.' test [DEBUG] log interpolation'."\n"
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>