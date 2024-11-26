<?php
	/*
	 * csv2table.php library test
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

	echo ' -> Removing temporary files';
		@mkdir(__DIR__.'/tmp');
		@mkdir(__DIR__.'/tmp/csv2table');
		foreach([
			'data.csv',
			'data.html'
		] as $file)
			@unlink(__DIR__.'/tmp/csv2table/'.$file);
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Creating test directory';
		file_put_contents(
			__DIR__.'/tmp/csv2table/data.csv',
			'"AA","BB","CC","DD","EE","FF","GG","HH","II","JJ"'."\r\n",
			FILE_APPEND
		);
		$a=0;
		for($y=0; $y<10; ++$y)
		{
			$b=0;

			for($x=0; $x<10; ++$x)
			{
				file_put_contents(__DIR__.'/tmp/csv2table/data.csv', '"'.$a.$b.'"', FILE_APPEND);

				if($b < 9)
					file_put_contents(__DIR__.'/tmp/csv2table/data.csv', ',', FILE_APPEND);

				++$b;
			}

			file_put_contents(__DIR__.'/tmp/csv2table/data.csv', "\r\n", FILE_APPEND);
			++$a;
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing library';
		file_put_contents(
			__DIR__.'/tmp/csv2table/data.html',
			csv2table([
				'input_file'=>__DIR__.'/tmp/csv2table/data.csv',
				'table_header'=>true
			])
		);
		//echo ' ('.md5(file_get_contents(__DIR__.'/tmp/csv2table/data.html')).')';
		if(md5(file_get_contents(__DIR__.'/tmp/csv2table/data.html')) === '8e22f8745fa0936074caf4301f91b1be')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>