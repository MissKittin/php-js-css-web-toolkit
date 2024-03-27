<?php
	foreach($view['form_fields'] as $form_field)
	{
		if($form_field['tag'] === null)
		{
			echo $form_field['content'];
			continue;
		}

		if(
			isset($form_field['type']) &&
			($form_field['type'] === 'slider')
		){
			$form_field['type']='checkbox';
			$slider_label=$form_field['slider_label'];
			unset($form_field['slider_label']);
			unset($form_field['tag']);

			echo ''
			.	'<div class="input_checkbox">'
			.		'<label class="switch">';

					echo '<input';
					foreach($form_field as $parameter_name=>$parameter_value)
						echo ' '
						.	$parameter_name
						.	'='
						.	'"'.$parameter_value.'"';
					echo '>';

			echo ''
			.			'<span class="slider"></span>'
			.		'</label>'
			.		'<div class="input_checkbox_text">'
			.			$slider_label
			.		'</div>'
			.	'</div>';

			unset($slider_label);
		}
		else if(
			isset($form_field['type']) &&
			isset($form_field['label']) &&
			(
				($form_field['type'] === 'checkbox') ||
				($form_field['type'] === 'radio')
			)
		){
			$label=$form_field['label'];
			unset($form_field['label']);

			echo ''
			.	'<div class="input_checkbox">'
			.		'<'.$form_field['tag'];

			unset($form_field['tag']);

			foreach($form_field as $parameter_name=>$parameter_value)
				if($parameter_value === null)
					echo ' '.$parameter_name;
				else
					echo ' '.$parameter_name.'="'.$parameter_value.'"';

			echo '>'
			.		'<label>'.$label.'</label>'
			.	'</div>';
		}
		else
		{
			echo ''
			.	'<div class="input_text">'
			.	'<'.$form_field['tag'];

			unset($form_field['tag']);

			foreach($form_field as $parameter_name=>$parameter_value)
				if($parameter_value === null)
					echo ' '.$parameter_name;
				else
					echo ' '.$parameter_name.'="'.$parameter_value.'"';

			echo '>'
			.	'</div>';
		}
	}
?>