<?php
	/*
	 * assets_compiler.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  rmdir_recursive.php library is required
	 */

	echo ' -> Including rmdir_recursive.php';
		if(is_file(__DIR__.'/../lib/rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../lib/rmdir_recursive.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../rmdir_recursive.php'))
		{
			if(@(include __DIR__.'/../rmdir_recursive.php') === false)
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

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/assets_compiler');
		foreach(['assets', 'public'] as $file)
			rmdir_recursive(__DIR__.'/tmp/assets_compiler/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		mkdir(__DIR__.'/tmp/assets_compiler/assets');
		mkdir(__DIR__.'/tmp/assets_compiler/public');

		mkdir(__DIR__.'/tmp/assets_compiler/assets/preprocessed.css');
		file_put_contents(__DIR__.'/tmp/assets_compiler/assets/preprocessed.css/body.css', 'body { color: #000; }');
		file_put_contents(__DIR__.'/tmp/assets_compiler/assets/preprocessed.css/div.css', 'div { color: #000; }');
		file_put_contents(__DIR__.'/tmp/assets_compiler/assets/preprocessed.css/main.php', '<?php
			include __DIR__."/body.css";
			echo "span { color: #fff; }";
			include __DIR__."/div.css";
		?>');

		mkdir(__DIR__.'/tmp/assets_compiler/assets/concatenated.css');
		file_put_contents(__DIR__.'/tmp/assets_compiler/assets/concatenated.css/body.css', 'body { color: #000; }');
		file_put_contents(__DIR__.'/tmp/assets_compiler/assets/concatenated.css/div.css', 'div { color: #000; }');

		file_put_contents(__DIR__.'/tmp/assets_compiler/assets/single.css', 'body { color: #000; }');
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing preprocessed asset';
		if(assets_compiler(
			__DIR__.'/tmp/assets_compiler/assets/preprocessed.css',
			__DIR__.'/tmp/assets_compiler/public/preprocessed.css'
		) === 0)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(
			file_get_contents(__DIR__.'/tmp/assets_compiler/assets/preprocessed.css/body.css')
			.'span { color: #fff; }'
			.file_get_contents(__DIR__.'/tmp/assets_compiler/assets/preprocessed.css/div.css')
			===
			file_get_contents(__DIR__.'/tmp/assets_compiler/public/preprocessed.css')
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing concatenated asset';
		if(!empty(assets_compiler(
			__DIR__.'/tmp/assets_compiler/assets/concatenated.css',
			__DIR__.'/tmp/assets_compiler/public/concatenated.css'
		)))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(
			file_get_contents(__DIR__.'/tmp/assets_compiler/assets/concatenated.css/body.css')
			.file_get_contents(__DIR__.'/tmp/assets_compiler/assets/concatenated.css/div.css')
			===
			file_get_contents(__DIR__.'/tmp/assets_compiler/public/concatenated.css')
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing single file';
		if(assets_compiler(
			__DIR__.'/tmp/assets_compiler/assets/single.css',
			__DIR__.'/tmp/assets_compiler/public/single.css'
		) === 0)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(
			file_get_contents(__DIR__.'/tmp/assets_compiler/assets/single.css')
			===
			file_get_contents(__DIR__.'/tmp/assets_compiler/public/single.css')
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);

?>