<?php
	/*
	 * string_translator.php library test
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

	echo ' -> Testing default lang';
		$lang=new string_translator();
		if($lang('String') === 'String')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang('Log in') === 'Log in')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>2, '%s'=>3, '%d'=>'s']
		) === '2 minutes and 3 seconds left')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>2, '%s'=>1, '%d'=>'']
		) === '2 minutes and 1 second left')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing translation';
		$lang=new string_translator([
			'String'=>'Sznurek',
			'Log in'=>'Zaloguj sie',
			'%m minutes and %s second%d left'=>'Zostalo %m minut%x i %s sekund%d'
		]);
		if($lang('String') === 'Sznurek')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang('Log in') === 'Zaloguj sie')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>2, '%x'=>'y', '%s'=>5, '%d'=>'']
		) === 'Zostalo 2 minuty i 5 sekund')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>5, '%x'=>'', '%s'=>1, '%d'=>'a']
		) === 'Zostalo 5 minut i 1 sekunda')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing JSON single';
		$json=''
		.	'{'
		.		'"String": "Sznurek",'
		.		'"Log in": "Zaloguj sie",'
		.		'"%m minutes and %s second%d left": "Zostalo %m minut%x i %s sekund%d"'
		.	'}'
		;
		$lang=new string_translator(string_translator::from_json($json));
		if($lang('String') === 'Sznurek')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang('Log in') === 'Zaloguj sie')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>2, '%x'=>'y', '%s'=>5, '%d'=>'']
		) === 'Zostalo 2 minuty i 5 sekund')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>5, '%x'=>'', '%s'=>1, '%d'=>'a']
		) === 'Zostalo 5 minut i 1 sekunda')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing JSON multi';
		$json=''
		.	'{'
		.		'"pl": {'
		.			'"String": "Sznurek",'
		.			'"Log in": "Zaloguj sie",'
		.			'"%m minutes and %s second%d left": "Zostalo %m minut%x i %s sekund%d"'
		.		'},'
		.		'"ru": {'
		.			'"String": "Priwiet",'
		.			'"Log in": "Awtorizowatsia",'
		.			'"%m minutes and %s second%d left": "Wnimanje: %m minuty i %s sekundy"'
		.		'}'
		.	'}'
		;
		$lang=new string_translator(string_translator::from_json($json, 'pl'));
		if($lang('String') === 'Sznurek')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang('Log in') === 'Zaloguj sie')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>2, '%x'=>'y', '%s'=>5, '%d'=>'']
		) === 'Zostalo 2 minuty i 5 sekund')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if($lang(
			'%m minutes and %s second%d left',
			['%m'=>5, '%x'=>'', '%s'=>1, '%d'=>'a']
		) === 'Zostalo 5 minut i 1 sekunda')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>