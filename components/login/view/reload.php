<?php global $login_config; ?>
<!DOCTYPE html>
<html<?php if(isset($view['lang'])) echo ' lang="' . $view['lang'] . '"'; ?>>
	<head>
		<title><?php echo $login_config['title']; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $login_config['assets_path']; ?>/assets/<?php if(isset($login_config['login_style'])) echo $login_config['login_style']; else echo 'login_dark.css'; ?>">
		<meta http-equiv="refresh" content="0">
	</head>
	<body>
		<h1 id="reload_label"><?php echo $login_config['loading_label']; ?></h1>
	</body>
</html>