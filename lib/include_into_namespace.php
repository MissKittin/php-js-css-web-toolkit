<?php
	function include_into_namespace(
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
			.'namespace '.$namespace.' { ?>'
				.$code
			.$close_tag.'}'
		);
	}
?>