<?php
	/*
	 * ULID manipulation library
	 *
	 * Functions:
	 *  generate_ulid [returns string]
	 *   Robin van der Vleuten's php-ulid generator
	 *  is_ulid [returns bool]
	 *   Robin van der Vleuten's php-ulid checker
	 *  decode_ulid [returns int]
	 *   Robin van der Vleuten's php-ulid decoder
	 *  ulid2uuid [returns string]
	 *   mpyw's uuid-ulid-converter
	 *  uuid2ulid [returns string]
	 *   mpyw's uuid-ulid-converter
	 *
	 * Usage:
	 *  generate_ulid
	 *   generate_ulid()
	 *  is_ulid
	 *   is_ulid('61862H2EWP9TCTRX3MJ15XNY7X')
	 *  decode_ulid
	 *   decode_ulid('61862H2EWP9TCTRX3MJ15XNY7X')
	 *  ulid2uuid
	 *   ulid2uuid('61862H2EWP9TCTRX3MJ15XNY7X')
	 *  uuid2ulid
	 *   uuid2ulid('c1418511-3b96-4e99-ac74-74904bdaf8fd')
	 *
	 * Sources:
	 *  https://github.com/robinvdvleuten/php-ulid/blob/master/src/Ulid.php
	 *  https://github.com/mpyw/uuid-ulid-converter/blob/master/src/Converter.php
	 * License: MIT
	 */

	class ulid_exception extends Exception {}
	function generate_ulid()
	{
		if(function_exists('random_int'))
			$random_int=function($min, $max)
			{
				return random_int($min, $max);
			};
		else
		{
			if(!extension_loaded('openssl'))
				throw new ulid_exception('openssl extension is not loaded');

			$random_int=function($min, $max)
			{
				return ((unpack("N", openssl_random_pseudo_bytes(4))%($max-$min))+$min);
			};
		}

		$timestamp=(int)floor((microtime(true)*1000));

		// fromTimestamp()
		static $last_rand_chars=[];
		static $last_timestamp=null;
		$duplicated_time=false;
		$time_chars='';
		$rand_chars='';
		$encoding_chars='0123456789ABCDEFGHJKMNPQRSTVWXYZ';

		if(($last_timestamp !== null) && ($timestamp === $last_timestamp))
			$duplicated_time=true;

		$last_timestamp=$timestamp;

		for($i=9; $i>=0; --$i)
		{
			$mod=$timestamp%32;
			$time_chars=$encoding_chars[$mod].$time_chars;
			$timestamp=($timestamp-$mod)/32;
		}

		if($duplicated_time)
		{
			for($i=15; $i>=0 && ($last_rand_chars[$i] === 31); --$i)
				$last_rand_chars[$i]=0;

			++$last_rand_chars[$i];
		}
		else
			for($i=0; $i<16; ++$i)
				$last_rand_chars[$i]=$random_int(0, 31);

		for($i=0; $i<16; ++$i)
			$rand_chars.=$encoding_chars[$last_rand_chars[$i]];

		// __toString()
		return $time_chars.$rand_chars;
	}
	function is_ulid(string $ulid)
	{
		return (
			(strlen($ulid) === 26) &&
			(preg_match(
				sprintf(
					'!^[%s]{%d}$!',
					'0123456789ABCDEFGHJKMNPQRSTVWXYZ',
					26
				),
				strtoupper($ulid)
			) === 1)
		);
	}
	function decode_ulid(string $ulid)
	{
		// fromString()
		if(!is_ulid($ulid))
			return false;

		$ulid=strtoupper($ulid);
		$time=substr($ulid, 0, 10);
		$randomness=substr($ulid, 10, 16);

		// decodeTime()
		$time_chars=str_split(strrev($time));
		$carry=0;

		foreach($time_chars as $index=>$char)
		{
			$encoding_index=strripos('0123456789ABCDEFGHJKMNPQRSTVWXYZ', $char);

			if($encoding_index === false)
				throw new ulid_exception('Invalid ULID character: '.$char);

			$carry+=($encoding_index*pow(32, $index));
		}

		if($carry>281474976710655)
			throw new ulid_exception('Invalid ULID string: timestamp too large');

		return (int)$carry;
	}
	function ulid2uuid(string $ulid)
	{
		if(!is_ulid($ulid))
			return false;

		$ulid_reverse_table=[
			0=>0,
			1=>1,
			2=>2,
			3=>3,
			4=>4,
			5=>5,
			6=>6,
			7=>7,
			8=>8,
			9=>9,
			'A'=>10,
			'B'=>11,
			'C'=>12,
			'D'=>13,
			'E'=>14,
			'F'=>15,
			'G'=>16,
			'H'=>17,
			'J'=>18,
			'K'=>19,
			'M'=>20,
			'N'=>21,
			'P'=>22,
			'Q'=>23,
			'R'=>24,
			'S'=>25,
			'T'=>26,
			'V'=>27,
			'W'=>28,
			'X'=>29,
			'Y'=>30,
			'Z'=>31
		];

		$ord=array_map(
			function($char) use($ulid_reverse_table)
			{
				return $ulid_reverse_table[$char];
			},
			str_split(strtoupper($ulid))
		);

		return preg_replace(
			'/^(.{8})(.{4})(.{4})(.{4})(.{12})$/',
			'$1-$2-$3-$4-$5',
			bin2hex(pack(
				'C*',
				(($ord[0] << 5) | $ord[1]),
				(($ord[2] << 3) | ($ord[3] >> 2)),
				(($ord[3] << 6) | ($ord[4] << 1) | ($ord[5] >> 4)),
				(($ord[5] << 4) | ($ord[6] >> 1)),
				(($ord[6] << 7) | ($ord[7] << 2) | ($ord[8] >> 3)),
				(($ord[8] << 5) | $ord[9]),
				(($ord[10] << 3) | ($ord[11] >> 2)),
				(($ord[11] << 6) | ($ord[12] << 1) | ($ord[13] >> 4)),
				(($ord[13] << 4) | ($ord[14] >> 1)),
				(($ord[14] << 7) | ($ord[15] << 2) | ($ord[16] >> 3)),
				(($ord[16] << 5) | $ord[17]),
				(($ord[18] << 3) | $ord[19] >> 2),
				(($ord[19] << 6) | ($ord[20] << 1) | ($ord[21] >> 4)),
				(($ord[21] << 4) | ($ord[22] >> 1)),
				(($ord[22] << 7) | ($ord[23] << 2) | ($ord[24] >> 3)),
				(($ord[24] << 5) | $ord[25])
			))
		);
	}
	function uuid2ulid(string $uuid)
	{
		if(preg_match('/\A[0-9A-Z]{8}(?:-[0-9A-Z]{4}){3}-[0-9A-Z]{12}\z/i', $uuid) !== 1)
			return false;

		$ulid_table='0123456789ABCDEFGHJKMNPQRSTVWXYZ';

		$chr=array_values(unpack(
			'C*',
			hex2bin(str_replace('-', '', $uuid))
		));

		return implode('', [
			$ulid_table[($chr[0] & 224) >> 5],
			$ulid_table[$chr[0] & 31],
			$ulid_table[($chr[1] & 248) >> 3],
			$ulid_table[(($chr[1] & 7) << 2) | (($chr[2] & 192) >> 6)],
			$ulid_table[($chr[2] & 62) >> 1],
			$ulid_table[(($chr[2] & 1) << 4) | (($chr[3] & 240) >> 4)],
			$ulid_table[(($chr[3] & 15) << 1) | (($chr[4] & 128) >> 7)],
			$ulid_table[($chr[4] & 124) >> 2],
			$ulid_table[(($chr[4] & 3) << 3) | (($chr[5] & 224) >> 5)],
			$ulid_table[$chr[5] & 31],
			$ulid_table[($chr[6] & 248) >> 3],
			$ulid_table[(($chr[6] & 7) << 2) | (($chr[7] & 192) >> 6)],
			$ulid_table[($chr[7] & 62) >> 1],
			$ulid_table[(($chr[7] & 1) << 4) | (($chr[8] & 240) >> 4)],
			$ulid_table[(($chr[8] & 15) << 1) | (($chr[9] & 128) >> 7)],
			$ulid_table[($chr[9] & 124) >> 2],
			$ulid_table[(($chr[9] & 3) << 3) | (($chr[10] & 224) >> 5)],
			$ulid_table[$chr[10] & 31],
			$ulid_table[($chr[11] & 248) >> 3],
			$ulid_table[(($chr[11] & 7) << 2) | (($chr[12] & 192) >> 6)],
			$ulid_table[($chr[12] & 62) >> 1],
			$ulid_table[(($chr[12] & 1) << 4) | (($chr[13] & 240) >> 4)],
			$ulid_table[(($chr[13] & 15) << 1) | (($chr[14] & 128) >> 7)],
			$ulid_table[($chr[14] & 124) >> 2],
			$ulid_table[(($chr[14] & 3) << 3) | (($chr[15] & 224) >> 5)],
			$ulid_table[$chr[15] & 31]
		]);
	}
?>