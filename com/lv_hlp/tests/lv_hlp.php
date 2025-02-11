<?php
	/*
	 * Tests only new functions and modified methods
	 */

	namespace
	{
		foreach([
			'rmdir_recursive.php',
			'var_export_contains.php'
		] as $library){
			echo ' -> Including '.$library;
				if(file_exists(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(file_exists(__DIR__.'/../../../lib/'.$library))
				{
					if(@(include __DIR__.'/../../../lib/'.$library) === false)
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

		if(
			isset($argv[1]) &&
			($argv[1] === 'composer') &&
			(!file_exists(__DIR__.'/tmp/.composer/vendor/league/commonmark'))
		){
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

			foreach([
				'doctrine/inflector',
				'illuminate/view',
				'league/commonmark',
				'nesbot/carbon'
			] as $_composer_package){
				echo ' -> Installing '.$_composer_package.PHP_EOL;
				system('"'.PHP_BINARY.'" "'.$_composer_binary.'" '
				.	'--no-cache '
				.	'"--working-dir='.__DIR__.'/tmp/.composer" '
				.	'require '.$_composer_package
				);
			}
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

		echo ' -> Creating test directory';
			if(class_exists('\Illuminate\View\View'))
			{
				rmdir_recursive(__DIR__.'/tmp/lv_hlp');
				@mkdir(__DIR__.'/tmp');
				mkdir(__DIR__.'/tmp/lv_hlp');
				mkdir(__DIR__.'/tmp/lv_hlp/views');
				mkdir(__DIR__.'/tmp/lv_hlp/views/headers');
				mkdir(__DIR__.'/tmp/lv_hlp/views_cache');
				file_put_contents(__DIR__.'/tmp/lv_hlp/views/headers/headerb.blade.php', 'HEADERB-');
				file_put_contents(__DIR__.'/tmp/lv_hlp/views/header.blade.php', 'HEADER-');
				file_put_contents(__DIR__.'/tmp/lv_hlp/views/main.blade.php',
					'@include(\'headers.headerb\')'."\n"
					.'@include(\'header\')'."\n"
					.'MAINSTART {{ $my_variable }} MAINEND'
				);

				echo ' [ OK ]'.PHP_EOL;
			}
			else
				echo ' [SKIP]'.PHP_EOL;

		echo ' -> Including main.php';
			try {
				if(@(include __DIR__.'/../main.php') === false)
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

		$failed=false;

		echo ' -> Testing string helpers'.PHP_EOL;
			echo '  -> lv_hlp_of [LATR]'.PHP_EOL;
			echo '  -> lv_hlp_match_all';
				//echo ' ['.var_export_contains(lv_hlp_match_all('/bar/', 'bar foo bar')->all(), '', true).']';
				if(var_export_contains(
					lv_hlp_match_all('/bar/', 'bar foo bar')->all(),
					"array(0=>'bar',1=>'bar',)"
				))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				//echo ' ['.var_export_contains(lv_hlp_match_all('/f(\w*)/', 'bar fun bar fly')->all(), '', true).']';
				if(var_export_contains(
					lv_hlp_match_all('/f(\w*)/', 'bar fun bar fly')->all(),
					"array(0=>'un',1=>'ly',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> lv_hlp_str [SKIP]'.PHP_EOL;
			echo '  -> lv_str_ascii';
				if(function_exists('transliterator_transliterate'))
				{
					if(lv_str_ascii('ü') === 'u')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> lv_str_inline_markdown';
				if(class_exists('\League\CommonMark\MarkdownConverter') && function_exists('mb_substr'))
				{
					if(trim(lv_str_inline_markdown('**Laravel**')) === '<strong>Laravel</strong>')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(
						trim(lv_str_inline_markdown('Inject: <script>alert("Hello XSS!");</script>', [
							'html_input'=>'strip',
							'allow_unsafe_links'=>false,
						]))
						===
						'Inject: alert(&quot;Hello XSS!&quot;);'
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
			echo '  -> lv_str_is_ascii';
				if(lv_str_is_ascii('Taylor'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_is_ascii('ü'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> lv_str_is_json';
				if(lv_str_is_json('[1,2,3]'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_is_json('{"first": "John", "last": "Doe"}'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_is_json('{first: "John", last: "Doe"}'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> lv_str_is_ulid';
				if(lv_str_is_ulid('01gd6r360bp37zj17nxb55yv40'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_is_ulid('laravel'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> lv_str_is_uuid';
				if(lv_str_is_uuid('a0a2a2d2-0b87-4a18-83f2-2529882be2de'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_is_ulid('laravel'))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> lv_str_markdown';
				if(class_exists('\League\CommonMark\GithubFlavoredMarkdownConverter') && function_exists('mb_substr'))
				{
					if(trim(lv_str_markdown('# Laravel')) === '<h1>Laravel</h1>')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(
						trim(lv_str_markdown('# Taylor <b>Otwell</b>', [
							'html_input'=>'strip'
						]))
						===
						'<h1>Taylor Otwell</h1>'
					)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(
						trim(lv_str_markdown('Inject: <script>alert("Hello XSS!");</script>', [
							'html_input'=>'strip',
							'allow_unsafe_links'=>false,
						]))
						===
						'<p>Inject: alert(&quot;Hello XSS!&quot;);</p>'
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
			echo '  -> lv_str_ordered_uuid: ';
				echo lv_str_ordered_uuid().PHP_EOL;
			echo '  -> lv_str_password [LATR]'.PHP_EOL;
			echo '  -> lv_str_plural';
				if(class_exists('\Doctrine\Inflector\Inflector') && function_exists('mb_substr'))
				{
					if(lv_str_plural('car') === 'cars')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_plural('child') === 'children')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_plural('child', 2) === 'children')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_plural('child', 1) === 'child')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> lv_str_plural_studly';
				if(class_exists('\Doctrine\Inflector\Inflector') && function_exists('mb_substr'))
				{
					if(lv_str_plural_studly('VerifiedHuman') === 'VerifiedHumans')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_plural_studly('UserFeedback') === 'UserFeedback')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_plural_studly('VerifiedHuman', 2) === 'VerifiedHumans')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_plural_studly('VerifiedHuman', 1) === 'VerifiedHuman')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> lv_str_singular';
				if(class_exists('\Doctrine\Inflector\Inflector') && function_exists('mb_substr'))
				{
					if(lv_str_singular('cars') === 'car')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_str_singular('children') === 'child')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> lv_str_slug';
				if(function_exists('mb_substr'))
				{
					if(lv_str_slug('Laravel 5 Framework', '-') === 'laravel-5-framework')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> lv_str_ulid: ';
				echo lv_str_ulid().PHP_EOL;
			echo '  -> lv_str_uuid: ';
				echo lv_str_uuid().PHP_EOL;

		echo ' -> Testing array helpers'.PHP_EOL;
			echo '  -> lv_hlp_collect [LATR]'.PHP_EOL;
			echo '  -> lv_hlp_sort';
				if(var_export_contains(
					lv_hlp_sort(['Desk', 'Table', 'Chair']),
					"array(2=>'Chair',0=>'Desk',1=>'Table',)"
				))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(var_export_contains(
					array_values(lv_hlp_sort([
						['name'=>'Desk'],
						['name'=>'Table'],
						['name'=>'Chair']
					], function(array $value){
						return $value['name'];
					})),
					"array(0=>array('name'=>'Chair',),1=>array('name'=>'Desk',),2=>array('name'=>'Table',),)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> lv_hlp_sort_desc';
				if(var_export_contains(
					lv_hlp_sort_desc(['Desk', 'Table', 'Chair']),
					"array(1=>'Table',0=>'Desk',2=>'Chair',)"
				))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(var_export_contains(
					array_values(lv_hlp_sort_desc([
						['name'=>'Desk'],
						['name'=>'Table'],
						['name'=>'Chair']
					], function(array $value){
						return $value['name'];
					})),
					"array(0=>array('name'=>'Table',),1=>array('name'=>'Desk',),2=>array('name'=>'Chair',),)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> lv_hlp_lazy_collect [LATR]'.PHP_EOL;
			echo '  -> lv_hlp_to_css_styles';
				if(lv_hlp_to_css_styles(['background-color: blue', 'color: blue'=>true]) === 'background-color: blue; color: blue;')
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_hlp_to_css_styles(['background-color: blue', 'color: blue'=>false]) === 'background-color: blue;')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

		echo ' -> Testing lv_hlp_pluralizer';
			if(class_exists('\Doctrine\Inflector\Inflector') && function_exists('mb_substr'))
			{
				echo PHP_EOL;

				echo '  -> plural';
					if(lv_hlp_pluralizer::plural('car') === 'cars')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_pluralizer::plural('child') === 'children')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_pluralizer::plural('child', 2) === 'children')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_pluralizer::plural('child', 1) === 'child')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				echo '  -> lv_str_plural_studly';
					if(lv_hlp_pluralizer::plural_studly('VerifiedHuman') === 'VerifiedHumans')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_pluralizer::plural_studly('UserFeedback') === 'UserFeedback')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_pluralizer::plural_studly('VerifiedHuman', 2) === 'VerifiedHumans')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_pluralizer::plural_studly('VerifiedHuman', 1) === 'VerifiedHuman')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				echo '  -> singular';
					if(lv_hlp_pluralizer::singular('cars') === 'car')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_pluralizer::singular('children') === 'child')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
			}
			else
				echo ' [SKIP]'.PHP_EOL;

		echo ' -> Testing lv_hlp_ingable'.PHP_EOL;
			echo '  -> ascii';
				if(function_exists('transliterator_transliterate'))
				{
					if(lv_hlp_of('ü')->ascii()->to_string() === 'u')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> dd [SKIP]'.PHP_EOL;
			echo '  -> dump [SKIP]'.PHP_EOL;
			echo '  -> explode';
				//echo ' ['.var_export_contains(lv_hlp_of('foo bar baz')->explode(' ')->all(), '', true).']';
				if(var_export_contains(
					lv_hlp_of('foo bar baz')->explode(' ')->all(),
					"array(0=>'foo',1=>'bar',2=>'baz',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> inline_markdown';
				if(class_exists('\League\CommonMark\GithubFlavoredMarkdownConverter') && function_exists('mb_substr'))
				{
					if(trim(lv_hlp_of('**Laravel**')->inline_markdown()->to_string()) === '<strong>Laravel</strong>')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> is_ascii';
				if(lv_hlp_of('Taylor')->is_ascii())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_hlp_of('ü')->is_ascii())
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> is_json';
				if(lv_hlp_of('[1,2,3]')->is_json())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_hlp_of('{"first": "John", "last": "Doe"}')->is_json())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_hlp_of('{first: "John", last: "Doe"}')->is_json())
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> is_ulid';
				if(lv_hlp_of('01gd6r360bp37zj17nxb55yv40')->is_ulid())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_hlp_of('Taylor')->is_ulid())
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> is_uuid';
				if(lv_hlp_of('5ace9ab9-e9cf-4ec6-a19d-5881212a452c')->is_uuid())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_hlp_of('Taylor')->is_uuid())
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			echo '  -> macro [SKIP]'.PHP_EOL;
			echo '  -> markdown';
				if(class_exists('\League\CommonMark\GithubFlavoredMarkdownConverter') && function_exists('mb_substr'))
				{
					if(trim(lv_hlp_of('# Laravel')->markdown()->to_string()) === '<h1>Laravel</h1>')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(trim(lv_hlp_of('# Taylor <b>Otwell</b>')->markdown([
						'html_input'=>'strip'
					])->to_string()) === '<h1>Taylor Otwell</h1>')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> match_all';
				//echo ' ['.var_export_contains(lv_hlp_of('bar foo bar')->match_all('/bar/')->all(), '', true).']';
				if(var_export_contains(
					lv_hlp_of('bar foo bar')->match_all('/bar/')->all(),
					"array(0=>'bar',1=>'bar',)"
				))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				//echo ' ['.var_export_contains(lv_hlp_of('bar fun bar fly')->match_all('/f(\w*)/')->all(), '', true).']';
				if(var_export_contains(
					lv_hlp_of('bar fun bar fly')->match_all('/f(\w*)/')->all(),
					"array(0=>'un',1=>'ly',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> plural';
				if(class_exists('\Doctrine\Inflector\Inflector') && function_exists('mb_substr'))
				{
					if(lv_hlp_of('car')->plural()->to_string() === 'cars')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_of('child')->plural()->to_string() === 'children')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_of('child')->plural(2)->to_string() === 'children')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_of('child')->plural(1)->to_string() === 'child')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> plural_studly';
				if(class_exists('\Doctrine\Inflector\Inflector') && function_exists('mb_substr'))
				{
					if(lv_hlp_of('VerifiedHuman')->plural_studly()->to_string() === 'VerifiedHumans')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_of('UserFeedback')->plural_studly()->to_string() === 'UserFeedback')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_of('VerifiedHuman')->plural_studly(2)->to_string() === 'VerifiedHumans')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_of('VerifiedHuman')->plural_studly(1)->to_string() === 'VerifiedHuman')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> singular';
				if(class_exists('\Doctrine\Inflector\Inflector') && function_exists('mb_substr'))
				{
					if(lv_hlp_of('cars')->singular()->to_string() === 'car')
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_of('children')->singular()->to_string() === 'child')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> scan';
				//echo ' ['.var_export_contains(lv_hlp_of('filename.jpg')->scan('%[^.].%s')->all(), '', true).']';
				if(var_export_contains(
					lv_hlp_of('filename.jpg')->scan('%[^.].%s')->all(),
					"array(0=>'filename',1=>'jpg',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> slug';
				if(function_exists('mb_substr'))
				{
					if(lv_hlp_of('Laravel Framework')->slug('-')->to_string() === 'laravel-framework')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> split';
				if(function_exists('mb_substr'))
				{
					//echo ' ['.var_export_contains(lv_hlp_of('one, two, three')->split('/[\s,]+/')->all(), '', true).']';
					if(var_export_contains(
						lv_hlp_of('one, two, three')->split('/[\s,]+/')->all(),
						"array(0=>'one',1=>'two',2=>'three',)"
					))
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> to_date';
				if(class_exists('\Carbon\Carbon'))
				{
					try {
						if(lv_hlp_of('12-31-2001')->to_date('m-d-Y')->toDateString() === '2001-12-31')
							echo ' [ OK ]'.PHP_EOL;
						else
						{
							echo ' [FAIL]'.PHP_EOL;
							$failed=true;
						}
					} catch(Throwable $error) {
						echo ' [FAIL] (caught)'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> transliterate [SKIP]'.PHP_EOL;
			echo '  -> ucsplit';
				//echo ' ['.var_export_contains(lv_hlp_of('Foo Bar')->ucsplit()->all(), '', true).']';
				if(var_export_contains(
					lv_hlp_of('Foo Bar')->ucsplit()->all(),
					"array(0=>'Foo',1=>'Bar',)"
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			echo '  -> when_is_ascii/title';
				if(function_exists('mb_substr'))
				{
					if(lv_hlp_of('laravel')->when_is_ascii(function(lv_hlp_ingable $string){
						return $string->title();
					})->to_string() === 'Laravel')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_is_ulid/substr';
				if(function_exists('mb_substr'))
				{
					if(lv_hlp_of('01gd6r360bp37zj17nxb55yv40')->when_is_ulid(function(lv_hlp_ingable $string){
						return $string->substr(0, 8);
					})->to_string() === '01gd6r36')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;
			echo '  -> when_is_uuid/substr';
				if(function_exists('mb_substr'))
				{
					if(lv_hlp_of('a0a2a2d2-0b87-4a18-83f2-2529882be2de')->when_is_uuid(function(lv_hlp_ingable $string){
						return $string->substr(0, 8);
					})->to_string() === 'a0a2a2d2')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				}
				else
					echo ' [SKIP]'.PHP_EOL;

		echo ' -> Testing lv_hlp_collection'.PHP_EOL;
			echo '  -> dd [SKIP]'.PHP_EOL;
			echo '  -> dump [SKIP]'.PHP_EOL;
			echo '  -> ensure';
				try {
					//echo ' ['.var_export_contains(lv_hlp_collect([1, 2, 3])->ensure('int'), '', true, function($i){ return str_replace('\lv_hlp_collection', 'lv_hlp_collection', $i); }).']';
					if(var_export_contains(
						lv_hlp_collect([1, 2, 3])->ensure('int'),
						"lv_hlp_collection::__set_state(array('items'=>array(0=>1,1=>2,2=>3,),'escape_when_casting_to_string'=>false,))",
						false, function($i){ return str_replace('\lv_hlp_collection', 'lv_hlp_collection', $i); }
					))
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
				} catch(lv_hlp_exception $error) {
					echo ' [FAIL]';
					$failed=true;
				}
				try {
					lv_hlp_collect([1, 2, 3])->ensure('float');
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				} catch(lv_hlp_exception $error) {
					echo ' [ OK ]'.PHP_EOL;
				}
			echo '  -> macro';
				lv_hlp_collection::macro('to_upper', function(){
					return $this->map(function(string $value){
						return strtoupper($value);
					});
				});
				$collection=lv_hlp_collect(['first', 'second']);
				//echo ' ['.var_export_contains($collection->to_upper()->__toString(), '', true).']';
				if($collection->to_upper()->__toString() === '["FIRST","SECOND"]')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

		echo ' -> Testing lv_hlp_lazy_collection'.PHP_EOL;
			echo '  -> chunk_while';
				$collection=lv_hlp_lazy_collect(str_split('AABBCCCD'));
				$chunks=$collection->chunk_while(function(string $value, int $key, lv_hlp_collection $chunk){
					return ($value === $chunk->last());
				});
				//echo ' ['.var_export_contains($chunks->all(), '', true, function($i){ return str_replace('\lv_hlp_lazy_collection', 'lv_hlp_lazy_collection', $i); }).']';
				if(var_export_contains(
					$chunks->all(),
					"array(0=>lv_hlp_lazy_collection::__set_state(array('source'=>array(0=>'A',1=>'A',),'escape_when_casting_to_string'=>false,)),1=>lv_hlp_lazy_collection::__set_state(array('source'=>array(2=>'B',3=>'B',),'escape_when_casting_to_string'=>false,)),2=>lv_hlp_lazy_collection::__set_state(array('source'=>array(4=>'C',5=>'C',6=>'C',),'escape_when_casting_to_string'=>false,)),3=>lv_hlp_lazy_collection::__set_state(array('source'=>array(7=>'D',),'escape_when_casting_to_string'=>false,)),)",
					false, function($i){ return str_replace('\lv_hlp_lazy_collection', 'lv_hlp_lazy_collection', $i); }
				))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}

		echo ' -> Testing encrypter';
			if(extension_loaded('openssl'))
			{
				echo PHP_EOL;
				echo '  -> lv_hlp_encrypter_generate_key';
					$lv_hlp_encrypter_key=lv_hlp_encrypter_generate_key();
					if(strlen($lv_hlp_encrypter_key) === 44)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				echo '  -> lv_hlp_encrypter_key';
					if(lv_hlp_encrypter_key(false) === null)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}
					if(lv_hlp_encrypter_key($lv_hlp_encrypter_key) === null)
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
					else
						echo ' [ OK ]'.PHP_EOL;
				echo '  -> lv_hlp_encrypt/lv_hlp_decrypt';
					if(lv_hlp_decrypt(lv_hlp_encrypt('TO BE ENCRYPTED')) === 'TO BE ENCRYPTED')
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
			}
			else
				echo ' [SKIP]'.PHP_EOL;

		echo ' -> Testing view';
			if(class_exists('\Illuminate\View\View'))
			{
				$view_output='HEADERB-HEADER-MAINSTART Hello world MAINEND';
				try {
					$rendered_view=lv_hlp_view
					::	set_cache_path(__DIR__.'/tmp/lv_hlp/views_cache')
					::	set_view_path(__DIR__.'/tmp/lv_hlp/views')
					::	view('main', [
						'my_variable'=>'Hello world'
					]);
					//echo ' ('.$rendered_view.')';
					if($rendered_view === $view_output)
						echo ' [ OK ]';
					else
					{
						echo ' [FAIL]';
						$failed=true;
					}

					$rendered_view=lv_hlp_view
					::	register_resolver('blade', function(){
							return new Illuminate\View\Engines\CompilerEngine(
								new Illuminate\View\Compilers\BladeCompiler(
									new Illuminate\Filesystem\Filesystem(),
									__DIR__.'/tmp/lv_hlp/views_cache'
								)
							);
					})
					::	set_cache_path(__DIR__.'/tmp/lv_hlp/views_cache')
					::	set_view_path(__DIR__.'/tmp/lv_hlp/views')
					::	view('main', [
						'my_variable'=>'Hello world'
					]);
					//echo ' ('.$rendered_view.')';
					if($rendered_view === $view_output)
						echo ' [ OK ]'.PHP_EOL;
					else
					{
						echo ' [FAIL]'.PHP_EOL;
						$failed=true;
					}
				} catch(Throwable $error) {
					echo ' [FAIL]'.PHP_EOL;
					echo $error->getMessage().PHP_EOL;
					$failed=true;
				}
			}
			else
				echo ' [SKIP]'.PHP_EOL;
	}
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

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
		{
			echo ' -> Including '.$library;
				if(file_exists(__DIR__.'/../lib/'.$library))
				{
					if(@(include __DIR__.'/../lib/'.$library) === false)
					{
						echo ' [FAIL]'.PHP_EOL;
						exit(1);
					}
				}
				else if(file_exists(__DIR__.'/../../../lib/'.$library))
				{
					if(@(include __DIR__.'/../../../lib/'.$library) === false)
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

		echo ' -> Mocking functions';
			class Exception extends \Exception {}
			class lv_str_ingable extends \lv_str_ingable {}
			trait t_lv_macroable { use \t_lv_macroable; }
			trait lv_arr_enumerates_values { use \lv_arr_enumerates_values; }
			class lv_arr_collection extends \lv_arr_collection {}
			interface ArrayAccess extends \ArrayAccess {}
			interface lv_arr_enumerable extends \lv_arr_enumerable {}
			class lv_arr_lazy_collection extends \lv_arr_lazy_collection {}
			function random_int()
			{
				return 1;
			}
		echo ' [ OK ]'.PHP_EOL;

		echo ' -> Including main.php';
			try {
				if(!_include_tested_library(
					__NAMESPACE__,
					__DIR__.'/../main.php'
				)){
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

		echo ' -> Testing lv_str_password';
			$password=lv_str_password(8);
			if(
				(strpos($password, 'b') !== false) &&
				(strpos($password, '1') !== false) &&
				(strpos($password, '!') !== false)
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

		if(!is_file(__DIR__.'/tmp/.composer/vendor/autoload.php'))
		{
			echo PHP_EOL;
			echo 'Note: run'.PHP_EOL;
			echo ' '.$argv[0].' composer'.PHP_EOL;
			echo 'to install the packages'.PHP_EOL;
		}

		if($failed)
			exit(1);
	}
?>