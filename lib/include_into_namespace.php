<?php
	/*
	 * Function that facilitates including libraries to a namespace
	 * Mainly for testing purposes
	 *
	 * Functions:
	 *  include_into_namespace - controller
	 *  include_into_namespace_a - evals namespace exampl { / code / }
	 *  include_into_namespace_b - evals namespace exampl; / code /
	 */

	function include_into_namespace(
		string $namespace,
		string $code,
		$has_close_tag=null
	){
		/*
		 * Function that facilitates including libraries to a namespace
		 * Mainly for testing purposes
		 */

		if($has_close_tag === null)
			include_into_namespace_b($namespace, $code);
		else
			include_into_namespace_a($namespace, $code, $has_close_tag);
	}
	function include_into_namespace_a(
		string $namespace,
		string $code,
		bool $has_close_tag=true
	){
		/*
		 * Function that facilitates including libraries to a namespace
		 * Mainly for testing purposes
		 *
		 * Note:
		 *  to find out if code has such a closing at the end
		 *  you can use the has_php_close_tag.php library
		 *
		 * Warning:
		 *  eval() must be allowed
		 */

		$close_tag='';

		if($has_close_tag)
			$close_tag='<?php ';

		eval(''
		.	'namespace '.$namespace.' { ?>'
		.		$code
		.	$close_tag.'}'
		);
	}
	function include_into_namespace_b(string $namespace, string $code)
	{
		/*
		 * Function that facilitates including libraries to a namespace
		 * Mainly for testing purposes
		 *
		 * Warning:
		 *  eval() must be allowed
		 */

		eval(''
		.	'namespace '.$namespace.'; ?>'
		.	$code
		);
	}
?>