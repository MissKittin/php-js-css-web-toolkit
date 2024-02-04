<?php
	/*
	 * Error polyfill
	 */

	if(!class_exists('Error'))
	{
		class Error extends Exception {}
	}

	/*
	 * ValueError polyfill
	 */

	if(!class_exists('ValueError'))
	{
		class ValueError extends Error {}
	}
?>