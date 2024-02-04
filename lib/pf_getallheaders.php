<?php
	// getallheaders() polyfill

	if(!function_exists('getallheaders'))
	{
		function getallheaders()
		{
			$headers=[];

			foreach($_SERVER as $header_name=>$header_value)
				if(substr($header_name, 0, 5) === 'HTTP_')
					$headers[
						strtr(
							ucwords(strtolower(strtr(
								substr($header_name, 5),
								'_', ' '
							))),
							' ', '-')
					]=$header_value;

			return $headers;
		}
	}
?>