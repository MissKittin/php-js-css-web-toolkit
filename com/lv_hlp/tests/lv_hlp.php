<?php
	/*
	 * Tests only new functions and modified methods
	 */

	foreach(['var_export_contains.php'] as $library)
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

	echo ' -> Including lv_hlp.php';
		try {
			if(@(include __DIR__.'/../lv_hlp.php') === false)
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

	if(
		isset($argv[1]) &&
		($argv[1] === 'composer') &&
		(!file_exists(__DIR__.'/tmp/.composer/vendor/league/commonmark'))
	){
		echo ' -> Installing league/commonmark'.PHP_EOL;

		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/.composer');

		if(file_exists(__DIR__.'/../bin/composer.phar'))
			system(PHP_BINARY.' '.__DIR__.'/../bin/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require league/commonmark');
		else if(file_exists(__DIR__.'/../../../bin/composer.phar'))
			system(PHP_BINARY.' '.__DIR__.'/../../../bin/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require league/commonmark');
		else if(file_exists(__DIR__.'/tmp/.composer/composer.phar'))
			system(PHP_BINARY.' '.__DIR__.'/tmp/.composer/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require league/commonmark');
		else if(file_exists(__DIR__.'/../bin/get-composer.php'))
		{
			system(PHP_BINARY.' '.__DIR__.'/../bin/get-composer.php '.__DIR__.'/tmp/.composer');
			system(PHP_BINARY.' '.__DIR__.'/tmp/.composer/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require league/commonmark');
		}
		else if(file_exists(__DIR__.'/../../../bin/get-composer.php'))
		{
			system(PHP_BINARY.' '.__DIR__.'/../../../bin/get-composer.php '.__DIR__.'/tmp/.composer');
			system(PHP_BINARY.' '.__DIR__.'/tmp/.composer/composer.phar --no-cache --working-dir='.__DIR__.'/tmp/.composer require league/commonmark');
		}
		else
		{
			echo 'Error: get-composer.php tool not found'.PHP_EOL;
			exit(1);
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

	$failed=false;

	echo ' -> Testing string helpers'.PHP_EOL;
		echo '  -> lv_str_ascii';
			if(extension_loaded('intl'))
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
		echo '  -> lv_str_contains';
			if(extension_loaded('mbstring'))
			{
				if(lv_str_contains('This is my name', 'my'))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_contains('This is my name', 'xdd'))
				{
					echo ' [FAIL]';
					$failed=true;
				}
				else
					echo ' [ OK ]';
				if(lv_str_contains('This is my name', ['my', 'foo']))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			}
			else
				echo ' [SKIP]'.PHP_EOL;
		echo '  -> lv_str_contains_all';
			if(extension_loaded('mbstring'))
			{
				if(lv_str_contains_all('This is my name', ['my', 'name']))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				if(lv_str_contains_all('This is my name', ['my', 'nameE']))
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			}
			else
				echo ' [SKIP]'.PHP_EOL;
		echo '  -> lv_str_ends_with';
			if(lv_str_ends_with('This is my name', 'name'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_ends_with('This is my name', ['name', 'foo']))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_ends_with('This is my name', ['this', 'foo']))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> lv_str_inline_markdown';
			if(class_exists('League\CommonMark\MarkdownConverter'))
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
			if(class_exists('League\CommonMark\GithubFlavoredMarkdownConverter'))
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
		echo '  -> lv_str_password: ';
			echo lv_str_password(8).PHP_EOL;
		echo '  -> lv_str_remove';
			if(lv_str_remove('e', 'Peter Piper picked a peck of pickled peppers.') === 'Ptr Pipr pickd a pck of pickld ppprs.')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lv_str_replace';
			if(lv_str_replace('10.x', '11.x', 'Laravel 10.x') === 'Laravel 11.x')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lv_str_replace_array';
			if(lv_str_replace_array('?', ['8:30', '9:00'], 'The event will take place between ? and ?') === 'The event will take place between 8:30 and 9:00')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lv_str_replace_end';
			if(lv_str_replace_end('World', 'Laravel', 'Hello World') === 'Hello Laravel')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_replace_end('Hello', 'Laravel', 'Hello World') === 'Hello World')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lv_str_replace_start';
			if(lv_str_replace_start('Hello', 'Laravel', 'Hello World') === 'Laravel World')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_replace_start('World', 'Laravel', 'Hello World') === 'Hello World')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lv_str_reverse';
			if(extension_loaded('mbstring'))
			{
				if(lv_str_reverse('Hello World') === 'dlroW olleH')
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
			if(extension_loaded('mbstring'))
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
		echo '  -> lv_str_starts_with';
			if(lv_str_starts_with('This is my name', 'This'))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_str_starts_with('This is my name', 'That'))
			{
				echo ' [FAIL]';
				$failed=true;
			}
			else
				echo ' [ OK ]';
			if(lv_str_starts_with('This is my name', ['This', 'That', 'There']))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lv_str_ulid: ';
			echo lv_str_ulid().PHP_EOL;
		echo '  -> lv_str_uuid: ';
			echo lv_str_uuid().PHP_EOL;

	echo ' -> Testing array helpers'.PHP_EOL;
		echo '  -> lv_hlp_collect [LATR]'.PHP_EOL;
		echo '  -> lv_hlp_is_assoc';
			if(lv_hlp_is_assoc(['product'=>['name'=>'Desk', 'price'=>100]]))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_hlp_is_assoc([1, 2, 3]))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> lv_hlp_is_list';
			if(lv_hlp_is_list(['foo', 'bar', 'baz']))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			if(lv_hlp_is_list(['product'=>['name'=>'Desk', 'price'=>100]]))
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
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
		echo '  -> lv_hlp_sort_recursive';
			if(var_export_contains(
				lv_arr_sort_recursive([
					['Roman', 'Taylor', 'Li'],
					['PHP', 'Ruby', 'JavaScript'],
					['one'=>1, 'two'=>2, 'three'=>3]
				]),
				"array(0=>array(0=>'JavaScript',1=>'PHP',2=>'Ruby',),1=>array('one'=>1,'three'=>3,'two'=>2,),2=>array(0=>'Li',1=>'Roman',2=>'Taylor',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> lv_hlp_sort_recursive_desc';
			if(var_export_contains(
				lv_arr_sort_recursive_desc([
					['Roman', 'Taylor', 'Li'],
					['PHP', 'Ruby', 'JavaScript'],
					['one'=>1, 'two'=>2, 'three'=>3]
				]),
				"array(0=>array(0=>'Taylor',1=>'Roman',2=>'Li',),1=>array(0=>'Ruby',1=>'PHP',2=>'JavaScript',),2=>array('two'=>2,'three'=>3,'one'=>1,),)"
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

	echo ' -> Testing lv_hlp_collection'.PHP_EOL;
		echo '  -> dd [SKIP]'.PHP_EOL;
		echo '  -> dump [SKIP]'.PHP_EOL;
		echo '  -> ensure';
			try {
				//echo ' ['.var_export_contains(lv_hlp_collect([1, 2, 3])->ensure('int'), '', true).']';
				if(var_export_contains(
					lv_hlp_collect([1, 2, 3])->ensure('int'),
					"lv_hlp_collection::__set_state(array('items'=>array(0=>1,1=>2,2=>3,),'escape_when_casting_to_string'=>false,))"
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
			//echo ' ['.var_export_contains($chunks->all(), '', true).']';
			if(var_export_contains(
				$chunks->all(),
				"array(0=>lv_hlp_lazy_collection::__set_state(array('source'=>array(0=>'A',1=>'A',),'escape_when_casting_to_string'=>false,)),1=>lv_hlp_lazy_collection::__set_state(array('source'=>array(2=>'B',3=>'B',),'escape_when_casting_to_string'=>false,)),2=>lv_hlp_lazy_collection::__set_state(array('source'=>array(4=>'C',5=>'C',6=>'C',),'escape_when_casting_to_string'=>false,)),3=>lv_hlp_lazy_collection::__set_state(array('source'=>array(7=>'D',),'escape_when_casting_to_string'=>false,)),)"
			))
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
?>