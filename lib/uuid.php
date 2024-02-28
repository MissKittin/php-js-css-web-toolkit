<?php
	/*
	 * UUID manipulation library
	 *
	 * Functions:
	 *  generate_uuid_v1 [returns string]
	 *   Nick Lombard's UUID v1 generator
	 *   can take an optional node identifier
	 *    based on mac address or a unique string id
	 *  generate_uuid_v3 [returns string]
	 *   Sheeraz Gul's UUID v3 generator
	 *  generate_uuid_v4 [returns string]
	 *   UUID v4 generator from uuidgenerator.net
	 *   note: throws an uuid_exception on error
	 *  generate_uuid_v5 [returns string]
	 *   Sheeraz Gul's UUID v5 generator
	 *  generate_uuid_ordered [returns string]
	 *   fab2s' time-ordered UUID generator
	 *    extracted from the SoUuid package
	 *   note:
	 *    the string format does not match RFC pattern
	 *    to prevent any confusion, but it's still matching
	 *    the storage requirement of the RFC in every
	 *    way for better portability: 36 chars string
	 *    or 16 bytes binary string, also being the
	 *    most efficient option
	 *  is_uuid [returns bool]
	 *   warning: will fail with a time-ordered UUID
	 *  is_uuid_ordered [returns bool]
	 *  is_uuid_v1 [returns bool]
	 *  is_uuid_v2 [returns bool]
	 *  is_uuid_v3 [returns bool]
	 *  is_uuid_v4 [returns bool]
	 *  is_uuid_v5 [returns bool]
	 *  decode_uuid_ordered [returns array|false]
	 *   fab2s' time-ordered UUID decoder
	 *    extracted from the SoUuid package
	 *   returns false on error
	 *   returns on success:
	 *    ['microtime'=>string,
	 *    'datetime'=>DateTimeImmutable,
	 *    'identifier'=>string,
	 *    'rand'=>string]
	 *
	 * Usage:
	 *  generate_uuid_v1
	 *   generate_uuid_v1()
	 *   generate_uuid_v1('gist.github.com')
	 *  generate_uuid_v3
	 *   generate_uuid_v3('5f6384bfec4ca0b2d4114a13aa2a5435', 'delftstack')
	 *   generate_uuid_v3('591531f16f581b69a390980eb282ba83', 'this is delftstack!')
	 *  generate_uuid_v4
	 *   generate_uuid_v4()
	 *   generate_uuid_v4(random_bytes(16))
	 *  generate_uuid_v5
	 *   generate_uuid_v5('8fc990b07418d5826d98de952cfb268dee4a23a3', 'delftstack')
	 *   generate_uuid_v5('24316ec81e3bea40286b986249a41e29924d35bf', 'this is delftstack!')
	 *  generate_uuid_ordered
	 *   generate_uuid_ordered()
	 *  is_uuid is_uuid_v1|2|3|4|5
	 *   is_uuid('3b0018c3-f273-5889-88e7-8c66a8720e99')
	 *   is_uuid_v4('3b0018c3-f273-5889-88e7-8c66a8720e99')
	 *  is_uuid_ordered
	 *   is_uuid_ordered('0564a6553c9fbe-003a-46b0-28db-456107')
	 *  decode_uuid_ordered
	 *   decode_uuid_ordered('0564a6553c9fbe-003a-46b0-28db-456107')
	 *
	 * Sources:
	 *  https://gist.github.com/nickl-/79502d2dd383e11f7de437a84c45fffa
	 *  https://www.delftstack.com/howto/php/php-uuid/
	 *  https://www.uuidgenerator.net/dev-corner/php
	 *  https://github.com/fab2s/SoUuid/blob/master/src/SoUuid.php
	 *  https://gist.github.com/joel-james/3a6201861f12a7acf4f2
	 * License: MIT
	 */

	class uuid_exception extends Exception {}

	function generate_uuid_v1(string $node='')
	{
		if(function_exists('random_bytes'))
			$random_bytes=function($bytes)
			{
				return random_bytes($bytes);
			};
		else
		{
			if(!extension_loaded('openssl'))
				throw new uuid_exception('openssl extension is not loaded');

			$random_bytes=function($bytes)
			{
				return openssl_random_pseudo_bytes($bytes);
			};
		}

		$time=pack("H*", sprintf(
			'%016x',
			microtime(true)*10000000+0x01b21dd213814000
		));
		$time[0]=chr(ord($time[0]) & 0x0f | 0x10);
		$sequence=$random_bytes(2);
		$sequence[0]=chr(ord($sequence[0]) & 0x3f | 0x80);

		if(empty($node))
		{
			$node=$random_bytes(6);
			$node[0]=chr(ord($node[0]) | 1);
			$node=bin2hex($node);
		}
		else
		{
			if(preg_match('/[^a-f0-9]/is', $node))
			{
				$node=md5($node);
				$node=(hexdec(substr($node, 0, 2)) | 1).substr($node, 2, 10);
			}

			if(is_numeric($node))
				$node=sprintf('%012x', $node);

			$len=strlen($node);

			if($len > 12)
				$node=substr($node, 0, 12);
			else if($len < 12)
				$node.=str_repeat('0', 12-$len);
		}

		return ''
		.	    bin2hex($time[4].$time[5].$time[6].$time[7])
		.	'-'.bin2hex($time[2].$time[3])
		.	'-'.bin2hex($time[0].$time[1])
		.	'-'.bin2hex($sequence)
		.	'-'.$node
		;
	}
	function generate_uuid_v3(string $name_space, string $string)
	{
		$n_hex=str_replace(['-','{','}'], '', $name_space);
		$binary_str='';

		for($i=0; $i<strlen($n_hex); $i+=2)
			$binary_str.=chr(hexdec($n_hex[$i].$n_hex[$i+1]));

		$hashing=md5($binary_str.$string);

		return sprintf(
			'%08s-%04s-%04x-%04x-%12s',
			substr($hashing, 0, 8),
			substr($hashing, 8, 4),
			(hexdec(substr($hashing, 12, 4)) & 0x0fff) | 0x3000,
			(hexdec(substr($hashing, 16, 4)) & 0x3fff) | 0x8000,
			substr($hashing, 20, 12)
		);
	}
	function generate_uuid_v4(string $data=null)
	{
		if(function_exists('random_bytes'))
			$random_bytes=function($bytes)
			{
				return random_bytes($bytes);
			};
		else
		{
			if(!extension_loaded('openssl'))
				throw new uuid_exception('openssl extension is not loaded');

			$random_bytes=function($bytes)
			{
				return openssl_random_pseudo_bytes($bytes);
			};
		}

		if($data === null)
			$data=$random_bytes(16);

		if((!isset($data[15])) || isset($data[16])) // (strlen($data) !== 16)
			throw new uuid_exception('Input data must be 16 bytes long');

		$data[6]=chr(ord($data[6]) & 0x0f | 0x40);
		$data[8]=chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf(
			'%s%s-%s-%s-%s-%s%s%s',
			str_split(bin2hex($data), 4)
		);
	}
	function generate_uuid_v5(string $name_space, string $string)
	{
		$n_hex=str_replace(['-','{','}'], '', $name_space);
		$binray_str='';

		for($i=0; $i<strlen($n_hex); $i+=2)
			$binray_str.=chr(hexdec($n_hex[$i].$n_hex[$i+1]));

		$hashing=sha1($binray_str.$string);

		return sprintf(
			'%08s-%04s-%04x-%04x-%12s',
			substr($hashing, 0, 8),
			substr($hashing, 8, 4),
			(hexdec(substr($hashing, 12, 4)) & 0x0fff) | 0x5000,
			(hexdec(substr($hashing, 16, 4)) & 0x3fff) | 0x8000,
			substr($hashing, 20, 12)
		);
	}
	function generate_uuid_ordered()
	{
		if(function_exists('random_bytes'))
			$random_bytes=function($bytes)
			{
				return random_bytes($bytes);
			};
		else
		{
			if(!extension_loaded('openssl'))
				throw new uuid_exception('openssl extension is not loaded');

			$random_bytes=function($bytes)
			{
				return openssl_random_pseudo_bytes($bytes);
			};
		}

		$time_parts=explode(' ', microtime(false)); // microTimeBin()

		$uuid_hex=bin2hex( // getHex()
				hex2bin(str_pad( // microTimeBin() return
					base_convert( // $time
						$time_parts[1].substr($time_parts[0], 2, 6), // $timeMicroSec
						10, 16
					),
					14, '0', STR_PAD_LEFT
				))
			.	"\0".$random_bytes(5) // encodeIdentifier()
			.	$random_bytes(3)
		);

		return '' // getString()
		.	substr($uuid_hex, 0, 14)
		.	'-'.substr($uuid_hex, 14, 4)
		.	'-'.substr($uuid_hex, 18, 4)
		.	'-'.substr($uuid_hex, 22, 4)
		.	'-'.substr($uuid_hex, 26)
		;
	}

	function is_uuid(string $uuid)
	{
		return (preg_match(
			'/^'
			.	 '[0-9a-f]{8}'
			.	'-[0-9a-f]{4}'
			.	'-[0-9a-f]{4}'
			.	'-[89ab][0-9a-f]{3}'
			.	'-[0-9a-f]{12}'
			.'$/',
			$uuid
			) === 1
		);
	}
	function is_uuid_ordered(string $uuid)
	{
		return (preg_match(
			'/^'
			.	 '[0-9a-f]{14}'
			.	'-[0-9a-f]{4}'
			.	'-[0-9a-f]{4}'
			.	'-[0-9a-f]{4}'
			.	'-[0-9a-f]{6}'
			.'$/',
			$uuid
			) === 1
		);
	}
	function is_uuid_v1(string $uuid)
	{
		return (
			is_uuid($uuid) &&
			(explode('-', $uuid)[2][0] === '1')
		);
	}
	function is_uuid_v2(string $uuid)
	{
		return (
			is_uuid($uuid) &&
			(explode('-', $uuid)[2][0] === '2')
		);
	}
	function is_uuid_v3(string $uuid)
	{
		return (
			is_uuid($uuid) &&
			(explode('-', $uuid)[2][0] === '3')
		);
	}
	function is_uuid_v4(string $uuid)
	{
		return (
			is_uuid($uuid) &&
			(explode('-', $uuid)[2][0] === '4')
		);
	}
	function is_uuid_v5(string $uuid)
	{
		return (
			is_uuid($uuid) &&
			(explode('-', $uuid)[2][0] === '5')
		);
	}

	function decode_uuid_ordered(string $uuid)
	{
		if(!is_uuid_ordered($uuid))
			return false;

		// fromString()
		$uuid=hex2bin(str_replace('-', '', $uuid));

		// getMicroTime()
		$microtime=base_convert(bin2hex(
			substr($uuid, 0, 7) // $timeBin
		), 16, 10);

		// getIdentifier()
		$identifier=substr($uuid, 7, 6);
		$identifier_null_pos=strpos($identifier, "\0");
		if($identifier_null_pos !== false)
			$identifier=substr($identifier, 0, $identifier_null_pos);

		// decode() $this->decoded['rand']
		$identifier_len=strlen($identifier);
		if($identifier_len === 0)
			$identifier_len=8;
		else
			$identifier_len=7+$identifier_len;

		return [
			'microtime'=>$microtime,
			'datetime'=>new DateTimeImmutable('@'.substr($microtime, 0, -6)), // getDateTime()
			'identifier'=>$identifier,
			'rand'=>bin2hex(substr($uuid, $identifier_len))
		];
	}
?>