<?php
	echo ' -> Mocking functions';
		function csrf_check_token()
		{
			return $GLOBALS['mock_csrf_check_token'];
		}
		function check_post()
		{
			return $GLOBALS['mock_check_post'];
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including main.php';
		if(@(include __DIR__.'/../main.php') === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Mocking middleware_form class';
		class middleware_form_test extends middleware_form
		{
			protected function load_function($a) {}

			public function parse_fields($a=null)
			{
				ob_start();
				parent::{__FUNCTION__}($this->registry);
				return ob_get_clean();
			}
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing is_form_sent'.PHP_EOL;
		echo '  -> returns false';
			$GLOBALS['mock_csrf_check_token']=false;
			$GLOBALS['mock_check_post']=null;
			$form=new middleware_form_test();
			if(!$form->is_form_sent())
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$GLOBALS['mock_csrf_check_token']=true;
			$GLOBALS['mock_check_post']=null;
			$form=new middleware_form_test();
			if(!$form->is_form_sent())
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$GLOBALS['mock_csrf_check_token']=false;
			$GLOBALS['mock_check_post']='submit_button';
			$form=new middleware_form_test();
			if(!$form->is_form_sent())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> returns true';
			$GLOBALS['mock_csrf_check_token']=true;
			$GLOBALS['mock_check_post']='submit_button';
			$form=new middleware_form_test();
			if($form->is_form_sent())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing parse_fields (default template)';
		$GLOBALS['mock_csrf_check_token']=false;
		$GLOBALS['mock_check_post']=null;
		$form=new middleware_form_test();
		$form
		->	add_field([
				'tag'=>null,
				'content'=>'Plain text'
			])
		->	add_field([
				'tag'=>'input',
				'type'=>'text',
				'name'=>'text_box',
				'placeholder'=>'Text'
			])
		->	add_field([
				'tag'=>'input',
				'type'=>'password',
				'name'=>'new_password',
				'placeholder'=>'New password'
			])
		->	add_field([
				'tag'=>'input',
				'type'=>'checkbox',
				'name'=>'checkbox_name',
				'value'=>'checkbox_value'
			])
		->	add_field([
				'tag'=>'input',
				'type'=>'slider',
				'slider_label'=>'Slider label'
			]);
		if($form->parse_fields() === 'Plain text<div class="input_text"><input type="text" name="text_box" placeholder="Text"></div><div class="input_text"><input type="password" name="new_password" placeholder="New password"></div><div class="input_text"><input type="checkbox" name="checkbox_name" value="checkbox_value"></div><div class="input_checkbox"><label class="switch"><input type="checkbox"><span class="slider"></span></label><div class="input_checkbox_text">Slider label</div></div>')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>