<?php
	if(!class_exists('lv_hlp_exception'))
		require __DIR__.'/bootstrap.php';

	(function($libraries){
		foreach($libraries as $library_file=>$function_or_class)
			_lv_hlp_load_library('class', $library_file, $function_or_class);
	})([
		'lv_arr.php'=>'lv_arr_exception',
		'lv_macroable.php'=>'lv_macroable_exception'
	]);

	// array helpers
		function lv_arr_to_css_styles(array $array)
		{
			$style_list=lv_arr_wrap($array);
			$styles=[];

			_lv_hlp_load_library('function', 'lv_str.php', 'lv_str_finish');

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
?>