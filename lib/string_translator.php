<?php
	class string_translator
	{
		/*
		 * Translate your app
		 *
		 * Note:
		 *  from_json() trims the given string
		 *
		 * Constructor parameters:
		 *  array with 'original string'=>'translated string'
		 *
		 * Methods:
		 *  [static] from_json(string_json, string_language) // string_language is optional
		 *   converts JSON to array
		 *    (see Usage - load from JSON single language)
		 *   if string_language is defined, extracts the string_language subarray
		 *    (see Usage - load from JSON multi language)
		 *
		 * Usage - default language:
			$lang=new string_translator();
			echo $lang('String'); // returns String
			echo $lang('Log in'); // returns Log in
			echo $lang('%m minutes and %s second%d left', [
				'%m'=>2,
				'%s'=>3,
				'%d'=>'s'
			]); // returns 2 minutes and 3 seconds left
			echo $lang('%m minutes and %s second%d left', [
				'%m'=>2,
				'%s'=>1,
				'%d'=>''
			]); // returns 2 minutes and 1 second left
		 *
		 * Usage - translate:
			$lang=new string_translator([
				'String'=>'Sznurek',
				'Log in'=>'Zaloguj sie',
				'%m minutes and %s second%d left'=>'Zostalo %m minut%x i %s sekund%d'
			]);
			echo $lang('String'); // returns Sznurek
			echo $lang('Log in'); // returns Zaloguj sie
			echo $lang('%m minutes and %s second%d left', [
				'%m'=>2,
				'%x'=>'y',
				'%s'=>5,
				'%d'=>''
			]); // returns Zostalo 2 minuty i 5 sekund
			echo $lang('%m minutes and %s second%d left', [
				'%m'=>5,
				'%x'=>'',
				'%s'=>1,
				'%d'=>'a'
			]); // returns Zostalo 5 minut i 1 sekunda
		 *
		 * Usage - load from JSON (single language):
			$json=''
			.	'{'
			.		'"String": "Sznurek",'
			.		'"Log in": "Zaloguj sie",'
			.		'"%m minutes and %s second%d left": "Zostalo %m minut%x i %s sekund%d"'
			.	'}';
			$lang=new string_translator(string_translator::from_json($json));
		 *
		 * Usage - load from JSON (multi language):
			$json=''
			.	'{'
			.		'"pl": {'
			.			'"String": "Sznurek",'
			.			'"Log in": "Zaloguj sie",'
			.			'"%m minutes and %s second%d left": "Zostalo %m minut%x i %s sekund%d"'
			.		'},'
			.		'"ru": {'
			.			'"String": "Priwiet",'
			.			'"Log in": "Awtorizowatsia",'
			.			'"%m minutes and %s second%d left": "Wnimanje: %m minuty i %s sekundy"'
			.		'}'
			.	'}';
			$lang=new string_translator(string_translator::from_json($json, 'pl'));
		 */

		protected $strings=[];

		public static function from_json(string $json, ?string $language=null)
		{
			$json=trim($json);

			if(empty($json))
				return [];

			if($language === null)
				return json_decode($json, true);

			$decoded_json=json_decode($json, true);

			if(!isset($decoded_json[$language]))
				return [];

			return $decoded_json[$language];
		}

		public function __construct(array $strings=[])
		{
			$this->strings=$strings;
		}
		public function __invoke(string $string, array $replace_map=[])
		{
			$replace_params=[];
			$replace_values=[];

			foreach($replace_map as $replace_param=>$replace_value)
			{
				$replace_params[]=$replace_param;
				$replace_values[]=$replace_value;
			}

			if(isset($this->strings[$string]))
				$string=$this->strings[$string];

			return str_replace($replace_params, $replace_values, $string);
		}
	}
?>