<?php
	$this
		->add_csp_header('default-src', '\'none\'')
		->add_csp_header('script-src', '\'self\'')
		->add_csp_header('connect-src', '\'self\'')
		->add_csp_header('img-src', '\'self\'')
		->add_csp_header('style-src', '\'self\'')
		->add_csp_header('base-uri', '\'self\'')
		->add_csp_header('form-action', '\'self\'')
	;
?>