<?php
	class php_polyfill_exception extends Exception {}

	(function($library_list){
		$included_files=[];
		$lib_dir=__DIR__.'/../../lib';

		if(file_exists(__DIR__.'/lib'))
			$lib_dir=__DIR__.'/lib';

		foreach($library_list as $version=>$libraries)
			if(PHP_VERSION_ID < $version)
				foreach($libraries as $library)
				{
					if(isset($included_files[$library]))
						continue;

					if(file_exists($lib_dir.'/pf_'.$library.'.php'))
					{
						require $lib_dir.'/pf_'.$library.'.php';
						$included_files[$library]=0;
						continue;
					}

					throw new php_polyfill_exception('pf_'.$library.'.php library not found');
				}
	})(
		require __DIR__.'/registry.php'
	);
?>