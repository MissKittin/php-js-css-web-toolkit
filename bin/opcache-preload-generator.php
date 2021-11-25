<?php
	/*
	 * Opcache preload script generator
	 *
	 * Usage:
	 *  dump output of this command to the file, eg:
	 *   php ./bin/opcache-preload-generator.php > ./tmp/app-preload.php
	 *  be sure that ./tmp directory exists
	 *  and add ./tmp/app-preload.php to the opcache.preload config entry
	 *   use absolute path!
	 *  you can add --debug parameter - additional info will be printed to stderr
	 *
	 * Configuration: create opcache-preload-generator.config.php in the app directory
	 *  and define the blacklist, eg:
		<?php
			$blacklist[]='../app/opcache-preload-generator.config.php';
			$blacklist[]='../app/assets';
			$blacklist[]='../app/databases';
			$blacklist[]='../app/views/samples/default/default.js';
		?>
	 *
	 * Required libraries:
	 *  check_var.php
	 *  strip_php_comments.php
	 */

	chdir(__DIR__.'/..');

	include './lib/check_var.php';
	include './lib/strip_php_comments.php';

	$blacklist=array();
	@include './app/opcache-preload-generator.config.php';

	if(check_argv('--debug'))
	{
		function _debug($message)
		{
			fwrite(STDERR, ' /* '.$message.' */ '.PHP_EOL);
		}
	}
	else
	{
		function _debug(){}
	}
	function is_in_blacklist($file_name, $blacklist)
	{
		foreach($blacklist as $blacklist_item)
			if(strpos($file_name, $blacklist_item) === 0)
			{
				_debug($file_name.' is blacklisted');
				return true;
			}
		return false;
	}
	function scan_for_includes($file, $blacklist)
	{
		if(!is_in_blacklist('.'.$file, $blacklist))
		{
			$source=strip_php_comments(file_get_contents($file));
			preg_match_all('/(include|include_once|require|require_once) *\(? *[\'"](.*?)[\'"] *\)? *;/', $source, $output);
			if(isset($output[2]))
				foreach($output[2] as $include_file)
					if(strtolower(substr($include_file, strrpos($include_file, '.')+1)) === 'php')
					{
						_debug('[:] scanning '.$include_file);
						add_to_list($include_file);
						scan_for_includes($include_file, $blacklist);
						_debug('[:] ended '.$include_file);
					}
		}
	}
	$add_to_list__already_added=array(); function add_to_list($file)
	{
		global $add_to_list__already_added;
		if(!in_array($file, $add_to_list__already_added))
		{
			_debug('adding '.$file);
			echo 'opcache_compile_file(\'.'.$file.'\');'.PHP_EOL;
			$add_to_list__already_added[]=$file;
		}
		else
			_debug($file.' already added');
	}

	echo '<?php'.PHP_EOL;
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./app')) as $file)
		if(strtolower($file->getExtension()) === 'php')
		{
			if(!is_in_blacklist('.'.$file, $blacklist))
			{
				$file=$file->getPathname();
				_debug('[1] scanning '.$file);
				add_to_list($file);
				scan_for_includes($file, $blacklist);
				_debug('[1] ended '.$file);
			}
		}
	echo '?>';
?>