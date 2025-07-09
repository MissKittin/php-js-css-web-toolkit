<?php
	if(!class_exists('lv_hlp_exception'))
		require __DIR__.'/bootstrap.php';

	(function($libraries){
		foreach($libraries as $library_file=>$function_or_class)
			_lv_hlp_load_library('class', $library_file, $function_or_class);
	})([
		'lv_macroable.php'=>'lv_macroable_exception',
		'lv_str.php'=>'lv_str_exception'
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
			if(!function_exists('lv_hlp_collect'))
				require __DIR__.'/arr.php';

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
			return lv_str_ordered_uuid();
		}
		function lv_hlp_password(
			$length=32,
			$letters=true,
			$numbers=true,
			$symbols=true,
			$spaces=false
		){
			return lv_str_password(
				$length,
				$letters,
				$numbers,
				$symbols,
				$spaces
			);
		}
		function lv_hlp_plural(
			$value,
			$count=2,
			$language='english',
			$uncountable=['recommended', 'related']
		){
			return lv_str_plural(
				$value,
				$count,
				$language,
				$uncountable
			);
		}
		function lv_hlp_plural_studly($value, $count=2)
		{
			return lv_str_plural_studly($value, $count);
		}
		function lv_hlp_singular(string $value)
		{
			return lv_str_singular($value);
		}
		function lv_hlp_slug(
			$title,
			$separator='-',
			$dictionary=['@'=>'at']
		){
			return lv_str_slug(
				$title,
				$separator,
				$dictionary
			);
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

						return $method(
							...$parameters
						);
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
			return lv_str_ulid();
		}
		function lv_hlp_uuid()
		{
			return lv_str_uuid();
		}
		function lv_str_ascii($value)
		{
			_lv_hlp_load_library('function', 'ascii.php', 'to_ascii');
			return to_ascii($value);
		}
		function lv_str_inline_markdown(string $string, array $options=[])
		{
			if(!class_exists('\League\CommonMark\MarkdownConverter'))
				throw new lv_hlp_exception(
					'league/commonmark package is not installed'
				);

			static $environment=null;

			if($environment === null)
			{
				$environment='League\CommonMark\Environment';

				if(class_exists('League\CommonMark\Environment\Environment'))
					$environment='League\CommonMark\Environment\Environment';
			}

			return (string)(new League\CommonMark\MarkdownConverter(
				(new $environment($options))
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
			_lv_hlp_load_library('function', 'ascii.php', 'is_ascii');
			return is_ascii($value);
		}
		function lv_str_is_json($value)
		{
			if(!is_string($value))
				return false;

			_lv_hlp_load_library('class', 'pf_ValueError.php', 'ValueError');
			_lv_hlp_load_library('function', 'pf_json_validate.php', 'json_validate');

			return json_validate($value, 512);
		}
		function lv_str_is_ulid($value)
		{
			if(!is_string($value))
				return false;

			_lv_hlp_load_library('function', 'ulid.php', 'is_ulid');

			return is_ulid($value);
		}
		function lv_str_is_uuid($value)
		{
			if(!is_string($value))
				return false;

			_lv_hlp_load_library('function', 'uuid.php', 'is_uuid');

			return is_uuid($value);
		}
		function lv_str_markdown(string $string, array $options=[])
		{
			if(!class_exists('\League\CommonMark\GithubFlavoredMarkdownConverter'))
				throw new lv_hlp_exception(
					'league/commonmark package is not installed'
				);

			return (string)(new League\CommonMark\GithubFlavoredMarkdownConverter(
				$options
			))->convertToHtml($string);
		}
		function lv_str_ordered_uuid()
		{
			_lv_hlp_load_library('function', 'uuid.php', 'generate_uuid_ordered');
			return generate_uuid_ordered();
		}
		function lv_str_password(
			int $length=32,
			bool $letters=true,
			bool $numbers=true,
			bool $symbols=true,
			bool $spaces=false
		){
			if(!class_exists('lv_hlp_collection'))
				require __DIR__.'/arr.php';

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
		function lv_str_plural_studly(string $value, $count=2)
		{
			$parts=preg_split(
				'/(.)(?=[A-Z])/u',
				$value,
				-1,
				PREG_SPLIT_DELIM_CAPTURE
			);
			$last_word=array_pop($parts);

			return ''
			.	implode('', $parts)
			.	lv_str_plural($last_word, $count);
		}
		function lv_str_slug(
			$title,
			$separator='-',
			array $dictionary=['@'=>'at']
		){
			_lv_hlp_load_library('function', 'ocw_slugify.php', 'sgmurphy_url_slug');

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
			_lv_hlp_load_library('function', 'ulid.php', 'generate_ulid');
			return generate_ulid();
		}
		function lv_str_uuid()
		{
			_lv_hlp_load_library('function', 'uuid.php', 'generate_uuid_v4');
			return generate_uuid_v4();
		}

		if(function_exists('mb_strtolower'))
		{
			function lv_hlp_excerpt(
				string $text,
				string $phrase='',
				array $options=[]
			){
				$radius=100;
				$omission='...';

				if(isset($options['radius']))
					$radius=$options['radius'];

				if(isset($options['omission']))
					$omission=$options['omission'];

				preg_match(
					'/^(.*?)('
					.	preg_quote(
							(string)$phrase,
							'/'
						)
					.')(.*)$/iu',
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
			function lv_str_plural(
				string $value,
				$count=2,
				string $language='english',
				array $uncountable=['recommended', 'related']
			){
				if(!class_exists('\Doctrine\Inflector\Inflector'))
					throw new lv_hlp_exception(
						'doctrine/inflector package is not installed'
					);

				// Pluralizer::inflector()
					static $inflector=null;
					static $inflector_lang=null;

					if($inflector_lang === null)
						$inflector_lang=$language;

					if(
						($inflector === null) ||
						($inflector_lang !== $language)
					)
						$inflector=Doctrine\Inflector\InflectorFactory
						::	createForLanguage($language)
						->	build();

				// Pluralizer::plural()
					_lv_hlp_load_library('function', 'pf_is_countable.php', 'is_countable');

					if(is_countable($count))
						$count=count($count);

					if(
						((int)abs($count) === 1) ||
						in_array(strtolower($value), $uncountable) || // Pluralizer::uncountable()
						(preg_match(
							'/^(.*)[A-Za-z0-9\x{0080}-\x{FFFF}]$/u',
							$value
						) == 0)
					)
						return $value;

					$plural=$inflector->pluralize($value);

					// Pluralizer::matchCase()
						foreach([
							'mb_strtolower',
							'mb_strtoupper',
							'ucfirst',
							'ucwords'
						] as $function)
							if($function($value) === $value)
								return $function($plural);

						return $plural;
			}
			function lv_str_singular(
				string $value,
				string $language='english'
			){
				if(!class_exists('\Doctrine\Inflector\Inflector'))
					throw new lv_hlp_exception(
						'doctrine/inflector package is not installed'
					);

				// Pluralizer::inflector()
					static $inflector=null;
					static $inflector_lang=null;

					if($inflector_lang === null)
						$inflector_lang=$language;

					if(
						($inflector === null) ||
						($inflector_lang !== $language)
					)
						$inflector=Doctrine\Inflector\InflectorFactory
						::	createForLanguage($language)
						->	build();

				// Pluralizer::singular()
					$singular=$inflector->singularize($value);

					// Pluralizer::matchCase()
						foreach([
							'mb_strtolower',
							'mb_strtoupper',
							'ucfirst',
							'ucwords'
						] as $function)
							if($function($value) === $value)
								return $function($singular);

						return $singular;
			}
		}
		else /* some boilerplate */
		{
			function lv_hlp_excerpt()
			{
				throw new lv_hlp_exception(
					'mbstring extension is not loaded'
				);
			}
			function lv_str_plural()
			{
				throw new lv_hlp_exception(
					'mbstring extension is not loaded'
				);
			}
			function lv_str_singular()
			{
				throw new lv_hlp_exception(
					'mbstring extension is not loaded'
				);
			}
		}

	// stringable
		class lv_hlp_ingable extends lv_str_ingable
		{
			use t_lv_macroable;

			public function ascii(string $language='en')
			{
				return new static(lv_str_ascii(
					$this->value,
					$language
				));
			}
			public function dd()
			{
				$this->dump();
				exit(1);
			}
			public function dump()
			{
				if(!class_exists('\Symfony\Component\VarDumper\VarDumper'))
					throw new lv_hlp_exception(
						'symfony/var-dumper package is not installed'
					);

				Symfony\Component\VarDumper\VarDumper::dump(
					$this->value
				);

				return $this;
			}
			public function excerpt(
				string $phrase='',
				array $options=[]
			){
				return lv_hlp_excerpt(
					$this->value,
					$phrase,
					$options
				);
			}
			public function explode(
				string $delimiter,
				int $limit=PHP_INT_MAX
			){
				if(!function_exists('lv_hlp_collect'))
					require __DIR__.'/arr.php';

				return lv_hlp_collect(explode(
					$delimiter,
					$this->value,
					$limit
				));
			}
			public function inline_markdown(array $options=[])
			{
				return new static(lv_str_inline_markdown(
					$this->value,
					$options
				));
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
				return new static(lv_str_markdown(
					$this->value,
					$options
				));
			}
			public function match_all(string $pattern)
			{
				return lv_hlp_match_all(
					$pattern,
					$this->value
				);
			}
			public function plural($count=2)
			{
				//return new static(lv_str_plural(
				//	$this->value,
				//	$count
				//));

				if(!class_exists('lv_hlp_pluralizer'))
					require __DIR__.'/pluralizer.php';

				return new static(lv_hlp_pluralizer::plural(
					$this->value,
					$count
				));
			}
			public function plural_studly($count=2)
			{
				//return new static(lv_str_plural_studly(
				//	$this->value,
				//	$count
				//));

				if(!class_exists('lv_hlp_pluralizer'))
					require __DIR__.'/pluralizer.php';

				return new static(lv_hlp_pluralizer::plural_studly(
					$this->value,
					$count
				));
			}
			public function scan(string $format)
			{
				if(!function_exists('lv_hlp_collect'))
					require __DIR__.'/arr.php';

				return lv_hlp_collect(sscanf(
					$this->value,
					$format
				));
			}
			public function singular()
			{
				//return new static(lv_str_singular(
				//	$this->value
				//));

				if(!class_exists('lv_hlp_pluralizer'))
					require __DIR__.'/pluralizer.php';

				return new static(lv_hlp_pluralizer::singular(
					$this->value
				));
			}
			public function slug(
				string $separator='-',
				array $dictionary=['@'=>'at']
			){
				return new static(lv_str_slug(
					$this->value,
					$separator,
					$dictionary
				));
			}
			public function split(
				$pattern,
				int $limit=-1,
				int $flags=0
			){
				if(!function_exists('lv_hlp_collect'))
					require __DIR__.'/arr.php';

				if(filter_var($pattern, FILTER_VALIDATE_INT) !== false)
				{
					_lv_hlp_load_library('function', 'pf_mbstring.php', 'mb_str_split');

					return lv_hlp_collect(mb_str_split(
						$this->value,
						$pattern
					));
				}

				$segments=preg_split(
					$pattern,
					$this->value,
					$limit,
					$flags
				);

				if(empty($segments))
					return lv_hlp_collect();

				return lv_hlp_collect($segments);
			}
			public function to_date(
				string $format,
				?string $tz=null
			){
				if(!class_exists('\Carbon\Carbon'))
					throw new lv_hlp_exception(
						'nesbot/carbon package is not installed'
					);

				if(is_null($format))
					return Carbon\Carbon::parse(
						$this->value,
						$tz
					);

				return Carbon\Carbon::createFromFormat(
					$format,
					$this->value,
					$tz
				);
			}
			public function transliterate()
			{
				return $this->ascii();
			}
			public function ucsplit()
			{
				if(!function_exists('lv_hlp_collect'))
					require __DIR__.'/arr.php';

				return lv_hlp_collect(lv_str_ucsplit(
					$this->value
				));
			}
			public function when_is_ascii(callable $callback, ?callable $default=null)
			{
				return $this->when($this->is_ascii(), $callback, $default);
			}
			public function when_is_ulid(callable $callback, ?callable $default=null)
			{
				return $this->when($this->is_ulid(), $callback, $default);
			}
			public function when_is_uuid(callable $callback, ?callable $default=null)
			{
				return $this->when($this->is_uuid(), $callback, $default);
			}
		}
?>