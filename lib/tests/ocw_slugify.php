<?php
	/*
	 * ocw_slugify.php library test
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

	echo ' -> Testing ocw_slugify';
		if(extension_loaded('iconv'))
		{
			if(
				(ocw_slugify('Cómo hablar en sílabas') === 'como-hablar-en-silabas') ||
				(ocw_slugify('Cómo hablar en sílabas') === 'cmo-hablar-en-slabas')
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	echo ' -> Testing sgmurphy_url_slug';
		if(function_exists('mb_strlen'))
		{
			if(
				sgmurphy_url_slug('Qu\'en est-il français? Ça marche alors?', ['transliterate'=>true])
				===
				'qu-en-est-il-francais-ca-marche-alors'
			)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(
				sgmurphy_url_slug('Что делат, если. Я не хочу, UTF-8?', ['transliterate'=>true])
				===
				'chto-delat-esli-ya-ne-hochu-utf-8'
			)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(
				sgmurphy_url_slug(
					'This is an Example String. What\'s Going to Happen to Me?',
					[
						'delimiter'=>'_',
						'limit'=>40,
						'lowercase'=>false,
						'replacements'=>[
							'/\b(an)\b/i'=>'a',
							'/\b(example)\b/i'=>'Test'
						]
					]
				)
				===
				'This_is_a_Test_String_What_s_Going_to_Ha'
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		}
		else
			echo ' [SKIP]'.PHP_EOL;

	echo ' -> Testing ocw_artisan_slug';
		if(ocw_artisan_slug('Cómo hablar en sílabas') === 'como-hablar-en-silabas')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(ocw_artisan_slug('Álix Ãxel') === 'alix-axel')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(ocw_artisan_slug('Álix----_Ãxel!?!?') === 'alix-axel')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>