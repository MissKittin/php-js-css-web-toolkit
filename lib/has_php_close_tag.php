<?php
	function has_php_close_tag(string $source)
	{
		/*
		 * Check if the PHP file ends with a close tag
		 *
		 * Warning:
		 *  tokenizer extension is required
		 *
		 * Usage:
		 *  if(has_php_close_tag(file_get_contents('file.php')))
		 *
		 * Source:
		 *  https://stackoverflow.com/a/38406054
		 */

		if(!extension_loaded('tokenizer'))
			throw new Exception('tokenizer extension is not loaded');

		$is_php=false;
		$return=true;

		foreach(token_get_all($source) as $token)
			if(is_array($token))
			{
				if(token_name($token[0]) === 'T_CLOSE_TAG')
					$return=true;
				elseif(token_name($token[0]) === 'T_OPEN_TAG')
				{
					$is_php=true;
					$return=false;
				}
			}

		if($is_php)
			return $return;

		return false;
	}
?>