<?php
	$__init_module_path=__DIR__.'/../../lib';
	if(file_exists(__DIR__.'/lib'))
		$__init_module_path=__DIR__.'/lib';

	if(!function_exists('check_post'))
		include $__init_module_path.'/check_var.php';
	if(!function_exists('csrf_check_token'))
		include $__init_module_path.'/sec_csrf.php';

	unset($__init_module_path);
?>