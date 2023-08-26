<?php
	class middleware_form
	{
		protected $is_form_sent=true;
		protected $registry=[];

		public function __construct()
		{
			$this->load_function([
				'check_var.php'=>'check_post',
				'sec_csrf.php'=>'csrf_check_token'
			]);

			require __DIR__.'/config.php';
			$this->registry=$view;

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
						throw new Exception('Library '.$library_file.' not found');
				}
		}

		protected function parse_fields($view)
		{
			foreach($view['form_fields'] as $form_field)
				if($form_field['tag'] === null)
					echo $form_field['content'];
				else
				{
					if(
						isset($form_field['type']) &&
						($form_field['type'] === 'slider')
					){
						$form_field['type']='checkbox';
						$slider_label=$form_field['slider_label'];
						unset($form_field['slider_label']);
						unset($form_field['tag']);

						echo ''
						.	'<div class="input_checkbox">'
						.		'<label class="switch">'
						;

								echo '<input';
								foreach($form_field as $parameter_name=>$parameter_value)
									echo ' '
									.	$parameter_name
									.	'='
									.	'"'.$parameter_value.'"'
									;
								echo '>';

						echo ''
						.			'<span class="slider"></span>'
						.		'</label>'
						.		'<div class="input_checkbox_text">'
						.			$slider_label
						.		'</div>'
						.	'</div>'
						;

						unset($slider_label);
					}
					else if(
						isset($form_field['type']) &&
						isset($form_field['label']) &&
						(
							($form_field['type'] === 'checkbox') ||
							($form_field['type'] === 'radio')
						)
					){
						$label=$form_field['label'];
						unset($form_field['label']);

						echo ''
						.	'<div class="input_checkbox">'
						.		'<'.$form_field['tag']
						;
						unset($form_field['tag']);

						foreach($form_field as $parameter_name=>$parameter_value)
							if($parameter_value === null)
								echo ' '.$parameter_name;
							else
								echo ' '.$parameter_name.'="'.$parameter_value.'"';

						echo '>'
						.		'<label>'.$label.'</label>'
						.	'</div>'
						;
					}
					else
					{
						echo ''
						.	'<div class="input_text">'
						.	'<'.$form_field['tag']
						;
						unset($form_field['tag']);

						foreach($form_field as $parameter_name=>$parameter_value)
							if($parameter_value === null)
								echo ' '.$parameter_name;
							else
								echo ' '.$parameter_name.'="'.$parameter_value.'"';

						echo '>'
						.	'</div>'
						;
					}
				}
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
			require __DIR__.'/view.php';
		}
	}
?>