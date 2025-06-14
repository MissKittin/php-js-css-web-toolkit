<?php
	class lv_hlp_exception extends Exception {}

	function _lv_hlp_load_library($type, $library_file, $function_or_class)
	{
		$result=false;

		switch($type)
		{
			case 'class':
				$result=class_exists($function_or_class);
			break;
			case 'function':
				$result=function_exists($function_or_class);
		}

		if($result)
			return;

		if(file_exists(__DIR__.'/lib/'.$library_file))
			return require __DIR__.'/lib/'.$library_file;

		if(file_exists(__DIR__.'/../../lib/'.$library_file))
			return require __DIR__.'/../../lib/'.$library_file;

		throw new lv_hlp_exception(
			$library_file.' library not found'
		);
	}

	(function($libraries){
		foreach($libraries as $library_file=>$function_or_class)
			_lv_hlp_load_library('class', $library_file, $function_or_class);
	})([
		'lv_arr.php'=>'lv_arr_exception',
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
			return lv_hlp_collection
			::	make($array)
			->	sort_by($callback)
			->	all();
		}
		function lv_hlp_sort_desc(array $array, $callback=null)
		{
			return lv_hlp_collection
			::	make($array)
			->	sort_by_desc($callback)
			->	all();
		}
		function lv_hlp_lazy_collect($value=[])
		{
			return new lv_hlp_lazy_collection($value);
		}
		function lv_hlp_to_css_styles($array)
		{
			return lv_arr_to_css_styles($array);
		}

	// pluralizer
		class lv_hlp_pluralizer
		{
			protected static $inflector=null;
			protected static $language='english';

			public static $uncountable=['recommended', 'related'];

			protected static function match_case(string $value, string $comparison)
			{
				if(!function_exists('mb_strtolower'))
					throw new lv_hlp_exception(
						'mbstring extension is not loaded'
					);

				foreach([
					'mb_strtolower',
					'mb_strtoupper',
					'ucfirst',
					'ucwords'
				] as $function)
					if($function($comparison) === $comparison)
						return $function($value);

				return $value;
			}

			public static function inflector()
			{
				if(!class_exists('\Doctrine\Inflector\Inflector'))
					throw new lv_hlp_exception(
						'doctrine/inflector package is not installed'
					);

				if(is_null(static::$inflector))
					static::$inflector=Doctrine\Inflector\InflectorFactory
					::	createForLanguage(static::$language)
					->	build();

				return static::$inflector;
			}
			public static function plural(string $value, $count=2)
			{
				_lv_hlp_load_library('function', 'pf_is_countable.php', 'is_countable');

				if(is_countable($count))
					$count=count($count);

				if(
					((int)abs($count) === 1) ||
					in_array(strtolower($value), static::$uncountable) || // static::uncountable()
					(preg_match(
						'/^(.*)[A-Za-z0-9\x{0080}-\x{FFFF}]$/u',
						$value
					) == 0)
				)
					return $value;

				return static::match_case(
					static::inflector()->pluralize($value),
					$value
				);
			}
			public static function plural_studly(string $value, $count=2)
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
				.	static::plural($last_word, $count);
			}
			public static function singular(string $value)
			{
				return static::match_case(
					static::inflector()->singularize($value),
					$value
				);
			}
			public static function use_language(string $language)
			{
				static::$language=$language;
				static::$inflector=null;
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

				return new static(lv_hlp_pluralizer::plural_studly(
					$this->value,
					$count
				));
			}
			public function scan(string $format)
			{
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
						throw new lv_hlp_exception(
							'symfony/var-dumper package is not installed'
						);

					dump(
						$this,
						...$args
					);

					return $this;
				}
			/* } */

			public function collect()
			{
				return new lv_hlp_collection(
					$this->all()
				);
			}
			public function ensure($type)
			{
				$allowed_types=$type;

				if(!is_array($type))
					$allowed_types=[$type];

				return $this->each(function($item) use($allowed_types){
					_lv_hlp_load_library('function', 'pf_get_debug_type.php', 'get_debug_type');

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
				return new lv_hlp_lazy_collection(
					$this->items
				);
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

	// encrypter
		final class lv_hlp_encrypter
		{
			private static $encrypter=null;

			public static function set_key(
				$key,
				$cipher='aes-256-gcm'
			){
				_lv_hlp_load_library('class', 'sec_lv_encrypter.php', 'lv_encrypter');

				self::$encrypter=new lv_encrypter(
					$key,
					$cipher
				);

				return self::class;
			}

			public static function encrypt($content)
			{
				if(self::$encrypter === null)
					throw new lv_hlp_exception(
						'Use '.__CLASS__.'::set_key first'
					);

				return self::$encrypter->encrypt($content);
			}
			public static function decrypt($payload)
			{
				if(self::$encrypter === null)
					throw new lv_hlp_exception(
						'Use '.__CLASS__.'::set_key first'
					);

				return self::$encrypter
				->	decrypt($payload);
			}
		}

		function lv_hlp_encrypter_generate_key($cipher='aes-256-gcm')
		{
			_lv_hlp_load_library('class', 'sec_lv_encrypter.php', 'lv_encrypter');
			return lv_encrypter::generate_key($cipher);
		}
		function lv_hlp_encrypter_key($key)
		{
			if($key === false)
				return;

			return lv_hlp_encrypter::set_key($key);
		}
		function lv_hlp_encrypt($content)
		{
			return lv_hlp_encrypter::encrypt($content);
		}
		function lv_hlp_decrypt($content)
		{
			return lv_hlp_encrypter::decrypt($content);
		}

	// view
		class lv_hlp_view
		{
			protected static $directives=[];
			protected static $engine_resolvers=[];
			protected static $cache_path=null;
			protected static $view_path=null;

			protected static function get_blade_compiler()
			{
				$blade_compiler=new Illuminate\View\Compilers\BladeCompiler(
					new Illuminate\Filesystem\Filesystem(),
					static::$cache_path
				);

				foreach(
					static::$directives
					as $directive_name=>$directive_params
				)
					$blade_compiler->directive(
						$directive_name,
						$directive_params[0],
						$directive_params[1]
					);

				return $blade_compiler;
			}
			protected static function get_compiler_engine($blade_compiler)
			{
				return new Illuminate\View\Engines\CompilerEngine(
					$blade_compiler
				);
			}
			protected static function get_engine_resolver($compiler_engine=null)
			{
				$engine_resolver=new Illuminate\View\Engines\EngineResolver();

				if(
					($compiler_engine !== null) &&
					(!static::is_resolver_registered('blade'))
				)
					static::register_resolver('blade', function() use($compiler_engine){
						return $compiler_engine;
					});

				foreach(
					static::$engine_resolvers
					as $resolver_name=>$resolver_callback
				)
					$engine_resolver->register(
						$resolver_name,
						$resolver_callback
					);

				return $engine_resolver;
			}

			public static function is_directive_registered(string $name)
			{
				return isset(
					static::$directives[$name]
				);
			}
			public static function is_resolver_registered(string $name)
			{
				return isset(
					static::$engine_resolvers[$name]
				);
			}
			public static function register_directive(
				string $name,
				callable $callback,
				bool $bind=false
			){
				static::$directives[$name]=[
					$callback,
					$bind
				];

				return static::class;
			}
			public static function register_resolver(
				string $name,
				Closure $callback
			){
				static::$engine_resolvers[$name]=$callback;
				return static::class;
			}

			public static function set_cache_path(string $path)
			{
				static::$cache_path=$path;
				return static::class;
			}
			public static function set_view_path(string $path)
			{
				static::$view_path=$path;
				return static::class;
			}

			public static function load_blade(
				string $blade,
				array $data=[]
			){
				if(!class_exists('\Illuminate\View\View'))
					throw new lv_hlp_exception(
						'illuminate/view package is not installed'
					);

				if(static::$cache_path === null)
					throw new lv_hlp_exception(
						'Use '.__CLASS__.'::set_cache_path first'
					);

				if(static::$view_path === null)
					throw new lv_hlp_exception(
						'Use '.__CLASS__.'::set_view_path first'
					);

				if(!file_exists(static::$cache_path))
					throw new lv_hlp_exception(''
					.	static::$cache_path
					.	' does not exist (cache path)'
					);

				if(!file_exists(
					static::$view_path.'/'.$blade.'.blade.php'
				))
					throw new lv_hlp_exception(''
					.	static::$view_path.'/'.$blade.'.blade.php '
					.	'does not exist (view path)'
					);

				$compiler_engine=static::get_compiler_engine(
					static::get_blade_compiler()
				);

				return new Illuminate\View\View(
					new Illuminate\View\Factory(
						static::get_engine_resolver(
							$compiler_engine
						),
						new Illuminate\View\FileViewFinder(
							new Illuminate\Filesystem\Filesystem(),
							[static::$view_path]
						),
						new Illuminate\Events\Dispatcher(
							new Illuminate\Container\Container()
						)
					),
					$compiler_engine,
					static::$view_path,
					static::$view_path.'/'.$blade.'.blade.php',
					$data
				);
			}
			public static function view(
				string $blade,
				array $data=[]
			){
				return static
				::	load_blade($blade, $data)
				->	render();
			}
		}

		function lv_hlp_view(
			string $view_file,
			array $view_data=[],
			?string $view_dir=null,
			?string $cache_dir=null
		){
			if($view_dir !== null)
				lv_hlp_view::set_view_path($view_dir);

			if($cache_dir !== null)
				lv_hlp_view::set_cache_path($cache_dir);

			return lv_hlp_view::view(
				$view_file,
				$view_data
			);
		}

	// inertia
		class lv_hlp_inertia
		{
			protected static $data_page=null;
			protected static $asset_version=null;
			protected static $encrypt_history=false;
			protected static $clear_history=false;

			protected static function process_partial_properties($component, $props)
			{
				if(!isset(
					$_SERVER['HTTP_X_INERTIA_PARTIAL_COMPONENT']
				))
					return $props;

				if(
					$_SERVER['HTTP_X_INERTIA_PARTIAL_COMPONENT']
					!==
					$component
				)
					return $props;

				if(isset(
					$_SERVER['HTTP_X_INERTIA_PARTIAL_DATA']
				)){
					$partial_data=array_filter(explode(
						',',
						$_SERVER['HTTP_X_INERTIA_PARTIAL_DATA']
					));

					foreach($props as $prop=>$prop_value)
						if(!in_array($prop, $partial_data))
							unset($props[$prop]);

					return $props;
				}

				if(!isset(
					$_SERVER['HTTP_X_INERTIA_PARTIAL_EXCEPT']
				))
					return $props;

				$partial_data=array_filter(explode(
					',',
					$_SERVER['HTTP_X_INERTIA_PARTIAL_EXCEPT']
				));

				foreach($props as $prop=>$prop_value)
					if(in_array($prop, $partial_data))
						unset($props[$prop]);

				return $props;
			}
			protected static function process_closures($props)
			{
				foreach($props as $prop=>$prop_value)
				{
					if(static::_instanceof_closure($prop_value))
					{
						$props[$prop]=$prop_value();
						continue;
					}

					if(
						is_object($prop_value) &&
						method_exists($prop_value , '__toString')
					)
						$props[$prop]=$prop_value->__toString();
				}

				return $props;
			}
			protected static function _instanceof_closure($prop_value)
			{
				// for testing purposes
				return ($prop_value instanceof Closure);
			}

			public static function set_asset_version(string $file)
			{
				if(!is_file($file))
					throw new lv_hlp_exception(
						$file.' does not exist'
					);

				static::$asset_version=md5_file($file);

				return static::class;
			}
			public static function set_clear_history(bool $option)
			{
				static::$clear_history=$option;
				return static::class;
			}
			public static function set_encrypt_history(bool $option)
			{
				static::$encrypt_history=$option;
				return static::class;
			}

			public static function api(
				string $component,
				array $props=[]
			){
				if(!isset(
					$_SERVER['REQUEST_URI']
				))
					throw new lv_hlp_exception(
						'REQUEST_URI is not set in $_SERVER'
					);

				static::$data_page=[
					'component'=>$component,
					'props'=>static::process_closures(
						static::process_partial_properties(
							$component,
							$props
						)
					),
					'url'=>$_SERVER['REQUEST_URI'],
					'encryptHistory'=>static::$encrypt_history,
					'clearHistory'=>static::$clear_history
				];

				if(static::$asset_version !== null)
					static::$data_page['version']=static::$asset_version;

				return (
					isset($_SERVER['HTTP_X_INERTIA']) &&
					($_SERVER['HTTP_X_INERTIA'] === 'true')
				);
			}
			public static function render($data_page=null)
			{
				if($data_page === null)
					$data_page=static::$data_page;

				if($data_page === null)
					throw new lv_hlp_exception(
						'$data_page is not set - maybe you did not call the api() method?'
					);

				if(static::$asset_version !== null)
				{
					if(!isset(
						$_SERVER['REQUEST_METHOD']
					))
						throw new lv_hlp_exception(
							'REQUEST_METHOD is not set in $_SERVER'
						);

					if(
						isset($_SERVER['HTTP_X_INERTIA_VERSION']) &&
						($_SERVER['REQUEST_METHOD'] === 'GET') &&
						($_SERVER['HTTP_X_INERTIA_VERSION'] !== static::$asset_version)
					){
						http_response_code(409);

						header(''
						.	'X-Inertia-Location: '
						.	$_SERVER['REQUEST_URI']
						);

						return '';
					}

					header(''
					.	'X-Inertia-Version: '
					.	static::$asset_version
					);
				}

				header('Content-Type: application/json');
				header('X-Inertia: true');
				header('Vary: X-Inertia');

				return json_encode(
					static::$data_page
				);
			}

			public static function get_template(
				string $id='app',
				$data_page=null
			){
				if($data_page === null)
					$data_page=static::$data_page;

				if($data_page === null)
					throw new lv_hlp_exception(
						'$data_page is not set - maybe you did not call the api() method?'
					);

				return ''
				.	'<div '
				.		'id="'.$id.'" '
				.		'data-page="'
				.			htmlspecialchars(
								json_encode($data_page)
							)
				.		'"'
				.	'></div>';
			}

			public static function register_lv_view_directives(string $id='app')
			{
				if(lv_hlp_view::is_directive_registered(
					'inertia'
				))
					return static::class;

				lv_hlp_view::register_directive('inertia', function() use($id){
					return static::get_template($id);
				});

				return static::class;
			}
			public static function register_twig_functions(string $id='app')
			{
				if(!class_exists('\Twig\TwigFunction'))
					throw new lv_hlp_exception(
						'twig/twig package is not installed'
					);

				return new Twig\TwigFunction('inertia', function() use($id){
					return new Twig\Markup(
						static::get_template($id),
						'UTF-8'
					);
				});
			}
		}
?>