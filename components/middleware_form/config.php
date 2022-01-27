<?php
	$view=[
		'lang'=>'en',
		'title'=>'Middleware form',
		'assets_path'=>'/assets',
		'middleware_form_style'=>'middleware_form_dark.css',
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
?>