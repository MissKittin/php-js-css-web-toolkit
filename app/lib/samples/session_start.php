<?php
	/*
	 * This part of the application checks whether
	 * the sec_lv_encrypter.php library can be used
	 * to store session data in a cookie
	 * If not, it starts the session with the SessionHandler
	 *
	 * Warning:
	 *  this code was created for demonstration purposes
	 *   and should not be used in production
	 *  $_SESSION_CLEAN_TOOL is reserved for session-clean.php tool
	 *
	 * See:
	 *  bin/session-clean.php
	 *  controllers/samples/database-test.php
	 *  controllers/samples/login-component-test.php
	 *  controllers/samples/login-library-test.php
	 */

	if(isset($_SESSION_CLEAN_TOOL))
		// set vartiable for session-clean.php tool
		$_sessions_dir=__DIR__.'/../../../var/lib/sessions';

	else if(file_exists(__DIR__.'/../../../var/lib/session_cookie_key'))
	{
		// quick boot up (default behavior)

		if(!class_exists('lv_cookie_session_handler'))
			require './lib/sec_lv_encrypter.php';

		lv_cookie_session_handler::register_handler([
			'key'=>file_get_contents(__DIR__.'/../../../var/lib/session_cookie_key'),
			'cipher'=>'aes-256-gcm'
		]);

		lv_cookie_session_handler::session_start();
	}
	else if(is_dir(__DIR__.'/../../../var/lib/sessions'))
	{
		// quick boot up (fallback)

		session_save_path(__DIR__.'/../../../var/lib/sessions');
		session_name('id');
		session_start();
	}

	else if(
		extension_loaded('openssl') &&
		extension_loaded('mbstring') &&
		(OPENSSL_VERSION_NUMBER >= 269484159)
	){
		if(!class_exists('lv_cookie_session_handler'))
			require './lib/sec_lv_encrypter.php';

		@mkdir(__DIR__.'/../../../var/lib', 0777, true);
		file_put_contents(__DIR__.'/../../../var/lib/session_cookie_key', lv_encrypter::generate_key('aes-256-gcm'));

		lv_cookie_session_handler::register_handler([
			'key'=>file_get_contents(__DIR__.'/../../../var/lib/session_cookie_key'),
			'cipher'=>'aes-256-gcm'
		]);

		lv_cookie_session_handler::session_start();
	}
	else
	{
		@mkdir(__DIR__.'/../../../var/lib/sessions', 0777, true);

		session_save_path(__DIR__.'/../../../var/lib/sessions');
		session_name('id');
		session_start();
	}
?>