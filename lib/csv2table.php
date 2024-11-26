<?php
	class csv2table_exception extends Exception {}
	function csv2table(array $params)
	{
		/*
		 * Convert CSV file to HTML table
		 *
		 * Note:
		 *  throws an csv2table_exception on error
		 *
		 * Usage:
			$html_table=csv2table([
				'param'=>'value',
				'param2'=>'value2'
			])
		 *
		 * Parameters:
		 *  'input_file' => 'string/path/to/file' (required)
		 *  'separator' => ',' (char, optional)
		 *  'enclosure' => '"' (char, optional)
		 *  'escape' => '\\' (char, optional, default: \)
		 *  'table_header' => false (first row as <th>, optional)
		 *  'echo' => false (use echo instead of return, optional)
		 */

		if(!isset($params['input_file']))
			throw new csv2table_exception(
				'The input_file parameter was not specified'
			);

		foreach([
			'input_file'=>'string',
			'separator'=>'string',
			'enclosure'=>'string',
			'escape'=>'string',
			'table_header'=>'boolean',
			'echo'=>'boolean'
		] as $param=>$param_type)
			if(
				isset($params[$param]) &&
				(gettype($params[$param]) !== $param_type)
			)
				throw new csv2table_exception(
					'The input array parameter '.$param.' is not a '.$param_type
				);

		foreach([
			'separator',
			'enclosure',
			'escape'
		] as $param)
			if(
				isset($params[$param]) &&
				((!isset($params[$param][0])) || isset($params[$param][1])) // (strlen($params[$param]) !== 1)
			)
				throw new csv2table_exception(
					'The '.$param.' must be one character long'
				);

		foreach([
			'separator'=>',',
			'enclosure'=>'"',
			'escape'=>'\\',
			'table_header'=>false,
			'echo'=>false
		] as $param=>$param_value)
			if(!isset($params[$param]))
				$params[$param]=$param_value;

		if(!is_file($params['input_file']))
			throw new csv2table_exception(
				$params['input_file'].' is not a file'
			);

		$append_data=function($data)
		{
			return $data;
		};

		if($params['echo'])
			$append_data=function($data)
			{
				echo $data;
				return '';
			};

		$csv_handle=fopen($params['input_file'], 'r');

		if($csv_handle === false)
			throw new csv2table_exception(
				$params['input_file'].' fopen failed'
			);

		$return_string=$append_data('<table>');

		while(($csv_line=fgetcsv(
			$csv_handle,
			null,
			$params['separator'],
			$params['enclosure'],
			$params['escape']
		)) !== false){
			$return_string.=$append_data('<tr>');

			if($params['table_header'])
			{
				foreach($csv_line as $csv_cell)
					$return_string.=$append_data(''
					.	'<th>'
					.	htmlspecialchars($csv_cell)
					.	'</th>'
					);

				$params['table_header']=false;

				$return_string.=$append_data('</tr>');

				continue;
			}

			foreach($csv_line as $csv_cell)
				$return_string.=$append_data(''
				.	'<td>'
				.	htmlspecialchars($csv_cell)
				.	'</td>'
				);

			$return_string.=$append_data('</tr>');
		}

		$return_string.=$append_data('</table>');

		fclose($csv_handle);

		return $return_string;
	}
?>