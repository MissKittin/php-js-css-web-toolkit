<!DOCTYPE html>
<html lang="<?php echo $GLOBALS['_login']['view']['lang']; ?>">
	<head>
		<title><?php echo $GLOBALS['_login']['view']['loading_title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="default-src 'none'; style-src 'self';">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $GLOBALS['_login']['view']['assets_path']; ?>/<?php echo $GLOBALS['_login']['view']['login_style']; ?>">
		<meta name="robots" content="noindex,nofollow">
		<meta http-equiv="refresh" content="0">
	</head>
	<body>
		<h1 id="reload_label">
			<?php echo $GLOBALS['_login']['view']['loading_label']; ?>
		</h1>
	</body>
</html>