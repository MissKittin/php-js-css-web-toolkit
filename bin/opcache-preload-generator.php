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
	 *
	 * Configuration: create opcache-preload-generator.config.php in the app directory
	 *  and define the blacklist, eg:
		<?php
			$blacklist[]='../app/opcache-preload-generator.config.php';
			$blacklist[]='../app/assets';
			$blacklist[]='../app/databases';
			$blacklist[]='../app/views/samples/default/default.js';
		?>
	 */

	chdir(__DIR__.'/..');

	$blacklist=array();
	@include './app/opcache-preload-generator.config.php';

	function strip_comments($source)
	{
		// https://stackoverflow.com/questions/503871/best-way-to-automatically-remove-comments-from-php-code
		// this function requires tokenizer extension

		$output_string='';

		$comment_tokens=array(T_COMMENT);
		if(defined('T_DOC_COMMENT'))
			$comment_tokens[]=T_DOC_COMMENT;
		if(defined('T_ML_COMMENT'))
			$comment_tokens[]=T_ML_COMMENT;

		$tokens=token_get_all($source);
		foreach($tokens as $token)
		{    
			if(is_array($token))
			{
				if(in_array($token[0], $comment_tokens))
					continue;
				$token=$token[1];
			}
			$output_string.=$token;
		}

		return $output_string;	
	}
	function is_in_blacklist($file_name, $blacklist)
	{
		foreach($blacklist as $blacklist_item)
			if(strpos($file_name, $blacklist_item) === 0)
				return true;
		return false;
	}
	function scan_for_includes($file, $blacklist)
	{
		if(!is_in_blacklist('.'.$file, $blacklist))
		{
			$source=strip_comments(file_get_contents($file));
			preg_match_all('/(include|include_once|require|require_once) *\(? *[\'"](.*?)[\'"] *\)? *;/', $source, $output);
			if(isset($output[2]))
				foreach($output[2] as $include_file)
					if(strtolower(substr($include_file, strrpos($include_file, '.')+1)) === 'php')
					{
						add_to_list($include_file);
						scan_for_includes($include_file, $blacklist);
					}
		}
	}
	function add_to_list($file)
	{
		global $add_to_list__already_added;
		if(!isset($add_to_list__already_added))
			$add_to_list__already_added=array();

		if(!in_array($file, $add_to_list__already_added))
		{
			echo 'opcache_compile_file(\'.'.$file.'\');'.PHP_EOL;
			$add_to_list__already_added[]=$file;
		}
	}

	echo '<?php'.PHP_EOL;
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./app')) as $file)
		if(strtolower($file->getExtension()) === 'php')
			if(!is_in_blacklist('.'.$file, $blacklist))
			{
				$file=$file->getPathname();
				add_to_list($file);
				scan_for_includes($file, $blacklist);
			}
	echo '?>';
?>