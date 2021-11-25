<!DOCTYPE html>
<html<?php if(isset($view['lang'])) echo ' lang="' . $view['lang'] . '"'; ?>>
	<head>
		<title><?php echo $GLOBALS['login_config']['title']; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $GLOBALS['login_config']['assets_path']; ?>/assets/<?php if(isset($GLOBALS['login_config']['login_style'])) echo $GLOBALS['login_config']['login_style']; else echo 'login_dark.css'; ?>">
	</head>
	<body>
		<div id="login_form">
			<form method="post" action="">
				<div class="input_text"><input type="text" name="login" placeholder="<?php echo $GLOBALS['login_config']['login_label']; ?>"></div>
				<div class="input_text"><input type="password" name="password" placeholder="<?php echo $GLOBALS['login_config']['password_label']; ?>"></div>
				<?php if($GLOBALS['login_config']['display_remember_me_checkbox']) { ?><div class="input_checkbox"><input type="checkbox" name="remember_me" value="true"><?php echo $GLOBALS['login_config']['remember_me_label']; ?></div><?php } ?>
				<div class="input_button"><input type="submit" value="<?php echo $GLOBALS['login_config']['button_label']; ?>"></div>
				<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
			</form>
		</div>
	</body>
</html>