<?php
	/*
	 * Default http headers
	 * for controllers using views
	 */

	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');
?>