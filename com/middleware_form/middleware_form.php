<?php
	class middleware_form_exception extends Exception {}
	class middleware_form
	{
		protected $is_form_sent=true;
		protected $registry=[];
		protected $template;

		public function __construct(string $template='default')
		{
			$this->load_function([
				'check_var.php'=>'check_post',
				'sec_csrf.php'=>'csrf_check_token'
			]);

			if(!file_exists(__DIR__.'/templates/'.$template))
				throw new middleware_form_exception('The '.$template.' template does not exist');

			$this->template=$template;
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
			require __DIR__.'/templates/'.$this->template.'/parse_fields.php';
		}
		protected function setup_registry()
		{
			$this->registry=[
				'lang'=>'en',
				'title'=>'Middleware form',
				'assets_path'=>'/assets',
				'middleware_form_style'=>'middleware_form_default_bright.css',
				'inline_style'=>false,
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
			$view=$this->registry;

			if($view['inline_style'])
				$view['csp_header']['style-src'][]='\'nonce-mainstyle\'';

			require __DIR__.'/templates/'.$this->template.'/view.php';
		}
	}
?>