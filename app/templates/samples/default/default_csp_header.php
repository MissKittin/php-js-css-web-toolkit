<?php
	// Common CSP policy for default template

	$view['csp_header']=[
		'default-src'=>['\'none\''],
		'script-src'=>['\'self\''],
		'connect-src'=>['\'self\''],
		'img-src'=>['\'self\''],
		'style-src'=>['\'self\''],
		'base-uri'=>['\'self\''],
		'form-action'=>['\'self\'']
	];
?>