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
			'pf_ValueError.php'=>'ValueError',
			'ulid.php'=>'ulid_exception',
			'uuid.php'=>'uuid_exception'
		],
		'function_exists'=>[
			'pf_array.php'=>'array_is_list',
			'pf_get_debug_type.php'=>'get_debug_type',
			'pf_mbstring.php'=>'mb_str_split',
			'pf_json_validate.php'=>'json_validate',
			'pf_str.php'=>'str_starts_with'
		]
	]);

	// string helpers
		function lv_str_ascii($value)
		{
			return to_ascii($value);
		}
		function lv_str_contains_all($haystack, $needles, $ignore_case=false)
		{
			foreach($needles as $needle)
				if(!lv_str_contains($haystack, $needle, $ignore_case))
					return false;

			return true;
		}
		function lv_str_ends_with(string $haystack, $needles)
		{
			if(!is_iterable($needles))
				$needles=(array)$needles;

			foreach($needles as $needle)
				if(((string)$needle !== '') && str_ends_with($haystack, $needle))
					return true;

			return false;
		}
		function lv_str_inline_markdown(string $string, array $options=[])
		{
			if(!class_exists('League\CommonMark\MarkdownConverter'))
				throw new lv_hlp_exception('league/commonmark package is not installed');

			$environment=new League\CommonMark\Environment($options);
			$environment->addExtension(
				new League\CommonMark\Extension\GithubFlavoredMarkdownExtension()
			);
			$environment->addExtension(
				new League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension()
			);
			$converter=new League\CommonMark\MarkdownConverter($environment);

			return (string)$converter->convertToHtml($string);
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
			if(!class_exists('League\CommonMark\GithubFlavoredMarkdownConverter'))
				throw new lv_hlp_exception('league/commonmark package is not installed');

			$converter=new League\CommonMark\GithubFlavoredMarkdownConverter($options);

			return (string)$converter->convertToHtml($string);
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
			->filter()
			->each(function($c) use($password){
				return $password->push($c[random_int(
					0,
					count($c)-1
				)]);
			})
			->flatten();

			$length=$length-($password->count());

			return $password->merge($options->pipe(function($c) use($length){
				return lv_hlp_collection::times($length, function() use($c){
					return $c[random_int(
						0,
						($c->count())-1
					)];
				});
			}))->shuffle()->implode('');
		}
		function lv_str_remove($search, $subject, bool $case_sensitive=true)
		{
			if($search instanceof Traversable)
				$search=lv_hlp_collect($search)->all();

			if($case_sensitive)
				return str_replace($search, '', $subject);

			return str_ireplace($search, '', $subject);
		}
		function lv_str_replace(
			$search,
			$replace,
			$subject,
			bool $case_sensitive=true
		){
			if($search instanceof Traversable)
				$search=lv_hlp_collect($search)->all();
			if($replace instanceof Traversable)
				$replace=lv_hlp_collect($replace)->all();
			if($subject instanceof Traversable)
				$subject=lv_hlp_collect($subject)->all();

			if($case_sensitive)
				return str_replace($search, $replace, $subject);

			return str_ireplace($search, $replace, $subject);
		}
		function lv_str_replace_array(string $search, $replace, string $subject)
		{
			$to_string_or=function($value, $fallback)
			{
				try {
					return (string)$value;
				} catch(Throwable $error) {
					return $fallback;
				}
			};

			if($replace instanceof Traversable)
				$replace=lv_hlp_collect($replace)->all();

			$segments=explode($search, $subject);
			$result=array_shift($segments);

			foreach($segments as $segment)
				$result.=$to_string_or(array_shift($replace) ?? $search, $search).$segment;

			return $result;
		}
		function lv_str_replace_end(string $search, string $replace, string $subject)
		{
			$search=(string)$search;

			if($search === '')
				return $subject;

			if(lv_str_ends_with($subject, $search))
				return lv_str_replace_last($search, $replace, $subject);

			return $subject;
		}
		function lv_str_replace_start(string $search, string $replace, string $subject)
		{
			$search=(string)$search;

			if($search === '')
				return $subject;

			if(lv_str_starts_with($subject, $search))
				return lv_str_replace_first($search, $replace, $subject);

			return $subject;
		}
		function lv_str_slug($title, $separator='-', array $dictionary=['@' => 'at'])
		{
			$replacements=[];

			foreach($dictionary as $key=>$value)
				$replacements['/\b('.$key.')\b/i']=$value;;

			return sgmurphy_url_slug(
				$title,
				[
					'delimiter'=>$separator,
					'replacements'=>$replacements,
					'transliterate'=>true
				]
			);
		}
		function lv_str_starts_with(string $haystack, $needles)
		{
			if(!is_iterable($needles))
				$needles=[$needles];

			foreach($needles as $needle)
				if(
					((string)$needle !== '') &&
					str_starts_with($haystack, $needle)
				)
					return true;

			return false;
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
			function lv_str_contains(string $haystack, $needles, bool $ignore_case=false)
			{
				if($ignore_case)
					$haystack=mb_strtolower($haystack);

				if(!is_iterable($needles))
					$needles=(array)$needles;

				foreach($needles as $needle)
				{
					if($ignore_case)
						$needle=mb_strtolower($needle);

					if(($needle !== '') && str_contains($haystack, $needle))
						return true;
				}

				return false;
			}
			function lv_str_reverse(string $value)
			{
				return implode(array_reverse(mb_str_split($value)));
			}
		}
		else /* some boilerplate */
		{
			function lv_str_contains()
			{
				throw new lv_hlp_exception('mbstring extension is not loaded');
			}
			function lv_str_reverse()
			{
				throw new lv_hlp_exception('mbstring extension is not loaded');
			}
		}

	// array helpers
		function lv_arr_is_assoc(array $array)
		{
			return (!array_is_list($array));
		}
		function lv_arr_is_list(array $array)
		{
			return array_is_list($array);
		}
		function lv_arr_sort_recursive(array $array, int $options=SORT_REGULAR, bool $descending=false)
		{
			foreach($array as &$value)
				if(is_array($value))
					$value=(__METHOD__)($value, $options, $descending);

			if(!array_is_list($array))
			{
				if($descending)
					krsort($array, $options);
				else
					ksort($array, $options);
			}
			else
				if($descending)
					rsort($array, $options);
				else
					sort($array, $options);

			return $array;
		}
		function lv_arr_sort_recursive_desc($array, $options=SORT_REGULAR)
		{
			return lv_arr_sort_recursive($array, $options, true);
		}
		function lv_arr_to_css_styles(array $array)
		{
			$style_list=lv_arr_wrap($array);
			$styles=[];

			foreach($style_list as $class=>$constraint)
				if(is_numeric($class))
					$styles[]=lv_str_finish($constraint, ';');
				else if($constraint)
					$styles[]=lv_str_finish($class, ';');

			return implode(' ', $styles);
		}
		function lv_hlp_collect($value=[])
		{
			return new lv_hlp_collection($value);
		}
		function lv_hlp_is_assoc($array)
		{
			return lv_arr_is_assoc($array);
		}
		function lv_hlp_is_list($array)
		{
			return lv_arr_is_list($array);
		}
		function lv_hlp_sort(array $array, $callback=null)
		{
			return lv_hlp_collection::make($array)->sort_by($callback)->all();
		}
		function lv_hlp_sort_desc(array $array, $callback=null)
		{
			return lv_hlp_collection::make($array)->sort_by_desc($callback)->all();
		}
		function lv_hlp_sort_recursive($array, $options=SORT_REGULAR, $descending=false)
		{
			return lv_arr_sort_recursive($array, $options, $descending);
		}
		function lv_hlp_sort_recursive_desc($array, $options=SORT_REGULAR)
		{
			return lv_arr_sort_recursive_desc($array, $options);
		}
		function lv_hlp_lazy_collect($value=[])
		{
			return new lv_hlp_lazy_collection($value);
		}
		function lv_hlp_to_css_styles($array)
		{
			return lv_arr_to_css_styles($array);
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
				if(is_array($type))
					$allowed_types=$type;
				else
					$allowed_types=[$type];

				return $this->each(function($item) use($allowed_types){
					$item_type=get_debug_type($item);

					foreach($allowed_types as $allowed_type)
						if(($item_type === $allowed_type) || ($item instanceof $allowed_type))
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