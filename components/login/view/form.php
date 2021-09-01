<!DOCTYPE html>
<html<?php if(isset($view['lang'])) echo ' lang="' . $view['lang'] . '"'; ?>>
	<head>
		<title><?php echo $login_config['title']; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $login_config['assets_path']; ?>/assets/<?php if(isset($login_config['login_style'])) echo $login_config['login_style']; else echo 'login-dark.css'; ?>">
	</head>
	<body>
		<div id="login-form">
			<form method="post" action="">
				<div class="input-text"><input type="text" name="login" placeholder="<?php echo $login_config['login_label']; ?>"></div>
				<div class="input-text"><input type="password" name="password" placeholder="<?php echo $login_config['password_label']; ?>"></div>
				<div class="input-button"><input type="submit" value="<?php echo $login_config['button_label']; ?>"></div>
				<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
			</form>
		<div>
	</body>
</html>