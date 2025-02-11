<?php
	/*
	 * sitemap_generator.php library test
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
		.	'<?xml-stylesheet type="text/xsl" href="https://my.website/assets/sitemap.xsl" ?>'
		.	'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
		.		'<url>'
		.			'<loc>https://my.website/loc-a</loc>'
		.			'<lastmod>lastmod_date</lastmod>'
		.			'<changefreq>monthly</changefreq>'
		.			'<priority>0.5</priority>'
		.		'</url>'
		.		'<url>'
		.			'<loc>https://my.website/loc-b</loc>'
		.			'<lastmod>lastmod_date</lastmod>'
		.			'<changefreq>daily</changefreq>'
		.			'<priority>0.5</priority>'
		.		'</url>'
		.	'</urlset>';
		$sitemap=new sitemap_generator([
			'url'=>'https://my.website',
			'stylesheet'=>'/assets/sitemap.xsl',
			//'stylesheet_no_url'=>true,
			'no_newline'=>true,
			'default_tags'=>[
				'lastmod'=>'lastmod_date',
				'changefreq'=>'monthly',
				'priority'=>'0.5'
			]
		]);
		$sitemap
		->	add('/loc-a')
		->	add('/loc-b', ['changefreq'=>'daily']);
		if($sitemap->get() === $expected_output)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		ob_start();
		$sitemap->echo();
		$output=ob_get_contents();
		ob_end_clean();
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