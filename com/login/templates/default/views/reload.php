<!DOCTYPE html>
<html lang="<?php echo login_com_reg_view::_()['lang']; ?>">
	<head>
		<title><?php echo login_com_reg_view::_()['loading_title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="default-src 'none'; style-src 'self'<?php if(login_com_reg_view::_()['inline_style']) echo ' \'nonce-mainstyle\'';?>;">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			if(login_com_reg_view::_()['inline_style'])
			{
				?><style nonce="mainstyle"><?php
					if(is_dir(__DIR__.'/../assets/'.login_com_reg_view::_()['login_style']))
						foreach(
							array_diff(
								scandir(__DIR__.'/../assets/'.login_com_reg_view::_()['login_style']),
								['.', '..']
							)
							as $inline_style
						)
							readfile(__DIR__.'/../assets/'.login_com_reg_view::_()['login_style'].'/'.$inline_style);
				?></style><?php
			}
			else
				{ ?><link rel="stylesheet" href="<?php echo login_com_reg_view::_()['assets_path']; ?>/<?php echo login_com_reg_view::_()['login_style']; ?>"><?php }

			if(login_com_reg_view::_()['favicon'] !== null)
				readfile(login_com_reg_view::_()['favicon']);
		?>
		<meta name="robots" content="noindex,nofollow">
		<meta http-equiv="refresh" content="0">
	</head>
	<body>
		<h1 id="reload_label">
			<?php echo login_com_reg_view::_()['loading_label']; ?>
		</h1>
	</body>
</html>