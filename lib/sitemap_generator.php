<?php
	class sitemap_generator
	{
		/*
		 * sitemap.xml builder
		 *
		 * Example usage:
			$sitemap=new sitemap_generator([
				'url'=>'https://my.website',
				'default_tags'=>[
					'lastmod'=>date('Y-m-d'),
					'changefreq'=>'monthly',
					'priority'=>'0.5'
				]
			]);

			$sitemap->add('/loc-a');
			$sitemap->add('/loc-b', ['changefreq'=>'daily']);

			echo $sitemap->get();
		 */

		protected $url='';
		protected $default_tags=[];
		protected $content=[];

		public function __construct(array $params)
		{
			foreach(['url', 'default_tags'] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];
		}

		public function add(string $loc, array $tags=[])
		{
			$this->content[$loc]=array_merge($this->default_tags, $tags);
			return $this;
		}
		public function get()
		{
			$sitemap='<?xml version="1.0" encoding="UTF-8" ?>'."\n"
				.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

			foreach($this->content as $loc=>$tags)
			{
				$sitemap.='<url>'."\n";
					$sitemap.='<loc>'.$this->url.$loc.'</loc>'."\n";
					foreach($tags as $tag_name=>$tag_value)
						$sitemap.='<'.$tag_name.'>'.$tag_value.'</'.$tag_name.'>'."\n";
				$sitemap.='</url>'."\n";
			}

			$sitemap.='</urlset>';

			return $sitemap;
		}
	}
?>