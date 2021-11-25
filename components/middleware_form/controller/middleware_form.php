<?php
	$middleware_form_is_form_sent=true;

	if(!isset($middleware_form_config))
		include __DIR__.'/../config/middleware_form_config.php';
	if(!isset($view['form_fields']))
		include __DIR__.'/../config/middleware_form_fields.php';

	if((!csrf_check_token('post')) || (check_post('middleware_form') === null))
	{
		include __DIR__.'/../view/form.php';
		$middleware_form_is_form_sent=false;
	}

	unset($middleware_form_config);
?>