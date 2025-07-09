<?php
	if(!class_exists('php_psr_exception'))
		require __DIR__.'/bootstrap.php';

	(function(){
		// PSR-11: Container interface
			try {
				require __DIR__.'/container.php';
			} catch(php_psr_exception $error) {}

		// PSR-3: Logger Interface
			try {
				require __DIR__.'/logger.php';
			} catch(php_psr_exception $error) {}
	})();
?>