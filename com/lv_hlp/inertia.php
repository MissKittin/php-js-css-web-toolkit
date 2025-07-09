<?php
	if(!class_exists('lv_hlp_exception'))
		require __DIR__.'/bootstrap.php';

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