<?php
	if(!class_exists('registry'))
	{
		if(file_exists(__DIR__.'/lib/registry.php'))
			require __DIR__.'/lib/registry.php';
		else if(file_exists(__DIR__.'/../../lib/registry.php'))
			require __DIR__.'/../../lib/registry.php';
		else
			throw new Exception('registry.php library not found');
	}

	class admin_panel extends registry
	{
		protected static $return_content='';

		protected $base_url;
		protected $modules=[];
		protected $default_module=null;
		protected $registered_urls=[];

		public function __construct(array $params)
		{
			if(!isset($_SERVER['REQUEST_URI']))
				throw new Exception('$_SERVER["REQUEST_URI"] is not set');

			if(!isset($params['base_url']))
				throw new Exception('The base_url parameter was not specified for the constructor');
			$this->base_url=$params['base_url'];

			$this->_set_default_labels();

			if(
				isset($params['show_logout_button']) &&
				($params['show_logout_button'] === true)
			)
				$this->registry['_show_logout_button']=true;

			$this->registry['_assets_path']='';
			if(isset($params['assets_path']))
				$this->registry['_assets_path']=$params['assets_path'];

			if(isset($params['csrf_token']))
				$this->registry['_csrf_token']=[
					'name'=>$params['csrf_token'][0],
					'value'=>$params['csrf_token'][1]
				];

			if(
				isset($this->registry['_show_logout_button']) &&
				(!isset($this->registry['_csrf_token']))
			)
				throw new Exception('The CSRF token has not been set');
		}

		protected function _list_modules()
		{
			$modules=[];

			foreach($this->modules as $module_id=>$module_params)
				if(isset($module_params['name']))
				{
					if(isset($module_params['path']))
						$modules[$module_params['name']]=$this->base_url.'/'.$module_params['url'];
					else
						$modules[$module_params['name']]=$module_params['url'];
				}

			return $modules;
		}
		protected function _set_default_labels()
		{
			require __DIR__.'/views/csp_header.php';

			$this
				->set_title('Administration')
				->set_menu_button_label('Menu')
				->set_panel_label('Administration')
				->set_logout_button_label('Logout');
		}
		protected function _view($_module)
		{
			if(isset($_module['config']))
				require $_module['path'].'/'.$_module['config'];

			require __DIR__.'/views/top.php';
			require $_module['path'].'/'.$_module['script'];
			require __DIR__.'/views/bottom.php';
		}

		public function add_module(array $params)
		{
			foreach(['id', 'path', 'script', 'url'] as $param)
				if(!isset($params[$param]))
					throw new Exception('The '.$param.' parameter was not specified for the add_module');

			foreach(['_args', '_is_default', '_not_found'] as $reserved_param)
				if(isset($params[$reserved_param]))
					throw new Exception('The '.$reserved_param.' parameter is reserved');

			if(isset($this->modules[$params['id']]))
				throw new Exception('Module with id '.$params['id'].' is already registered');

			$params['path']=realpath($params['path']);
			if($params['path'] === false)
				throw new Exception('Module path does not exists');

			if(isset($params['config']))
				if(!file_exists($params['path'].'/'.$params['config']))
					throw new Exception($params['path'].'/'.$params['config'].' does not exists');

			if(!file_exists($params['path'].'/'.$params['script']))
				throw new Exception($params['path'].'/'.$params['script'].' does not exists');

			if(isset($this->registered_urls[$params['url']]))
				throw new Exception('URL '.$params['url'].' is already in use');

			$this->modules[$params['id']]=$params;
			$this->registered_urls[$params['url']]=$params['id'];

			return $this;
		}
		public function remove_module(string $module_id)
		{
			if(!isset($this->modules[$module_id]))
				throw new Exception('Module with id '.$params['id'].' is not registered');

			unset($this->registered_urls[$this->modules[$module_id]['url']]);
			unset($this->modules[$module_id]);

			return $this;
		}
		public function set_default_module(string $module_id)
		{
			$this->default_module=$module_id;
			return $this;
		}
		public function add_menu_entry(array $params)
		{
			foreach(['id', 'url', 'name'] as $param)
				if(!isset($params[$param]))
					throw new Exception('The '.$param.' parameter was not specified for the add_menu_entry');

			if(isset($this->modules[$params['id']]))
				throw new Exception('Module with id '.$params['id'].' is already registered');

			$this->modules[$params['id']]=$params;

			return $this;
		}

		public function is_module_registered(string $module_id)
		{
			return isset($this->modules[$module_id]);
		}
		public function is_url_registered(string $module_url)
		{
			return isset($this->registered_urls[$module_url]);
		}
		public function is_default_module_registered()
		{
			if($this->default_module === null)
				return false;

			return true;
		}

		public function run(bool $return_content=false)
		{
			if($this->default_module === null)
				throw new Exception('Default module is not defined');

			if(!isset($this->modules[$this->default_module]))
				throw new Exception('Default module is not registered');

			$current_url=substr(
				strtok($_SERVER['REQUEST_URI'], '?'),
				strlen($this->base_url)+1
			);

			$current_module=strtok($current_url, '/');
			if($current_module[-1] === '/')
				$current_module=substr($current_module, 0, -1);
			$current_module=trim($current_module);

			if($current_module === '')
			{
				$current_module=$this->modules[$this->default_module]['url'];
				$this->modules[$this->default_module]['_is_default']=true;
			}

			foreach($this->modules as $module_id=>$module_params)
				if(isset($module_params['path']) && ($current_module === $module_params['url']))
				{
					$current_module_id=$module_id;
					break;
				}

			if(isset($current_module_id))
			{
				$module_params=$this->modules[$current_module_id];
				$module_params['_args']=explode('/', substr($current_url, strlen($module_params['url'])+1));
			}
			else
			{
				$module_params=$this->modules[$this->default_module];
				$module_params['_args']=[''];
				$module_params['_not_found']=true;
			}
			$module_params['url']=$this->base_url.'/'.$module_params['url'];

			if($return_content)
				ob_start(function($content){
					static::$return_content.=$content;
					return $content;
				});

			$this->_view($module_params);

			if($return_content)
			{
				ob_end_clean();

				$return_content=static::$return_content;
				static::$return_content='';

				return $return_content;
			}
		}

		protected function set_lang(string $lang)
		{
			$this->registry['_lang']=$lang;
			return $this;
		}
		protected function set_title(string $title)
		{
			$this->registry['_title']=$title;
			return $this;
		}
		protected function add_csp_header(string $section, string $value)
		{
			$this->registry['_csp_header'][$section][]=$value;
			return $this;
		}
		public function add_style_header(string $path)
		{
			$this->registry['_styles'][]=$path;
			return $this;
		}
		public function add_script_header(string $path)
		{
			$this->registry['_scripts'][]=$path;
			return $this;
		}
		protected function add_html_header(string $header)
		{
			if(!isset($this->registry['_html_headers']))
				$this->registry['_html_headers']='';

			$this->registry['_html_headers'].=$header;

			return $this;
		}

		public function set_menu_button_label(string $label)
		{
			$this->registry['_menu_button_label']=$label;
			return $this;
		}
		public function set_panel_label(string $label)
		{
			$this->registry['_panel_label']=$label;
			return $this;
		}
		public function set_logout_button_label(string $label)
		{
			$this->registry['_logout_button_label']=$label;
			return $this;
		}
	}
?>