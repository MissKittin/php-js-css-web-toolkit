<?php
	function strip_php_comments(string $source)
	{
		/*
		 * Remove comments from PHP source
		 *
		 * Warning:
		 *  tokenizer extension is required
		 *
		 * Usage:
		 *  strip_php_comments(file_get_contents('file.php'))
		 *
		 * Source:
		 *  https://stackoverflow.com/questions/503871/best-way-to-automatically-remove-comments-from-php-code
		 */

		if(!extension_loaded('tokenizer'))
			throw new Exception('tokenizer extension is not loaded');

		$output_string='';

		$comment_tokens=[T_COMMENT];
		if(defined('T_DOC_COMMENT'))
			$comment_tokens[]=T_DOC_COMMENT;
		if(defined('T_ML_COMMENT'))
			$comment_tokens[]=T_ML_COMMENT;

		foreach(token_get_all($source) as $token)
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