<?php
	echo ' -> Including library global_variable_streamer.php';
		if(file_exists(__DIR__.'/lib/global_variable_streamer.php'))
			include __DIR__.'/lib/global_variable_streamer.php';
		else if(file_exists(__DIR__.'/../../../lib/global_variable_streamer.php'))
			include __DIR__.'/../../../lib/global_variable_streamer.php';
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including superclosure_router.php';
		try {
			if(@(include __DIR__.'/../superclosure_router.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		} catch(Throwable $error) {
			echo ' [FAIL]'
				.PHP_EOL.PHP_EOL
				.'Caught: '.$error->getMessage()
				.PHP_EOL;

			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Mocking superglobals';
		$_SERVER['REQUEST_URI']='nonerequri';
		$_SERVER['REQUEST_METHOD']='nonereqmeth';
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Setting up global_variable_streamer';
		global_variable_streamer::register_wrapper('gvs');
		$GLOBALS['router_cache']='';
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Setting up router';
		superclosure_router::set_base_path('/basepth');
		superclosure_router::set_source(strtok($_SERVER['REQUEST_URI'], '?'));
		superclosure_router::set_request_method($_SERVER['REQUEST_METHOD']);

		superclosure_router::set_default_route(function(){
			++$GLOBALS['default_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed default_route)';
		});

		superclosure_router::add(['/arg1/arg2/arg3'], function(){
			++$GLOBALS['simple_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed simple_route)';
		});

		superclosure_router::add(['/arg1/arg6/arg3', '/arg1/arg7/arg3'], function(){
			++$GLOBALS['multipath_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed multipath_route)';
		});

		superclosure_router::add(['/arg1/arg([0-9])/arg3'], function(){
			++$GLOBALS['regex_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed regex_route)';
		}, true);

		superclosure_router::add(['/arg1/arg2/arg3'], function(){
			++$GLOBALS['post_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed post_route)';
		}, false, 'POST');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Dumping cache';
		superclosure_router::add_to_cache('strtok', "strtok(\$_SERVER['REQUEST_URI'], '?')");
		superclosure_router::set_source_variable(superclosure_router::read_from_cache('strtok'));
		superclosure_router::set_request_method_variable("\$_SERVER['REQUEST_METHOD']");
		superclosure_router::dump_cache('gvs://router_cache');
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Validating cache';
		if(md5($GLOBALS['router_cache']) === 'c0b47000caecbd6f75bcb24c589254b4')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>