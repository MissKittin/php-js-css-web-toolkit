<?php
	if(!class_exists('lv_hlp_exception'))
		require __DIR__.'/bootstrap.php';

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
?>