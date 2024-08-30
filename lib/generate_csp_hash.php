<?php
	/*
	 * Generate SHA-256 for a string or file
	 *
	 * Functions:
	 *  generate_csp_hash('alert("ok");')
	 *  generate_csp_hash_file('./script.css')
	 *   note: throws an generate_csp_hash_exception on error
	 */

	class generate_csp_hash_exception extends Exception {}

	function generate_csp_hash(string $data)
	{
		/*
		 * Generate SHA-256 for a string
		 *
		 * Usage:
			$data='alert("ok");'
			$hash=generate_csp_hash($data);

			echo ''
			.	'<head>'
			.		'<meta http-equiv="Content-Security-Policy" content="script-src '.$hash.';">'
			.	'</head>'
			.	'<body>'
			.		'<script>'.$data.'</script>'
			.	'</body>';
		 */

		return ''
		.	'\'sha256-'
		.	base64_encode(hash('sha256', $data, true))
		.	'\'';
	}
	function generate_csp_hash_file(string $file)
	{
		/*
		 * Generate SHA-256 for file
		 *
		 * Note:
		 *  throws an generate_csp_hash_exception on error
		 *
		 * Usage:
			$hash=generate_csp_hash_file('./script.js');

			echo ''
			.	'<head>'
			.		'<meta http-equiv="Content-Security-Policy" content="script-src '.$hash.';">'
			.	'</head>'
			.	'<body>'
			.		'<script>'; readfile('./script.js'); echo '</script>'
			.	'</body>';
		 */

		if(!is_file($file))
			throw new generate_csp_hash_exception($file.' is not a file');

		$hash=hash_file('sha256', $file, true);

		if($hash === false)
			throw new generate_csp_hash_exception('hash_file returned false');

		return ''
		.	'\'sha256-'
		.	base64_encode($hash)
		.	'\'';
	}
?>