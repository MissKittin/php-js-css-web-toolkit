<?php
	function wp_is_serialized(string $data, bool $strict=true)
	{
		/*
		 * Checks value to find if it was serialized
		 *
		 * Usage:
		 *  if(wp_is_serialized($data)) // strictly follow the end of the chain
		 * or
		 *  if(wp_is_serialized($data, false))
		 *
		 * Source: https://developer.wordpress.org/reference/functions/is_serialized/
		 * License: GNU GPL2 https://github.com/WordPress/wordpress-develop/blob/6.6/src/license.txt
		 */

		$data=trim($data);

		if($data === 'N;')
			return true;

		if(!isset($data[3])) // (strlen($data) < 4)
			return false;

		if($data[1] !== ':')
			return false;

		if($strict)
		{
			$lastc=substr($data, -1);

			if(($lastc !== ';') && ($lastc !== '}'))
				return false;
		}
		else
		{
			$semicolon=strpos($data, ';');
			$brace=strpos($data, '}');

			if(($semicolon === false) && ($brace === false)) // either ; or } must exist
				return false;

			// but neither must be in the first X characters
				if(($semicolon !== false) && ($semicolon < 3))
					return false;

				if(($brace !== false) && ($brace < 4))
					return false;
		}

		$token=$data[0];

		switch($token)
		{
			case 's':
				if($strict)
				{
					if(substr($data, -2, 1) !== '"')
						return false;
				}
				else if(strpos($data, '"') === false)
					return false;
			case 'a':
			case 'O':
			case 'E':
				return (bool)preg_match('/^'.$token.':[0-9]+:/s', $data); // 0 || false => false
			case 'b':
			case 'i':
			case 'd':
				$end='';

				if($strict)
					$end='$';

				return (bool)preg_match('/^'.$token.':[0-9.E+-]+;'.$end.'/', $data); // 0 || false => false
		}

		return false;
	}
?>