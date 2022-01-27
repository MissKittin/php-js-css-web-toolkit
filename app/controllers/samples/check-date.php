<?php
	include './app/shared/samples/default_http_headers.php';

	include './app/shared/samples/ob_cache.php';
	ob_cache(ob_url2file(), 3600);

	include './lib/check_date.php';

	include './app/templates/samples/default/default_template.php';
	$view=new default_template();

	$view['first-question']=false;
	$view['second-question']=false;
	if(check_date(23,6, 12,8))
		$view['first-question']=true;
	if(check_date(14,9, 23,4))
		$view['second-question']=true;

	$view->view('./app/views/samples/check-date');
?>