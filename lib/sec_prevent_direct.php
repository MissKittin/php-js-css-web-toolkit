<?php
	/*
	 * This library is from webadmin and simpleblog projects
	 * that have all scripts placed in the public directory.
	 * Scripts/directories that should not be called/viewed directly are thus protected.
	 * Mainly for historical purposes. You shouldn't be using this.
	 */

	function prevent_index($redirect_page_content=null, $redirect_page_content_type=null)
	{
		/*
		 * index.php 404 not found
		 * Create one index.php and softlink it to another directories
		 *
		 * Usage:
			include '../../lib/sec_prevent_direct.php';
			prevent_index('path/to/404.html', 'file');
		 * where
		 *  'path/to/404.html' is flat file that will be printed
		 *   can be php script with $redirect_page_content_type='include'
		 *   or string with $redirect_page_content_type='echo'
		 *  'file' is $redirect_page_content_type
		 */

		http_response_code(404);
		switch($redirect_page_content_type)
		{
			case 'echo': echo $redirect_page_content; break;
			case 'file': readfile($redirect_page_content); break;
			case 'include': include $redirect_page_content; break;
		}
	}
	function prevent_direct($script_name, $redirect_page_content=null, $redirect_page_content_type=null, $log_callback=null)
	{
		/*
		 * Extension for prevent_index()
		 * Fake 404 if script is called directly from address bar
		 *
		 * Usage:
		 *  add at the beginning
				<?php
					if(!function_exists('prevent_direct'))
						include '../lib/sec_prevent_direct.php';

					prevent_direct(basename(__FILE__), 'path/to/404.html', 'file', function($ip, $url){
						error_log('prevent_direct: denied '.$url.' from '.$ip);
					});
				?>
		 *  or one liner
		 *   <?php if(!function_exists('prevent_direct')) include '../lib/sec_prevent_direct.php'; prevent_direct(basename(__FILE__), 'path/to/404.html', 'file', function($ip, $url){ error_log('prevent_direct: denied '.$url.' from '.$ip); }); ?>
		 *  where:
		 *   'path/to/404.html' and 'file' in prevent_direct() are for prevent_index()
		 *    basename(__FILE__) can be changed to 'script-name.php'
		 *   last parameter is callback for logging
		 */

		$strtok=strtok($_SERVER['REQUEST_URI'], '?');
		if(substr($strtok, strrpos($strtok, '/')) === '/'.$script_name)
		{
			if($log_callback !== null)
				$log_callback($_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
			prevent_index($redirect_page_content, $redirect_page_content_type);
			exit();
		}
	}
?>