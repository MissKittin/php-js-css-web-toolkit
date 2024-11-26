<?php
	/*
	 * Trivial templating engine
	 * with variable escaping
	 *
	 * Two versions: basic (faster) and full (more advanced)
	 *
	 * Usage:
		$rendered_text=basic_templating_engine(
			file_get_contents('./file.html'),
			$input_array
		);
		$rendered_text=trivial_templating_engine(
			file_get_contents('./file.html'),
			$input_array
		);
	 */

	function basic_templating_engine(string $source, array $variables=[])
	{
		/*
		 * Basic version of templating engine
		 *
		 * Warning:
		 *  must be {{ variable }}, not {{variable}}
		 *  if any {{ variable }} is not defined and is called in $source
		 *   raw "{{ variable }}" will be returned
		 *  the input array only accepts strings and classes that implement __toString
		 *
		 * Input array:
			[
				'variable_a'=>'value_a',
				'variable_b'=>'value_b',
				'class_a'=>new my_class() // must implement __toString method
			]
		 */

		foreach($variables as $variable_name=>$variable_value)
			if(
				is_string($variable_value) ||
				(
					is_object($variable_value) &&
					method_exists($variable_value , '__toString')
				)
			)
				$source=str_replace(
					'{{ '.$variable_name.' }}',
					htmlspecialchars($variable_value, ENT_QUOTES, 'UTF-8'),
					$source
				);

		return $source;
	}
	function trivial_templating_engine(string $source, array $variables=[])
	{
		/*
		 * Full version of templating engine
		 *
		 * Warning:
		 *  eval() must be allowed
		 *  for and foreach loops accepts variables with lowerspace letters only
		 *  if any {{ variable }} is not defined and is called in $source, {{ variable }} will be returned
		 *  the input array only accepts strings, arrays for foreach and classes that implement __toString
		 *
		 * Input array:
			[
				'variable_a'=>'value_a',
				'variable_b'=>'value_b',
				'fvariable'=>['f_a', 'f_b', 'f_c'],
				'cvariable'=>new my_class() // must implement __toString method
			]
		 *
		 * Input file:
		 *  variables: {{variable}} or {{ variable }}
		 *  inline PHP (without ; at the end): {[{phpcode}]}
		 *  foreach (spaces as in variables, tabs doesn't matter):
				{[foreach fvariable as myvariable]}
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

		$source=preg_replace(
			'/{\[\s*foreach ([a-z]*) as ([a-z]*)\s*\]}/i',
			'<?php foreach($variables[\'$1\'] as $$2) { ?>',
			$source
		);
		$source=preg_replace(
			'/{\[\s*for ([a-z]*)=([0-9]*) ([a-z]*)(<|<=|>=|>)([0-9]*) (\+|-)\s*\]}/i',
			'<?php for($$1=$2; $$3$4$5; $6$6$$1) { ?>',
			$source
		);
		$source=preg_replace(
			'/{\[\s*while (.*)\s*\]}/i',
			'<?php while($1) { ?>',
			$source
		);

		$source=preg_replace(
			'/{\[{\s*(.*)\s*}\]}/i',
			'<?php $1; ?>',
			$source
		);
		$source=preg_replace(
			'/{\[\[\s*([a-z]*)\s*\]\]}/i',
			'<?php echo htmlspecialchars($$1, ENT_QUOTES, \'UTF-8\'); ?>',
			$source
		);
		$source=preg_replace(
			'/{\[\s*end\s*\]}/i',
			'<?php } ?>',
			$source
		);

		foreach($variables as $variable_name=>$variable_value)
			if(
				is_string($variable_value) ||
				(
					is_object($variable_value) &&
					method_exists($variable_value , '__toString')
				)
			)
				$source=preg_replace(
					'/{{\s*'.$variable_name.'\s*}}/i',
					htmlspecialchars($variable_value, ENT_QUOTES, 'UTF-8'),
					$source
				);

		return eval(' ?>'.$source.'<?php ');
	}
?>