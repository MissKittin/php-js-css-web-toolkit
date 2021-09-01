<?php
	// import required libraries
	if(!function_exists('is_logged')) include './lib/login.php';
	if(!function_exists('csrf_check_token')) include './lib/sec_csrf.php';
	if(!function_exists('check_post')) include './lib/check_var.php';
?>