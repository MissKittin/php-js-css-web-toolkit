<?php
	/*
	 * observer.php library test
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

	echo ' -> Definig classes';
		$GLOBALS['logger']=0;
		$GLOBALS['logger_content']='';
		$GLOBALS['rss']=0;
		$GLOBALS['rss_content']='';
		$GLOBALS['article']=0;
		$GLOBALS['article_content']='';

		class logger implements observer_interface
		{
			public function update($observer)
			{
				++$GLOBALS[__CLASS__];
				$GLOBALS[__CLASS__.'_content'].=$observer->get_content();
			}
		}
		class rss implements observer_interface
		{
			public function update($observer)
			{
				++$GLOBALS[__CLASS__];
				$GLOBALS[__CLASS__.'_content'].=$observer->get_content();
			}
		}
		class article implements observer_interface
		{
			public function update($observer)
			{
				++$GLOBALS[__CLASS__];
				$GLOBALS[__CLASS__.'_content'].=$observer->get_content();
			}
		}

		class my_observer extends observer
		{
			private $content;

			public function add($content)
			{
				$this->content=$content;
				$this->notify();
			}
			public function get_content()
			{
				return $this->content;
			}
		}
	echo ' [ OK ]'.PHP_EOL;

	$logger_object=new logger();
	$my_observer_object=new my_observer();
	$failed=false;

	echo ' -> Testing library';
		$my_observer_object
			->attach(new article())
			->attach(new rss())
			->attach($logger_object);
		$my_observer_object->add('New article content');
		$my_observer_object->detach($logger_object);
		$my_observer_object->add('Second article content');

		foreach(['rss', 'article'] as $variable)
		{
			if($GLOBALS[$variable] !== 2)
				$failed=true;

			if($GLOBALS[$variable.'_content'] !== 'New article contentSecond article content')
				$failed=true;
		}

		if($GLOBALS['logger'] !== 1)
			$failed=true;

		if($GLOBALS['logger_content'] !== 'New article content')
			$failed=true;

	if($failed)
	{
		echo ' [FAIL]'.PHP_EOL;
		exit(1);
	}
	else
		echo ' [ OK ]'.PHP_EOL;
?>