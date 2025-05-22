<?php
	function string_interpolator(
		string $message,
		array $context=[],
		string $open_tag='{',
		string $close_tag='}'
	){
		/*
		 * PHP-FIG placeholder interpolation
		 *
		 * Usage:
			$context=[
				'username'=>'bolivar',
				'status'=>new class()
				{
					public function __toString()
					{
						return 'NO_ERROR';
					}
				}
			];

			$message=string_interpolator(
				'User {username} created ({status} {status})',
				$context
			); // User bolivar created (NO_ERROR NO_ERROR)

			$message=string_interpolator(
				'User [username] created ([status] [status])',
				$context,
				'[', ']'
			); // User bolivar created (NO_ERROR NO_ERROR)

			$message=string_interpolator(
				'User {{ username }} created ({{ status }} {{ status }})',
				$context,
				'{{ ', ' }}'
			); // User bolivar created (NO_ERROR NO_ERROR)
		 *
		 * Source: https://www.php-fig.org/psr/psr-3/
		 */

		$replace=[];

		foreach($context as $key=>$value)
			if(
				(!is_array($value)) &&
				(
					(!is_object($value)) ||
					method_exists($value, '__toString')
				)
			)
				$replace[''
				.	$open_tag
				.	$key
				.	$close_tag
				]=$value;

		return strtr(
			$message,
			$replace
		);
	}
?>