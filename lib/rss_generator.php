<?php
	/*
	 * RSS XML builder
	 *
	 * Note:
	 *  throws an rss_generator_exception on error
	 *
	 * Usage:
		$rss=new rss_generator([
			'link'=>'https://my.website', // website link
			'item_link_prefix'=>'/articles', // $link.$item_link_prefix
			'stylesheet'=>'/assets/rss.xsl',
			//'stylesheet_no_link'=>true, // do not add https://my.website at the beginning of the stylesheet URL
			'channel_tags'=>[ // before <item> tags
				'title'=>'Channel title',
				'language'=>'en-us',
				'description'=>'Channel description',
				'otherTag'=>'other tag value', // <otherTag>other tag value</otherTag>
				'anotherTag'=>'', // <anotherTag />
				// etc
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
					'otherTag'=>'other tag value', // <otherTag>other tag value</otherTag>
					'anotherTag'=>'', // <anotherTag />
					// etc
				])
			)
		->	add(
				new rss_generator_item([
					'title'=>'Third article',
					'link'=>'/third-article', // https://my.website/articles/third-article
					'pubDate'=>'Thu, 27 Apr 2006',
					'description'=>'<h1>Third article content</h1>',
					'otherTag'=>'other tag value', // <otherTag>other tag value</otherTag>
					'anotherTag'=>'', // <anotherTag />
					// etc
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
				->	otherTag('other tag value') // <otherTag>other tag value</otherTag>
				->	anotherTag() // <anotherTag />
				// etc
			)
		->	add(
				(new rss_generator_item())
				->	title('First article')
				->	link('/first-article') // https://my.website/articles/first-article
				->	pubDate('Thu, 28 Apr 2006')
				->	description('<h1>First article content</h1>'),
				->	otherTag('other tag value') // <otherTag>other tag value</otherTag>
				->	anotherTag() // <anotherTag />
				// etc
			);

		$rss_xml=$rss->get();

		// you can also print xml to output
		header('Content-Type: application/rss+xml');
		$rss->echo();
	 */

	class rss_generator_exception extends Exception {}
	class rss_generator
	{
		protected $link='';
		protected $item_link_prefix='';
		protected $stylesheet=null;
		protected $stylesheet_no_link=false;
		protected $channel_tags=[];
		protected $items=[];

		public function __construct(array $params)
		{
			foreach([
				'link'=>'string',
				'item_link_prefix'=>'string',
				'stylesheet'=>'string',
				'stylesheet_no_link'=>'boolean',
				'channel_tags'=>'array'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new rss_generator_exception(
							'The input array parameter '.$param.' is not a '.$param_type
						);

					$this->$param=$params[$param];
				}

			$this->channel_tags_array_type_hint(
				$this->channel_tags
			);
		}

		protected function channel_tags_array_type_hint($array)
		{
			foreach($array as $tag_param=>$tag_value)
			{
				if(!is_string($tag_param))
					throw new rss_generator_exception(
						'One of the tag name in the channel_tags array is not a string'
					);

				if(!is_string($tag_value))
					throw new rss_generator_exception(
						$tag_param.' tag value is not a string in the channel_tags array'
					);
			}
		}
		protected function get_xml_headers()
		{
			$stylesheet='';
			$link='';

			if($this->stylesheet !== null)
				$stylesheet=''
				.	'<?xml-stylesheet type="text/xsl" href="'
				.		(($this->stylesheet_no_link)? '' : $this->link)
				.		$this->stylesheet
				.	'" ?>';

			if($this->link !== '')
				$link=''
				.	'<link>'
				.		$this->link
				.	'</link>';

			return ''
			.	'<?xml version="1.0" encoding="UTF-8" ?>'
			.	$stylesheet
			.	'<rss version="2.0">'
			.		'<channel>'
			.			$link;
		}

		public function add(rss_generator_item $item)
		{
			$this->items[]=$item;
			return $this;
		}
		public function get()
		{
			$rss=$this->get_xml_headers();

			foreach($this->channel_tags as $tag_name=>$tag_value)
			{
				if($tag_value === '')
				{
					$rss.='<'.$tag_name.' />';
					continue;
				}

				$rss.=''
				.	'<'.$tag_name.'>'
				.		$tag_value
				.	'</'.$tag_name.'>';
			}

			foreach($this->items as $item)
				$rss.=''
				.	'<item>'
				.		$item->get_xml(''
						.	$this->link
						.	$this->item_link_prefix
						)
				.	'</item>';

			return ''
			.	$rss
			.	'</channel>'
			.	'</rss>';
		}
		public function echo()
		{
			echo $this->get_xml_headers();

			foreach($this->channel_tags as $tag_name=>$tag_value)
			{
				if($tag_value === '')
				{
					echo '<'.$tag_name.' />';
					continue;
				}

				echo ''
				.	'<'.$tag_name.'>'
				.		$tag_value
				.	'</'.$tag_name.'>';
			}

			foreach($this->items as $item)
				echo ''
				.	'<item>'
				.		$item->get_xml(''
						.	$this->link
						.	$this->item_link_prefix
						)
				.	'</item>';

			echo ''
			.	'</channel>'
			.	'</rss>';
		}
	}
	class rss_generator_item
	{
		protected $link=null;
		protected $description_closure=null;
		protected $xml='';

		public function __construct(array $tags=[])
		{
			foreach($tags as $tag_name=>$tag_value)
				$this->__call(
					$tag_name,
					[$tag_value]
				);
		}
		public function __call(string $name, $arguments)
		{
			if(
				(!isset($arguments[0])) ||
				($arguments[0] === '')
			){
				$this->xml.='<'.$name.' />';
				return $this;
			}

			if(
				($name === 'description') &&
				($arguments[0] instanceof Closure)
			){
				$this->description_closure[0]=$arguments[0];
				return $this;
			}

			if(!is_string($arguments[0]))
				throw new rss_generator_exception(
					'The argument for '.$name.' is not a string'
				);

			if($name === 'link')
			{
				$this->link=$arguments[0];
				return $this;
			}

			if($name === 'description')
			{
				$this->xml.=''
				.	'<description>'
				.		htmlspecialchars($arguments[0], ENT_QUOTES, 'UTF-8')
				.	'</description>';

				return $this;
			}

			$this->xml.=''
			.	'<'.$name.'>'
			.		$arguments[0]
			.	'</'.$name.'>';

			return $this;
		}

		public function get_xml(string $item_link_prefix)
		{
			$description='';

			if($this->description_closure !== null)
				$description=''
				.	'<description>'
				.		htmlspecialchars(
							$this->description_closure[0](),
							ENT_QUOTES,
							'UTF-8'
						)
				.	'</description>';

			if($this->link === null)
				return ''
				.	$this->xml
				.	$description;

			if($item_link_prefix === '')
				return ''
				.	'<link>'
				.		$this->link
				.	'</link>'
				.	$this->xml
				.	$description;

			return ''
			.	'<link>'
			.		$item_link_prefix
			.		$this->link
			.	'</link>'
			.	$this->xml
			.	$description;
		}
	}
?>