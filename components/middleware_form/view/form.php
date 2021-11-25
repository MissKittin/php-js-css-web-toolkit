<!DOCTYPE html>
<html<?php if(isset($view['lang'])) echo ' lang="' . $view['lang'] . '"'; ?>>
	<head>
		<title><?php echo $middleware_form_config['title']; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $middleware_form_config['assets_path']; ?>/assets/<?php if(isset($middleware_form['middleware_form_style'])) echo $middleware_form_config['middleware_form_style']; else echo 'middleware_form_dark.css'; ?>">
	</head>
	<body>
		<div id="middleware_form">
			<form method="post" action="">
				<?php
					foreach($view['form_fields'] as $form_field)
						echo '<div class="input_text"><input type="'.$form_field[0].'" name="'.$form_field[1].'" placeholder="'.$form_field[2].'"></div>';
				?>
				<div class="input_button"><input type="submit" name="middleware_form" value="<?php echo $middleware_form_config['button_label']; ?>"></div>
				<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
			</form>
		</div>
	</body>
</html>