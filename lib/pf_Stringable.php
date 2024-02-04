<?php
	/*
	 * Stringable polyfill
	 */

	if(!interface_exists('Stringable'))
	{
		interface Stringable
		{
			public function __toString();
		}
	}
?>