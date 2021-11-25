<?php
	/*
	 * Trivial templating engine
	 * with variable escaping
	 *
	 * Two versions: basic (faster) and full (more advanced)
	 *
	 * Usage: *_templating_engine(file_get_contents('./file.html'), $input_array)
	 */

	function basic_templating_engine($source, $variables)
	{
		/*
		 * Basic version of templating engine
		 * Supports only {{variable}}
		 *
		 * Warning: must be {{variable}}, not {{ variable }}
		 *
		 * Input array:
			array(
				'variable_a'=>'value_a',
				'variable_b'=>'value_b'
			)
		 */

		foreach($variables as $variable_name=>$variable_value)
			$source=str_replace('{{'.$variable_name.'}}', htmlspecialchars($variable_value, ENT_QUOTES, 'UTF-8'), $source);

		return $source;
	}
	function trivial_templating_engine($source, $variables)
	{
		/*
		 * Full version of templating engine
		 *
		 * eval() must be allowed
		 *
		 * Input array:
			array(
				'variable_a'=>'value_a',
				'variable_b'=>'value_b',
				'f_variable'=>['f_a', 'f_b', 'f_c']
			)
		 *
		 * Input file:
		 *  variables: {{variable}} or {{ variable }}
		 *  inline PHP (without ; at the end): {[{phpcode}]}
		 *  foreach (spaces as in variables, tabs doesn't matter):
				{[foreach f_variable as myvariable]}
					{[[myvariable]]} sample text
				{[end]}
		 *  for (operator in second parameter can be < <= >= > and third parameter can be + or -):
				{[for i=0 i<=10 +]}
					for i={[[i]]}
				{[end]}
		 *  while (can be anything instead of $i<=10):
				{[{$i=0}]}
				{[while $i<=10]}
					while i={[[i]]}
					{[{++$i}]}
				{[end]}
		 */

		$source=preg_replace('/{\[\s*foreach ([a-z]*) as ([a-z]*)\s*\]}/i', '<?php foreach($variables[\'$1\'] as $$2) { ?>', $source);
		$source=preg_replace('/{\[\s*for ([a-z]*)=([0-9]*) ([a-z]*)(<|<=|>=|>)([0-9]*) (\+|-)\s*\]}/i', '<?php for($$1=$2; $$3$4$5; $6$6$$1) { ?>', $source);
		$source=preg_replace('/{\[\s*while (.*)\s*\]}/i', '<?php while($1) { ?>', $source);

		$source=preg_replace('/{\[{\s*(.*)\s*}\]}/i', '<?php $1; ?>', $source);
		$source=preg_replace('/{\[\[\s*([a-z]*)\s*\]\]}/i', '<?php echo htmlspecialchars($$1, ENT_QUOTES, \'UTF-8\'); ?>', $source);
		$source=preg_replace('/{\[\s*end\s*\]}/i', '<?php } ?>', $source);

		foreach($variables as $variable_name=>$variable_value)
			if(!is_array($variable_value))
				$source=preg_replace('/{{\s*'.$variable_name.'\s*}}/i', htmlspecialchars($variable_value, ENT_QUOTES, 'UTF-8'), $source);

		return eval(' ?>'.$source.'<?php ');;
	}
?>