<?php
	/*
	 * uri_router.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$GLOBALS['do_echo']=false;
	function exec_uri_router()
	{
		$GLOBALS['default_route']=0;
		$GLOBALS['simple_route']=0;
		$GLOBALS['multipath_route']=0;
		$GLOBALS['regex_route']=0;
		$GLOBALS['post_route']=0;

		uri_router::set_base_path('/basepth');
		uri_router::set_source(strtok($_SERVER['REQUEST_URI'], '?'));
		uri_router::set_request_method($_SERVER['REQUEST_METHOD']);

		uri_router::set_default_route(function(){
			++$GLOBALS['default_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed default_route)';
		});

		uri_router::add(['/arg1/arg2/arg3'], function(){
			++$GLOBALS['simple_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed simple_route)';
		});

		uri_router::add(['/arg1/arg6/arg3', '/arg1/arg7/arg3'], function(){
			++$GLOBALS['multipath_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed multipath_route)';
		});

		uri_router::add(['/arg1/arg([0-9])/arg3'], function(){
			++$GLOBALS['regex_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed regex_route)';
		}, true);

		uri_router::add(['/arg1/arg2/arg3'], function(){
			++$GLOBALS['post_route'];

			if($GLOBALS['do_echo'])
				echo ' (executed post_route)';
		}, false, 'POST');

		uri_router::route();
	}

	$errors=[];

	foreach([false, true] as $reverse_mode)
	{
		if($reverse_mode)
			echo ' -> Testing reverse mode'.PHP_EOL;
		else
			echo ' -> Testing simple mode'.PHP_EOL;

		uri_router::set_reverse_mode($reverse_mode);

		foreach(['GET', 'POST'] as $method)
		{
			echo '  -> Testing '.$method.PHP_EOL;
				$_SERVER['REQUEST_METHOD']=$method;

			echo '   -> default_route';
				$_SERVER['REQUEST_URI']='/basepth/defaultroute';
				exec_uri_router();
				if($GLOBALS['default_route'] === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;

					if($reverse_mode)
						$errors[]='reverse_mode '.$method.' default_route';
					else
						$errors[]='simple_mode '.$method.' default_route';
				}

			echo '   -> simple_route';
				$_SERVER['REQUEST_URI']='/basepth/arg1/arg2/arg3?getarg1=getval1&getarg2=getval2';
				exec_uri_router();

				if($reverse_mode)
				{
					if($method === 'POST')
						$simple_route_value=$GLOBALS['post_route'];
					else
						$simple_route_value=$GLOBALS['regex_route'];
				}
				else
					$simple_route_value=$GLOBALS['simple_route'];

				if($simple_route_value === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;

					if($reverse_mode)
						$errors[]='reverse_mode '.$method.' simple_route';
					else
						$errors[]='simple_mode '.$method.' simple_route';
				}

			echo '   -> multipath_route';
				$_SERVER['REQUEST_URI']='/basepth/arg1/arg6/arg3?getarg1=getval1&getarg2=getval2';
				exec_uri_router();

				if($reverse_mode)
					$multipath_route_value=$GLOBALS['regex_route'];
				else
					$multipath_route_value=$GLOBALS['multipath_route'];

				if($multipath_route_value === 1)
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';

					if($reverse_mode)
						$errors[]='reverse_mode '.$method.' multipath_route test1';
					else
						$errors[]='simple_mode '.$method.' multipath_route test1';
				}

				$_SERVER['REQUEST_URI']='/basepth/arg1/arg7/arg3?getarg1=getval1&getarg2=getval2';
				exec_uri_router();

				if($reverse_mode)
					$multipath_route_value=$GLOBALS['regex_route'];
				else
					$multipath_route_value=$GLOBALS['multipath_route'];

				if($multipath_route_value === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;

					if($reverse_mode)
						$errors[]='reverse_mode '.$method.' multipath_route test2';
					else
						$errors[]='simple_mode '.$method.' multipath_route test2';
				}

			echo '   -> regex_route';
				$_SERVER['REQUEST_URI']='/basepth/arg1/arg0/arg3?getarg1=getval1&getarg2=getval2';
				exec_uri_router();
				if($GLOBALS['regex_route'] === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;

					if($reverse_mode)
						$errors[]='reverse_mode '.$method.' regex_route';
					else
						$errors[]='simple_mode '.$method.' regex_route';
				}

			echo '   -> post_route';
				$_SERVER['REQUEST_URI']='/basepth/arg1/arg2/arg3?getarg1=getval1&getarg2=getval2';
				exec_uri_router();

				if($reverse_mode)
				{
					if($method === 'POST')
						$post_route_value=$GLOBALS['post_route'];
					else
						$post_route_value=$GLOBALS['regex_route'];
				}
				else
					$post_route_value=$GLOBALS['simple_route'];

				if($post_route_value === 1)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;

					if($reverse_mode)
						$errors[]='reverse_mode '.$method.' post_route';
					else
						$errors[]='simple_mode '.$method.' post_route';
				}
		}
	}

	echo ' -> Testing run_callback method';
		$_SERVER['REQUEST_URI']='/basepth/arg1/arg2/arg3?getarg1=getval1&getarg2=getval2';
		$GLOBALS['custom_router_arg_a']=0;
		$GLOBALS['custom_router_arg_b']=0;

		class custom_router extends uri_router
		{
			protected static function run_callback(callable $callback)
			{
				$callback('example-arg-1', 'example-arg-2');
			}
		}

		custom_router::set_base_path('/basepth');
		custom_router::set_source(strtok($_SERVER['REQUEST_URI'], '?'));
		custom_router::set_request_method($_SERVER['REQUEST_METHOD']);
		custom_router::add(['/arg1/arg2/arg3'], function($arg_a=null, $arg_b=null){
			if($arg_a === 'example-arg-1')
				++$GLOBALS['custom_router_arg_a'];

			if($arg_b === 'example-arg-2')
				++$GLOBALS['custom_router_arg_b'];

			if($GLOBALS['do_echo'])
				echo ' (executed simple_route from custom_router)';
		});
		custom_router::route();

		foreach(['custom_router_arg_a', 'custom_router_arg_b'] as $router_arg)
			if($GLOBALS[$router_arg] === 1)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]='run_callback '.$router_arg;
			}
		echo PHP_EOL;

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>