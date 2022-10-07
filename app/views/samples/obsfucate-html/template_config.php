<?php
	$view['lang']='en';
	$view['title']='HTML obsfucator';
	$view['meta_description']='Output buffer library test';
	$view['meta_robots']='index,follow';

	// inline script in page_content.php (patch)
	$view['csp_header']['script-src'][]='\'sha256-X6J7hQYXqnu888xEQ+pczWCZ840+hDTUD88HRubhjuI=\'';

	$GLOBALS['_ob_sfucator']=[
		'title'=>'Title from views/obsfucate-html/template_config.php',
		'label'=>'<h1>Enable javascript to view content</h1>'
	];
?>