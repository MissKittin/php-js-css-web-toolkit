<?php
	/*
	 * sitemap_generator.php library test
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

	echo ' -> Testing library';
		$sitemap=new sitemap_generator([
			'url'=>'https://my.website',
			'default_tags'=>[
				'lastmod'=>'lastmod_date',
				'changefreq'=>'monthly',
				'priority'=>'0.5'
			]
		]);
		$sitemap->add('/loc-a');
		$sitemap->add('/loc-b', ['changefreq'=>'daily']);
		if(str_replace("\n", '', $sitemap->get()) === '<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://my.website/loc-a</loc><lastmod>lastmod_date</lastmod><changefreq>monthly</changefreq><priority>0.5</priority></url><url><loc>https://my.website/loc-b</loc><lastmod>lastmod_date</lastmod><changefreq>daily</changefreq><priority>0.5</priority></url></urlset>')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
?>