<!DOCTYPE html>
<html lang="<?php echo $GLOBALS['_login']['view']['lang']; ?>">
	<head>
		<title><?php echo $GLOBALS['_login']['view']['loading_title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="default-src 'none'; style-src 'self'<?php if($GLOBALS['_login']['view']['inline_style']) echo ' \'nonce-mainstyle\'';?>;">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			if($GLOBALS['_login']['view']['inline_style'])
			{
				?><style nonce="mainstyle"><?php
					if(is_dir(__DIR__.'/../assets/'.$GLOBALS['_login']['view']['login_style']))
						foreach(
							array_diff(
								scandir(__DIR__.'/../assets/'.$GLOBALS['_login']['view']['login_style']),
								['.', '..']
							)
							as $inline_style
						)
							readfile(__DIR__.'/../assets/'.$GLOBALS['_login']['view']['login_style'].'/'.$inline_style);

					if(is_file(__DIR__.'/../../../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../../../lib/simpleblog_materialized.css');
					else if(is_file(__DIR__.'/../../../../../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../../../../../lib/simpleblog_materialized.css');
					else
						echo '/* simpleblog_materialized.css library not found */';
				?></style><?php
			}
			else
			{ ?>
				<link rel="stylesheet" href="<?php echo $GLOBALS['_login']['view']['assets_path']; ?>/simpleblog_materialized.css">
				<link rel="stylesheet" href="<?php echo $GLOBALS['_login']['view']['assets_path']; ?>/<?php echo $GLOBALS['_login']['view']['login_style']; ?>">
			<?php }
		?>
		<meta name="robots" content="noindex,nofollow">
		<meta http-equiv="refresh" content="0">
	</head>
	<body>
		<h1 id="reload_label">
			<?php echo $GLOBALS['_login']['view']['loading_label']; ?>
		</h1>
	</body>
</html>