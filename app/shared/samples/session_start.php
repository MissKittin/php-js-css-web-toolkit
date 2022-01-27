<?php
	/*
	 * This part of the application checks whether
	 * the sec_lv_encrypter.php library can be used
	 * to store session data in a cookie
	 * If not, it starts the session with the SessionHandler
	 *
	 * Warning:
	 *  this code was created for demonstration purposes
	 *  and should not be used in production
	 */

	if(
		extension_loaded('openssl') &&
		extension_loaded('mbstring') &&
		(OPENSSL_VERSION_NUMBER >= 269484159)
	){
		if(!class_exists('lv_cookie_session_handler'))
			include './lib/sec_lv_encrypter.php';

		if(!file_exists('./var/lib/app_key'))
		{
			@mkdir('./var');
			@mkdir('./var/lib');
			file_put_contents('./var/lib/app_key', lv_encrypter::generate_key('aes-256-gcm'));
		}

		lv_cookie_session_handler::register_handler([
			'key'=>file_get_contents('./var/lib/app_key'),
			'cipher'=>'aes-256-gcm'
		]);
		lv_cookie_session_handler::session_start();
	}
	else
	{
		session_name('id');
		session_start();
	}
?>