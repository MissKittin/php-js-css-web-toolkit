<?php
	class lv_hlp_exception extends Exception {}

	function _lv_hlp_load_library($type, $library_file, $function_or_class)
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

		if(file_exists(__DIR__.'/lib/'.$library_file))
			return require __DIR__.'/lib/'.$library_file;

		if(file_exists(__DIR__.'/../../lib/'.$library_file))
			return require __DIR__.'/../../lib/'.$library_file;

		throw new lv_hlp_exception(
			$library_file.' library not found'
		);
	}
?>