<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');
	http_response_code(404);

	include './app/views/samples/404.html';
?>