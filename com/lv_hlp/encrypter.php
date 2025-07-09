<?php
	if(!class_exists('lv_hlp_exception'))
		require __DIR__.'/bootstrap.php';

	final class lv_hlp_encrypter
	{
		private static $encrypter=null;

		public static function set_key(
			$key,
			$cipher='aes-256-gcm'
		){
			_lv_hlp_load_library('class', 'sec_lv_encrypter.php', 'lv_encrypter');

			self::$encrypter=new lv_encrypter(
				$key,
				$cipher
			);

			return self::class;
		}

		public static function encrypt($content)
		{
			if(self::$encrypter === null)
				throw new lv_hlp_exception(
					'Use '.__CLASS__.'::set_key first'
				);

			return self::$encrypter->encrypt($content);
		}
		public static function decrypt($payload)
		{
			if(self::$encrypter === null)
				throw new lv_hlp_exception(
					'Use '.__CLASS__.'::set_key first'
				);

			return self::$encrypter
			->	decrypt($payload);
		}
	}

	function lv_hlp_encrypter_generate_key($cipher='aes-256-gcm')
	{
		_lv_hlp_load_library('class', 'sec_lv_encrypter.php', 'lv_encrypter');
		return lv_encrypter::generate_key($cipher);
	}
	function lv_hlp_encrypter_key($key)
	{
		if($key === false)
			return;

		return lv_hlp_encrypter::set_key($key);
	}
	function lv_hlp_encrypt($content)
	{
		return lv_hlp_encrypter::encrypt($content);
	}
	function lv_hlp_decrypt($content)
	{
		return lv_hlp_encrypter::decrypt($content);
	}
?>