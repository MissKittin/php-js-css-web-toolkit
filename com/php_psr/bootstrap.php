<?php
	class php_psr_exception extends Exception {}

	function _php_psr_load_library($type, $library, $function_or_class)
	{
		$result=false;

		switch($type)
		{
			case 'class':
				$result=class_exists($function_or_class);
			break;
			case 'function':
				$result=function_exists($function_or_class);
		}

		if($result)
			return;

		if(file_exists(__DIR__.'/lib/'.$library))
			return require __DIR__.'/lib/'.$library;

		if(file_exists(__DIR__.'/../../lib/'.$library))
			return require __DIR__.'/../../lib/'.$library;

		throw new php_psr_exception(
			$library.' library not found'
		);
	}
?>