<?php
	/*
	 * blog_page_slicer.php library test
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

	foreach(['blog_page_slicer', 'blog_page_slicer_old'] as $function)
	{
		echo ' -> Testing '.$function.PHP_EOL;

		for($page=1; $page<=3; ++$page)
		{
			echo '  -> Page '.$page;

			$result=var_export($function([
				'art01',
				'art02',
				'art03',
				'art04',
				'art05',
				'art06',
				'art07',
				'art08',
				'art09',
				'art10',
				'art11'
			], $page, 5), true);

			switch($page)
			{
				case 1:
					$expected_result="array(0=>'art01',1=>'art02',2=>'art03',3=>'art04',4=>'art05',)";
				break;
				case 2:
					$expected_result="array(0=>'art06',1=>'art07',2=>'art08',3=>'art09',4=>'art10',)";
				break;
				case 3:
					$expected_result="array(0=>'art11',)";
			}

			if(str_replace(["\n", ' '], '', $result) === $expected_result)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
	}
?>