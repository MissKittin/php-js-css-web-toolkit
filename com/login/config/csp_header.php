<?php
	$GLOBALS['_login']['csp_header']['default-src'][]='\'none\'';
	$GLOBALS['_login']['csp_header']['script-src'][]='\'self\'';
	$GLOBALS['_login']['csp_header']['connect-src'][]='\'self\'';
	$GLOBALS['_login']['csp_header']['img-src'][]='\'self\'';
	$GLOBALS['_login']['csp_header']['style-src'][]='\'self\'';
	$GLOBALS['_login']['csp_header']['base-uri'][]='\'self\'';
	$GLOBALS['_login']['csp_header']['form-action'][]='\'self\'';

	if($GLOBALS['_login']['view']['inline_style'])
		$GLOBALS['_login']['csp_header']['style-src'][]='\'nonce-mainstyle\'';
?>