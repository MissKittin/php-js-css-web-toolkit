<?php
	function strip_php_comments($source)
	{
		/*
		 * Remove comments from PHP source
		 * Requires tokenizer extension
		 *
		 * Usage: strip_php_comments(file_get_contents('file.php'))
		 *
		 * Source: https://stackoverflow.com/questions/503871/best-way-to-automatically-remove-comments-from-php-code
		 */

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
?>