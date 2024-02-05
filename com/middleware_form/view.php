<!DOCTYPE html>
<html lang="<?php echo $view['lang']; ?>">
	<head>
		<title><?php echo $view['title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach($view['csp_header'] as $csp_param=>$csp_values)
			{
				echo $csp_param;
				foreach($csp_values as $csp_value)
					echo ' '.$csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $view['assets_path']; ?>/<?php echo $view['middleware_form_style']; ?>">
		<meta name="robots" content="noindex,nofollow">
		<?php
			if(isset($view['html_headers']))
				echo $view['html_headers'];
		?>
	</head>
	<body>
		<div id="middleware_form">
			<form method="post" action="">
				<?php $this->parse_fields($view); ?>
				<?php if(isset($view['error_message'])) { ?>
					<div class="message_container">
						<?php echo $view['error_message']; ?>
					</div>
				<?php } ?>
				<div class="input_button"><input type="submit" name="middleware_form" value="<?php echo $view['submit_button_label']; ?>"></div>
				<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
			</form>
		</div>
	</body>
</html>