<?php
	function robots()
	{
		header('Content-type: text/plain');

		$proto='http';
		if(isset($_SERVER['HTTPS']))
			$proto='https';

		echo 'Sitemap: '.$proto.'://'.$_SERVER['HTTP_HOST'].'/sitemap.xml'."\n";
	}
	function sitemap()
	{
		header('Content-type: text/xml');

		if(file_exists('./var/lib/sitemap.xml'))
		{
			readfile('./var/lib/sitemap.xml');
			exit();
		}

		$proto='http';
		if(isset($_SERVER['HTTPS']))
			$proto='https';

		include './lib/sitemap_generator.php';
		$sitemap=new sitemap_generator([
			'url'=>$proto.'://'.$_SERVER['HTTP_HOST'],
			'default_tags'=>[
				'lastmod'=>date('Y-m-d'),
				'changefreq'=>'monthly',
				'priority'=>'0.5'
			]
		]);

		$xml=$sitemap
			->add('/about')
			->add('/check-date')
			->add('/database-test')
			->add('/obsfucate-html')
			->add('/login-library-test')
			->add('/preprocessing-test')
			->get();

		@mkdir('./var');
		@mkdir('./var/lib');
		file_put_contents('./var/lib/sitemap.xml', $xml);

		echo $xml;
	}
?>