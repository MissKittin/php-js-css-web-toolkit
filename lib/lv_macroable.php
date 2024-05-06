<?php
	/*
	 * Add extra functionality to a class
	 * that is missing in the class definition
	 * through a simple trait
	 *
	 * You can use a class or a trait
	 *
	 * Note:
	 *  throws an lv_macroable_exception on error
	 *
	 * Usage - macro:
		class my_class extends lv_macroable {}
		my_class::macro('my_method', function($arg){
			echo $arg;
		});
		my_class::my_method('arg'); // echoes 'arg'
		$my_class=new my_class();
		$my_class->my_method('arg2'); // echoes 'arg2'
		my_class::has_macro('my_method'); // true
		my_class::has_macro('nonexistent_method'); // false
		my_class::flush_macros();
		my_class::has_macro('my_method'); // false
	 *
	 * Usage - mixin:
		class my_class extends lv_macroable {}
		class my_class_macros
		{
			public function static_method()
			{
				return function($arg)
				{
					echo 'static-'.$arg;
				};
			}

			public function nonstatic_method()
			{
				return function($arg)
				{
					echo 'nonstatic-'.$arg;
				};
			}
		}
		my_class::mixin(new my_class_macros());
		my_class::static_method('arg'); // echoes 'static-arg'
		$my_class=new my_class();
		$my_class->nonstatic_method('arg2'); // echoes 'nonstatic-arg2'
	 *
	 * Source:
	 *  https://github.com/illuminate/macroable/blob/master/Traits/Macroable.php
	 * License: MIT
	 */

	trait t_lv_macroable
	{
		protected static $macros=[];

		public static function __callStatic(string $method, array $parameters)
		{
			if(!static::has_macro($method))
				throw new lv_macroable_exception(sprintf(
					'Method %s::%s does not exist',
					static::class,
					$method
				));

			$macro=static::$macros[$method];

			if($macro instanceof Closure)
				$macro=$macro->bindTo(null, static::class);

			return $macro(...$parameters);
		}

		public static function macro(string $name, $macro)
		{
			static::$macros[$name]=$macro;
		}
		public static function mixin($mixin, bool $replace=true)
		{
			// PHP 7.1 has no "object" declaration
			if(!is_object($mixin))
				throw new TypeError('Argument 1 passed to '.__METHOD__.'() must be an instance of object');

			$methods=(new ReflectionClass($mixin))->getMethods(
				ReflectionMethod::IS_PUBLIC|ReflectionMethod::IS_PROTECTED
			);

			foreach($methods as $method)
				if($replace || (!static::has_macro($method->name)))
					static::macro($method->name, $method->invoke($mixin));
		}
		public static function has_macro(string $name)
		{
			return isset(static::$macros[$name]);
		}
		public static function flush_macros()
		{
			static::$macros=[];
		}

		public function __call(string $method, array $parameters)
		{
			if(!static::has_macro($method))
				throw new lv_macroable_exception(sprintf(
					'Method %s::%s does not exist',
					static::class,
					$method
				));

			$macro=static::$macros[$method];

			if($macro instanceof Closure)
				$macro=$macro->bindTo($this, static::class);

			return $macro(...$parameters);
		}
	}

	class lv_macroable_exception extends Exception {}
	class lv_macroable
	{
		use t_lv_macroable;
	}
?>