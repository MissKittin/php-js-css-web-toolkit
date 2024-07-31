<?php
	class middleware_form_exception extends Exception {}
	class middleware_form
	{
		protected $is_form_sent=true;
		protected $registry=[];
		protected $template;
		protected $templates_dir;

		public function __construct(
			string $template='default',
			string $templates_dir=__DIR__.'/templates'
		){
			$this->load_function([
				'check_var.php'=>'check_post',
				'sec_csrf.php'=>'csrf_check_token'
			]);

			if(!is_dir($templates_dir))
				throw new middleware_form_exception($templates_dir.' is not a directory');

			if(!file_exists($templates_dir.'/'.$template))
				throw new middleware_form_exception('The '.$template.' template does not exist');

			$this->template=$template;
			$this->templates_dir=realpath($templates_dir);
			$this->setup_registry();

			if((!csrf_check_token('post')) || (check_post('middleware_form') === null))
				$this->is_form_sent=false;
		}

		protected function load_function($libraries)
		{
			foreach($libraries as $library_file=>$library_func)
				if(!function_exists($library_func))
				{
					if(file_exists(__DIR__.'/lib/'.$library_file))
						require __DIR__.'/lib/'.$library_file;
					else if(file_exists(__DIR__.'/../../lib/'.$library_file))
						require __DIR__.'/../../lib/'.$library_file;
					else
						throw new middleware_form_exception('Library '.$library_file.' not found');
				}
		}
		protected function parse_fields($view)
		{
			require $this->templates_dir.'/'.$this->template.'/parse_fields.php';
		}
		protected function setup_registry()
		{
			$this->registry=[
				'lang'=>'en',
				'title'=>'Middleware form',
				'assets_path'=>'/assets',
				'middleware_form_style'=>'middleware_form_default_bright.css',
				'inline_style'=>false,
				'favicon'=>null,
				'submit_button_label'=>'Next',
				'csp_header'=>[
					'default-src'=>['\'none\''],
					'script-src'=>['\'self\''],
					'connect-src'=>['\'self\''],
					'img-src'=>['\'self\''],
					'style-src'=>['\'self\''],
					'base-uri'=>['\'self\''],
					'form-action'=>['\'self\'']
				]
			];

			if($this->template === 'materialized')
				$this->registry['middleware_form_style']='middleware_form_materialized.css';
		}

		public function add_field(array $field)
		{
			$this->registry['form_fields'][]=$field;
			return $this;
		}
		public function add_config(string $key, $value)
		{
			$this->registry[$key]=$value;
			return $this;
		}
		public function add_csp_header(string $section, string $value)
		{
			$this->registry['csp_header'][$section][]=$value;
			return $this;
		}
		public function add_html_header(string $header)
		{
			if(!isset($this->registry['html_headers']))
				$this->registry['html_headers']='';

			$this->registry['html_headers'].=$header;

			return $this;
		}
		public function add_error_message(string $message=null)
		{
			if($message === null)
				unset($this->registry['error_message']);
			else
				$this->registry['error_message']=$message;

			return $this;
		}
		public function is_form_sent()
		{
			return $this->is_form_sent;
		}
		public function view()
		{
			if($this->registry['inline_style'])
				$this->registry['csp_header']['style-src'][]='\'nonce-mainstyle\'';

			$view=$this->registry;

			if(
				($view['favicon'] !== null) &&
				(!file_exists($view['favicon']))
			)
				throw new middleware_form_exception($view['favicon'].' does not exist');

			require $this->templates_dir.'/'.$this->template.'/view.php';
		}
	}
?>