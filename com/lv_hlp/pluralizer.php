<?php
	if(!class_exists('lv_hlp_exception'))
		require __DIR__.'/bootstrap.php';

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
?>