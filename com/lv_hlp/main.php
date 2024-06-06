<?php
	class lv_hlp_exception extends Exception {}

	(function($libraries){
		foreach($libraries as $check_function=>$library_meta)
			foreach($library_meta as $library_file=>$library_function)
				if(!$check_function($library_function))
				{
					if(file_exists(__DIR__.'/lib/'.$library_file))
						require __DIR__.'/lib/'.$library_file;
					else if(file_exists(__DIR__.'/../../lib/'.$library_file))
						require __DIR__.'/../../lib/'.$library_file;
					else
						throw new lv_hlp_exception($library_file.' library not found');
				}
	})([
		'class_exists'=>[
			'ascii.php'=>'ascii_exception',
			'lv_arr.php'=>'lv_arr_exception',
			'lv_macroable.php'=>'lv_macroable_exception',
			'lv_str.php'=>'lv_str_exception',
			'ocw_slugify.php'=>'ocw_slugify_exception',
			'pf_ValueError.php'=>'ValueError', // dep pf_json_validate.php
			'ulid.php'=>'ulid_exception',
			'uuid.php'=>'uuid_exception'
		],
		'function_exists'=>[
			'pf_get_debug_type.php'=>'get_debug_type',
			'pf_mbstring.php'=>'mb_str_split',
			'pf_json_validate.php'=>'json_validate'
		]
	]);

	// string helpers
		function lv_hlp_ascii($value)
		{
			return lv_str_ascii($value);
		}
		function lv_hlp_inline_markdown($string, $options=[])
		{
			return lv_str_inline_markdown($string, $options);
		}
		function lv_hlp_is_ascii($value)
		{
			return lv_str_is_ascii($value);
		}
		function lv_hlp_is_json($value)
		{
			return lv_str_is_json($value);
		}
		function lv_hlp_is_ulid($value)
		{
			return lv_str_is_ulid($value);
		}
		function lv_hlp_is_uuid($value)
		{
			return lv_str_is_uuid($value);
		}
		function lv_hlp_match_all(string $pattern, string $subject)
		{
			preg_match_all($pattern, $subject, $matches);

			if(empty($matches[0]))
				return lv_hlp_collect();

			if(isset($matches[1]))
				return lv_hlp_collect($matches[1]);

			return lv_hlp_collect($matches[0]);
		}
		function lv_hlp_markdown($string, $options=[])
		{
			return lv_str_markdown($string, $options);
		}
		function lv_hlp_of($string)
		{
			return new lv_hlp_ingable($string);
		}
		function lv_hlp_ordered_uuid()
		{
			return generate_uuid_ordered();
		}
		function lv_hlp_password($length=32, $letters=true, $numbers=true, $symbols=true, $spaces=false)
		{
			return lv_str_password($length, $letters, $numbers, $symbols, $spaces);
		}
		function lv_hlp_slug($title, $separator='-', $dictionary=['@' => 'at'])
		{
			return lv_str_slug($title, $separator, $dictionary);
		}
		function lv_hlp_str($string=null)
		{
			if(func_num_args() === 0)
				return new class
				{
					public function __call($method, $parameters)
					{
						$method='lv_str_'.$method;

						if($method === 'lv_str_of')
							$method='lv_hlp_of';

						return $method(...$parameters);
					}
					public function __toString()
					{
						return '';
					}
				};

			return lv_hlp_of($string);
		}
		function lv_hlp_ulid()
		{
			return generate_ulid();
		}
		function lv_hlp_uuid()
		{
			return generate_uuid_v4();
		}
		function lv_str_ascii($value)
		{
			return to_ascii($value);
		}
		function lv_str_inline_markdown(string $string, array $options=[])
		{
			if(!class_exists('\League\CommonMark\MarkdownConverter'))
				throw new lv_hlp_exception('league/commonmark package is not installed');

			return (string)(new League\CommonMark\MarkdownConverter(
				(new League\CommonMark\Environment($options))
				->	addExtension(
						new League\CommonMark\Extension\GithubFlavoredMarkdownExtension()
					)
				->	addExtension(
						new League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension()
					)
			))->convertToHtml($string);
		}
		function lv_str_is_ascii($value)
		{
			return is_ascii($value);
		}
		function lv_str_is_json($value)
		{
			if(!is_string($value))
				return false;

			return json_validate($value, 512);
		}
		function lv_str_is_ulid($value)
		{
			if(!is_string($value))
				return false;

			return is_ulid($value);
		}
		function lv_str_is_uuid($value)
		{
			if(!is_string($value))
				return false;

			return is_uuid($value);
		}
		function lv_str_markdown(string $string, array $options=[])
		{
			if(!class_exists('\League\CommonMark\GithubFlavoredMarkdownConverter'))
				throw new lv_hlp_exception('league/commonmark package is not installed');

			return (string)(new League\CommonMark\GithubFlavoredMarkdownConverter($options))
			->	convertToHtml($string);
		}
		function lv_str_ordered_uuid()
		{
			return generate_uuid_ordered();
		}
		function lv_str_password(
			int $length=32,
			bool $letters=true,
			bool $numbers=true,
			bool $symbols=true,
			bool $spaces=false
		){
			$password=new lv_hlp_collection();
			$charmap=[
				'letters'=>null,
				'numbers'=>null,
				'symbols'=>null,
				'spaces'=>null
			];

			if($letters)
				$charmap['letters']=[
					'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
					'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
					'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
					'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
					'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
				];
			if($numbers)
				$charmap['numbers']=['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
			if($symbols)
				$charmap['symbols']=[
					'~', '!', '#', '$', '%', '^', '&', '*', '(', ')', '-',
					'_', '.', ',', '<', '>', '?', '/', '\\', '{', '}', '[',
					']', '|', ':', ';'
				];
			if($spaces)
				$charmap['spaces']=[' '];

			$options=(new lv_hlp_collection($charmap))
			->	filter()
			->	each(function($callback) use($password){
					return $password->push($callback[random_int(
						0,
						count($callback)-1
					)]);
				})
			->	flatten();

			$length=$length-($password->count());

			return $password
			->	merge($options->pipe(function($callback) use($length){
					return lv_hlp_collection::times($length, function() use($callback){
						return $callback[random_int(
							0,
							($callback->count())-1
						)];
					});
				}))
			->	shuffle()
			->	implode('');
		}
		function lv_str_slug($title, $separator='-', array $dictionary=['@' => 'at'])
		{
			$replacements=[];

			foreach($dictionary as $key=>$value)
				$replacements['/\b('.$key.')\b/i']=$value;

			return sgmurphy_url_slug(
				$title,
				[
					'delimiter'=>$separator,
					'replacements'=>$replacements,
					'transliterate'=>true
				]
			);
		}
		function lv_str_ulid()
		{
			return generate_ulid();
		}
		function lv_str_uuid()
		{
			return generate_uuid_v4();
		}

		if(function_exists('mb_strtolower'))
		{
			function lv_hlp_excerpt(string $text, string $phrase='', array $options=[])
			{
				$radius=100;
				$omission='...';

				if(isset($options['radius']))
					$radius=$options['radius'];

				if(isset($options['omission']))
					$omission=$options['omission'];

				preg_match(
					'/^(.*?)('.preg_quote((string)$phrase, '/').')(.*)$/iu',
					(string)$text,
					$matches
				);

				if(empty($matches))
					return null;

				$start=ltrim($matches[1]);
				$start=lv_hlp_str(mb_substr(
					$start,
					max(
						mb_strlen($start, 'UTF-8')-$radius,
						0
					),
					$radius,
					'UTF-8'
				))
				->	ltrim()
				->	unless(
						function($start_with_radius) use($start)
						{
							return $start_with_radius->exactly($start);
						},
						function($start_with_radius) use($omission)
						{
							return $start_with_radius->prepend($omission);
						}
					);
				$end=rtrim($matches[3]);
				$end=lv_hlp_str(mb_substr(
					$end, 0, $radius, 'UTF-8'
				))
				->	rtrim()
				->	unless(
						function($end_with_radius) use($end)
						{
							return $end_with_radius->exactly($end);
						},
						function($end_with_radius) use($omission)
						{
							return $end_with_radius->append($omission);
						}
					);

				return $start
				->	append($matches[2], $end)
				->	to_string();
			}
			function lv_str_reverse(string $value)
			{
				return implode(array_reverse(mb_str_split($value)));
			}
		}
		else /* some boilerplate */
		{
			function lv_hlp_excerpt()
			{
				throw new lv_hlp_exception('mbstring extension is not loaded');
			}
			function lv_str_reverse()
			{
				throw new lv_hlp_exception('mbstring extension is not loaded');
			}
		}

	// array helpers
		function lv_arr_to_css_styles(array $array)
		{
			$style_list=lv_arr_wrap($array);
			$styles=[];

			foreach($style_list as $class=>$constraint)
			{
				if(is_numeric($class))
				{
					$styles[]=lv_str_finish($constraint, ';');
					continue;
				}

				if($constraint)
					$styles[]=lv_str_finish($class, ';');
			}

			return implode(' ', $styles);
		}
		function lv_hlp_collect($value=[])
		{
			return new lv_hlp_collection($value);
		}
		function lv_hlp_sort(array $array, $callback=null)
		{
			return lv_hlp_collection::make($array)->sort_by($callback)->all();
		}
		function lv_hlp_sort_desc(array $array, $callback=null)
		{
			return lv_hlp_collection::make($array)->sort_by_desc($callback)->all();
		}
		function lv_hlp_lazy_collect($value=[])
		{
			return new lv_hlp_lazy_collection($value);
		}
		function lv_hlp_to_css_styles($array)
		{
			return lv_arr_to_css_styles($array);
		}

	// stringable
		class lv_hlp_ingable extends lv_str_ingable
		{
			use t_lv_macroable;

			public function ascii(string $language='en')
			{
				return new static(lv_str_ascii($this->value, $language));
			}
			public function dd()
			{
				$this->dump();
				exit(1);
			}
			public function dump()
			{
				if(!class_exists('\Symfony\Component\VarDumper\VarDumper'))
					throw new lv_hlp_exception('symfony/var-dumper package is not installed');

				Symfony\Component\VarDumper\VarDumper::dump($this->value);

				return $this;
			}
			public function excerpt(string $phrase='', array $options=[])
			{
				return lv_hlp_excerpt($this->value, $phrase, $options);
			}
			public function explode(string $delimiter, int $limit=PHP_INT_MAX)
			{
				return lv_hlp_collect(explode($delimiter, $this->value, $limit));
			}
			public function inline_markdown(array $options=[])
			{
				return new static(lv_str_inline_markdown($this->value, $options));
			}
			public function is_ascii()
			{
				return lv_str_is_ascii($this->value);
			}
			public function is_json()
			{
				return lv_str_is_json($this->value);
			}
			public function is_ulid()
			{
				return lv_str_is_ulid($this->value);
			}
			public function is_uuid()
			{
				return lv_str_is_uuid($this->value);
			}
			public function markdown(array $options=[])
			{
				return new static(lv_str_markdown($this->value, $options));
			}
			public function match_all(string $pattern)
			{
				return lv_hlp_match_all($pattern, $this->value);
			}
			public function reverse()
			{
				return new static(lv_str_reverse($this->value));
			}
			public function scan(string $format)
			{
				return lv_hlp_collect(sscanf($this->value, $format));
			}
			public function slug(string $separator='-', array $dictionary=['@'=>'at'])
			{
				return new static(lv_str_slug($this->value, $separator, $dictionary));
			}
			public function split($pattern, int $limit=-1, int $flags=0)
			{
				if(filter_var($pattern, FILTER_VALIDATE_INT) !== false)
					return lv_hlp_collect(mb_str_split($this->value, $pattern));

				$segments=preg_split($pattern, $this->value, $limit, $flags);

				if(empty($segments))
					return lv_hlp_collect();

				return lv_hlp_collect($segments);
			}
			public function transliterate()
			{
				return $this->ascii();
			}
			public function ucsplit()
			{
				return lv_hlp_collect(lv_str_ucsplit($this->value));
			}
			public function when_is_ascii(callable $callback, callable $default=null)
			{
				return $this->when($this->is_ascii(), $callback, $default);
			}
			public function when_is_ulid(callable $callback, callable $default=null)
			{
				return $this->when($this->is_ulid(), $callback, $default);
			}
			public function when_is_uuid(callable $callback, callable $default=null)
			{
				return $this->when($this->is_uuid(), $callback, $default);
			}
		}

	// collections
		trait lv_hlp_enumerates_values
		{
			use lv_arr_enumerates_values;
			/* use dumpable; */

			/* trait dumpable */
			/* { */
				public function dd(...$args)
				{
					$this->dump(...$args);
					dd();
				}
				public function dump(...$args)
				{
					if(!function_exists('dump'))
						throw new lv_hlp_exception('symfony/var-dumper package is not installed');

					dump($this, ...$args);

					return $this;
				}
			/* } */

			public function collect()
			{
				return new lv_hlp_collection($this->all());
			}
			public function ensure($type)
			{
				$allowed_types=$type;

				if(!is_array($type))
					$allowed_types=[$type];

				return $this->each(function($item) use($allowed_types){
					$item_type=get_debug_type($item);

					foreach($allowed_types as $allowed_type)
						if(
							($item_type === $allowed_type) ||
							($item instanceof $allowed_type)
						)
							return true;

					throw new lv_hlp_exception(sprintf(
						"Collection should only include [%s] items, but '%s' found.",
						implode(', ', $allowed_types),
						$item_type
					));
				});
			}
		}

		class lv_hlp_collection
		extends lv_arr_collection
		implements ArrayAccess, lv_arr_enumerable
		{
			use lv_hlp_enumerates_values, t_lv_macroable;

			public function lazy()
			{
				return new lv_hlp_lazy_collection($this->items);
			}
		}
		class lv_hlp_lazy_collection
		extends lv_arr_lazy_collection
		implements lv_arr_enumerable
		{
			use lv_hlp_enumerates_values, t_lv_macroable;

			protected function chunk_while_collection()
			{
				return new lv_hlp_collection();
			}
		}
?>