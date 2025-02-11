<?php
	/*
	 * rss_generator.php library test
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

	echo ' -> Testing library';
		$expected_output=''
		.	'<?xml version="1.0" encoding="UTF-8" ?>'
		.	'<?xml-stylesheet type="text/xsl" href="https://my.website/assets/rss.xsl" ?>'
		.	'<rss version="2.0">'
		.		'<channel>'
		.			'<link>https://my.website</link>'
		.			'<title>Channel title</title>'
		.			'<language>en-us</language>'
		.			'<description>Channel description</description>'
		.			'<emptyTag />'
		.			'<item>'
		.				'<link>https://my.website/articles/fourth-article</link>'
		.				'<title>Fourth article</title>'
		.				'<pubDate>Thu, 27 Apr 2006</pubDate>'
		.				'<emptyTag />'
		.				'<description>&lt;h1&gt;Fourth article content&lt;/h1&gt;</description>'
		.			'</item>'
		.			'<item>'
		.				'<link>https://my.website/articles/third-article</link>'
		.				'<title>Third article</title>'
		.				'<pubDate>Thu, 27 Apr 2006</pubDate>'
		.				'<description>&lt;h1&gt;Third article content&lt;/h1&gt;</description>'
		.				'<emptyTag />'
		.			'</item>'
		.			'<item>'
		.				'<link>https://my.website/articles/second-article</link>'
		.				'<title>Second article</title>'
		.				'<pubDate>Thu, 27 Apr 2006</pubDate>'
		.				'<emptyTag />'
		.				'<description>&lt;h1&gt;Second article content&lt;/h1&gt;</description>'
		.			'</item>'
		.			'<item>'
		.				'<link>https://my.website/articles/first-article</link>'
		.				'<title>First article</title>'
		.				'<pubDate>Thu, 28 Apr 2006</pubDate>'
		.				'<description>&lt;h1&gt;First article content&lt;/h1&gt;</description>'
		.				'<emptyTag />'
		.			'</item>'
		.		'</channel>'
		.	'</rss>';
		$rss=new rss_generator([
			'link'=>'https://my.website',
			'item_link_prefix'=>'/articles',
			'stylesheet'=>'/assets/rss.xsl',
			//'stylesheet_no_link'=>true,
			'channel_tags'=>[
				'title'=>'Channel title',
				'language'=>'en-us',
				'description'=>'Channel description',
				'emptyTag'=>''
			]
		]);
		$rss
		->	add(
				new rss_generator_item([
					'title'=>'Fourth article',
					'link'=>'/fourth-article', // https://my.website/articles/fourth-article
					'pubDate'=>'Thu, 27 Apr 2006',
					'description'=>function()
					{
						return '<h1>Fourth article content</h1>';
					},
					'emptyTag'=>''
				])
			)
		->	add(
				new rss_generator_item([
					'title'=>'Third article',
					'link'=>'/third-article', // https://my.website/articles/third-article
					'pubDate'=>'Thu, 27 Apr 2006',
					'description'=>'<h1>Third article content</h1>',
					'emptyTag'=>''
				])
			)
		->	add(
				(new rss_generator_item())
				->	title('Second article')
				->	link('/second-article') // https://my.website/articles/second-article
				->	pubDate('Thu, 27 Apr 2006')
				->	description(function(){
						return '<h1>Second article content</h1>';
					})
				->	emptyTag()
			)
		->	add(
				(new rss_generator_item())
				->	title('First article')
				->	link('/first-article') // https://my.website/articles/first-article
				->	pubDate('Thu, 28 Apr 2006')
				->	description('<h1>First article content</h1>')
				->	emptyTag()
			);
		//echo ' ('.var_export($rss->get(), true).')';
		if($rss->get() === $expected_output)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		ob_start();
		$rss->echo();
		$output=ob_get_contents();
		ob_end_clean();
		//echo ' ('.$output.')';
		if($output === $expected_output)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>