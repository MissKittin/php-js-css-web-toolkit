<?php
	class sitemap_generator_exception extends Exception {}
	class sitemap_generator
	{
		/*
		 * sitemap.xml builder
		 *
		 * Note:
		 *  throws an sitemap_generator_exception on error
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

			echo $sitemap
			->	add('/loc-a');
			->	add('/loc-b', ['changefreq'=>'daily'])
			->	get();
		 */

		protected $url='';
		protected $default_tags=[];
		protected $content=[];

		public function __construct(array $params)
		{
			foreach([
				'url'=>'string',
				'default_tags'=>'array'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new sitemap_generator_exception('The input array parameter '.$param.' is not a '.$param_type);

					$this->$param=$params[$param];
				}

			$this->tags_array_type_hint($this->default_tags);
		}

		protected function tags_array_type_hint($array)
		{
			foreach($array as $tag_param=>$tag_value)
			{
				if(!is_string($tag_param))
					throw new sitemap_generator_exception('One of the tag name in the default_tags array is not a string');

				if(!is_string($tag_value))
					throw new sitemap_generator_exception($tag_param.' tag value is not a string in the default_tags array');
			}
		}

		public function add(string $loc, array $tags=[])
		{
			$this->tags_array_type_hint($tags);
			$this->content[$loc]=array_merge($this->default_tags, $tags);

			return $this;
		}
		public function get()
		{
			$sitemap=''
			.	'<?xml version="1.0" encoding="UTF-8" ?>'."\n"
			.	'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
			.	"\n";

			foreach($this->content as $loc=>$tags)
			{
				$sitemap.='<url>'."\n";
					$sitemap.=''
					.	'<loc>'
					.		$this->url.$loc
					.	'</loc>'
					.	"\n";

					foreach($tags as $tag_name=>$tag_value)
						$sitemap.=''
						.	'<'.$tag_name.'>'
						.		$tag_value
						.	'</'.$tag_name.'>'
						.	"\n";

				$sitemap.='</url>'."\n";
			}

			$sitemap.='</urlset>'."\n";

			return $sitemap;
		}
	}
?>