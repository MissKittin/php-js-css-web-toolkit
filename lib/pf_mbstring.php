<?php
	if(function_exists('mb_get_info'))
	{
		/*
		 * mb_chr() polyfill
		 *
		 * Warning:
		 *  converts string to UTF-8
		 *
		 * Source: https://github.com/symfony/polyfill-mbstring/blob/1.x/Mbstring.php
		 * License: MIT https://github.com/symfony/polyfill-mbstring/blob/1.x/LICENSE
		 */

		if(!function_exists('mb_chr'))
		{
			function mb_chr(int $code, ?string $encoding=null)
			{
				if(0x80 > $code %= 0x200000)
					$s=chr($code);
				else if(0x800 > $code)
					$s=chr(0xC0 | $code >> 6)
					.	chr(0x80 | $code & 0x3F);
				else if(0x10000 > $code)
					$s=chr(0xE0 | $code >> 12)
					.	chr(0x80 | $code >> 6 & 0x3F)
					.	chr(0x80 | $code & 0x3F);
				else
					$s=chr(0xF0 | $code >> 18)
					.	chr(0x80 | $code >> 12 & 0x3F)
					.	chr(0x80 | $code >> 6 & 0x3F)
					.	chr(0x80 | $code & 0x3F);

				if($encoding === null)
					$encoding=mb_internal_encoding();

				if(mb_internal_encoding() !== 'UTF-8')
					$s=mb_convert_encoding($s, $encoding, 'UTF-8');

				return $s;
			}
		}

		/*
		 * mb_lcfirst() polyfill
		 *
		 * Source: https://github.com/symfony/polyfill-php84/blob/1.x/Php84.php
		 * License: MIT https://github.com/symfony/polyfill-php84/blob/1.x/LICENSE
		 */

		if(!function_exists('mb_lcfirst'))
		{
			function mb_lcfirst(string $string, ?string $encoding=null)
			{
				if($encoding === null)
					$encoding=mb_internal_encoding();

				try {
					$valid_encoding=mb_check_encoding('', $encoding);
				} catch(ValueError $error) {
					throw new ValueError(sprintf(
						'mb_lcfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given',
						$encoding
					));
				}

				// BC for PHP 7.3 and lower
				if(!$valid_encoding)
					throw new ValueError(sprintf(
						'mb_lcfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given',
						$encoding
					));

				return ''
				.	mb_convert_case(
						mb_substr($string, 0, 1, $encoding),
						MB_CASE_LOWER,
						$encoding
					)
				.	mb_substr($string, 1, null, $encoding);
			}
		}

		/*
		 * mb_ord() polyfill
		 *
		 * Warning:
		 *  converts string to UTF-8
		 *
		 * Source: https://github.com/symfony/polyfill-mbstring/blob/1.x/Mbstring.php
		 * License: MIT https://github.com/symfony/polyfill-mbstring/blob/1.x/LICENSE
		 */

		if(!function_exists('mb_ord'))
		{
			function mb_ord(string $s, ?string $encoding=null)
			{
				if($encoding === null)
					$encoding=mb_internal_encoding();

				if(mb_internal_encoding() !== 'UTF-8')
					$s=mb_convert_encoding($s, 'UTF-8', $encoding);

				if(strlen($s) === 1)
					return ord($s);

				$s=unpack('C*', substr($s, 0, 4));

				if($s !== false)
					$code=$s[1];
				else
					$code=0;

				if($code >= 0xF0)
					return (
						(($code - 0xF0) << 18) +
						(($s[2] - 0x80) << 12) +
						(($s[3] - 0x80) << 6) +
						$s[4] -
						0x80
					);

				if($code >= 0xE0)
					return (
						(($code - 0xE0) << 12) +
						(($s[2] - 0x80) << 6) +
						$s[3] -
						0x80
					);

				if($code >= 0xC0)
					return (
						(($code - 0xC0) << 6) +
						$s[2] -
						0x80
					);

				return $code;
			}
		}

		/*
		 * mb_scrub() polyfill
		 *
		 * Source: https://github.com/symfony/polyfill-mbstring/blob/1.x/bootstrap.php
		 * License: MIT https://github.com/symfony/polyfill-mbstring/blob/1.x/LICENSE
		 */

		if(!function_exists('mb_scrub'))
		{
			function mb_scrub(string $string, ?string $encoding=null)
			{
				if($encoding === null)
					$encoding=mb_internal_encoding();

				return mb_convert_encoding($string, $encoding, $encoding);
			}
		}

		/*
		 * mb_str_split() polyfill
		 * based on symfony/polyfill-mbstring package
		 *
		 * Source: https://www.php.net/manual/en/function.mb-str-split.php
		 */

		if(!function_exists('mb_str_split'))
		{
			function mb_str_split(string $string, int $split_length=1, ?string $encoding=null)
			{
				if(
					($string !== null) &&
					(!is_scalar($string)) &&
					(!(
						is_object($string) &&
						method_exists($string, '__toString')
					))
				){
					trigger_error(
						'mb_str_split(): expects parameter 1 to be string, '.gettype($string).' given',
						E_USER_WARNING
					);

					return null;
				}

				if(
					($split_length !== null) &&
					(!is_bool($split_length)) &&
					(!is_numeric($split_length))
				){
					trigger_error(
						'mb_str_split(): expects parameter 2 to be int, '.gettype($split_length).' given',
						E_USER_WARNING
					);

					return null;
				}

				$split_length=(int)$split_length;

				if($split_length < 1)
				{
					trigger_error(
						'mb_str_split(): The length of each segment must be greater than zero',
						E_USER_WARNING
					);

					return false;
				}

				if($encoding === null)
					$encoding=mb_internal_encoding();

				if(!in_array($encoding, mb_list_encodings(), true))
				{
					static $aliases=null;

					if($aliases === null)
					{
						$aliases=[];

						foreach(mb_list_encodings() as $encoding)
						{
							$encoding_aliases=mb_encoding_aliases($encoding);

							if($encoding_aliases !== false)
								foreach($encoding_aliases as $alias)
									$aliases[]=$alias;
						}
					}

					if(!in_array($encoding, $aliases, true))
					{
						trigger_error(
							'mb_str_split(): Unknown encoding "'.$encoding.'"',
							E_USER_WARNING
						);

						return null;
					}
				}

				$result=[];
				$length=mb_strlen($string, $encoding);

				for($i=0; $i<$length; $i+=$split_length)
					$result[]=mb_substr($string, $i, $split_length, $encoding);

				return $result;
			}
		}

		/*
		 * mb_str_pad() polyfill
		 *
		 * Source: https://github.com/symfony/polyfill-mbstring/blob/1.x/Mbstring.php
		 * License: MIT https://github.com/symfony/polyfill-mbstring/blob/1.x/LICENSE
		 */

		if(!function_exists('mb_str_pad'))
		{
			function mb_str_pad(
				string $string,
				int $length,
				string $pad_string=' ',
				int $pad_type=STR_PAD_RIGHT,
				?string $encoding=null
			): string {
				if(!in_array($pad_type, [STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH], true))
					throw new ValueError('mb_str_pad(): Argument #4 ($pad_type) must be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH');

				if($encoding === null)
					$encoding=mb_internal_encoding();

				try {
					$valid_encoding=mb_check_encoding('', $encoding);
				} catch(ValueError $error) {
					throw new ValueError(sprintf(
						'mb_str_pad(): Argument #5 ($encoding) must be a valid encoding, "%s" given',
						$encoding
					));
				}

				// BC for PHP 7.3 and lower
				if(!$valid_encoding)
					throw new ValueError(sprintf(
						'mb_str_pad(): Argument #5 ($encoding) must be a valid encoding, "%s" given',
						$encoding
					));

				if(mb_strlen($pad_string, $encoding) <= 0)
					throw new ValueError('mb_str_pad(): Argument #3 ($pad_string) must be a non-empty string');

				$padding_required=$length-mb_strlen($string, $encoding);

				if($padding_required < 1)
					return $string;

				switch($pad_type)
				{
					case STR_PAD_LEFT:
						return mb_substr(
							str_repeat($pad_string, $padding_required),
							0,
							$padding_required, $encoding
						).$string;
					case STR_PAD_RIGHT:
						return $string.mb_substr(
							str_repeat($pad_string, $padding_required),
							0,
							$padding_required,
							$encoding
						);
					default:
						$left_padding_length=floor($padding_required/2);
						$right_padding_length=$padding_required-$left_padding_length;

						return mb_substr(
							str_repeat($pad_string, $left_padding_length),
							0,
							$left_padding_length,
							$encoding
						).$string.mb_substr(
							str_repeat($pad_string, $right_padding_length),
							0,
							$right_padding_length,
							$encoding
						);
				}
			}
		}

		/*
		 * mb_ucfirst() polyfill
		 *
		 * Source: https://github.com/symfony/polyfill-php84/blob/1.x/Php84.php
		 * License: MIT https://github.com/symfony/polyfill-php84/blob/1.x/LICENSE
		 */

		if(!function_exists('mb_ucfirst'))
		{
			function mb_ucfirst(string $string, ?string $encoding=null)
			{
				if($encoding === null)
					$encoding=mb_internal_encoding();

				try {
					$valid_encoding=mb_check_encoding('', $encoding);
				} catch(ValueError $error) {
					throw new ValueError(sprintf(
						'mb_ucfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given',
						$encoding
					));
				}

				// BC for PHP 7.3 and lower
				if(!$valid_encoding)
					throw new ValueError(sprintf(
						'mb_ucfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given',
						$encoding
					));

				return ''
				.	mb_convert_case(
						mb_substr($string, 0, 1, $encoding),
						MB_CASE_TITLE,
						$encoding
					)
				.	mb_substr($string, 1, null, $encoding);
			}
		}
	}
?>