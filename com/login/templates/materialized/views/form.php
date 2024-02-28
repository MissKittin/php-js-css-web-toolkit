<!DOCTYPE html>
<html lang="<?php echo $GLOBALS['_login']['view']['lang']; ?>">
	<head>
		<title><?php echo $GLOBALS['_login']['view']['title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach($GLOBALS['_login']['csp_header'] as $csp_param=>$csp_values)
			{
				echo $csp_param;
				foreach($csp_values as $csp_value)
					echo ' '.$csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<?php
			if($GLOBALS['_login']['view']['inline_style'])
			{
				?><style nonce="mainstyle"><?php
					if(is_file(__DIR__.'/../../../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../../../lib/simpleblog_materialized.css');
					else if(is_file(__DIR__.'/../../../../../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../../../../../lib/simpleblog_materialized.css');
					else
						echo '/* simpleblog_materialized.css library not found */';

					if(is_dir(__DIR__.'/../assets/'.$GLOBALS['_login']['view']['login_style']))
						foreach(
							array_diff(
								scandir(__DIR__.'/../assets/'.$GLOBALS['_login']['view']['login_style']),
								['.', '..']
							)
							as $inline_style
						)
							readfile(__DIR__.'/../assets/'.$GLOBALS['_login']['view']['login_style'].'/'.$inline_style);
				?></style><?php
			}
			else
			{ ?>
				<link rel="stylesheet" href="<?php echo $GLOBALS['_login']['view']['assets_path']; ?>/simpleblog_materialized.css">
				<link rel="stylesheet" href="<?php echo $GLOBALS['_login']['view']['assets_path']; ?>/<?php echo $GLOBALS['_login']['view']['login_style']; ?>">
			<?php }
		?>
		<meta name="robots" content="noindex,nofollow">
		<?php echo $GLOBALS['_login']['view']['html_headers']; ?>
	</head>
	<body>
		<div id="login_form">
			<div id="login_content">
				<form method="post" action="">
					<div class="input_text">
						<label for="login"><?php echo $GLOBALS['_login']['view']['login_label']; ?></label>
						<input type="text" name="login"<?php if($GLOBALS['_login']['view']['login_box_disabled']) echo ' disabled'; ?><?php if(isset($GLOBALS['_login']['view']['login_default_value'])) echo ' value="'.htmlspecialchars($GLOBALS['_login']['view']['login_default_value'], ENT_QUOTES, 'UTF-8').'"'; ?>>
					</div>
					<div class="input_text">
						<label for="password"><?php echo $GLOBALS['_login']['view']['password_label']; ?></label>
						<input type="password" name="password" <?php if($GLOBALS['_login']['view']['password_box_disabled']) echo ' disabled'; ?>>
					</div>
					<?php if($GLOBALS['_login']['view']['display_remember_me_checkbox']) { ?>
						<div class="input_checkbox">
							<label class="switch">
								<input type="checkbox" name="remember_me" value="true"<?php if($GLOBALS['_login']['view']['remember_me_box_disabled']) echo ' disabled'; ?>>
								<span class="sb_switch_lever"></span>
							</label>
							<div class="input_checkbox_text"><?php echo $GLOBALS['_login']['view']['remember_me_label']; ?></div>
						</div>
					<?php } ?>
					<div class="input_button"><input class="sb_full_button" type="submit" value="<?php echo $GLOBALS['_login']['view']['submit_button_label']; ?>"<?php if($GLOBALS['_login']['view']['submit_button_disabled']) echo ' disabled'; ?>></div>
					<?php if(isset($GLOBALS['_login']['wrong_credentials'])) { ?>
						<div class="message_container">
							<?php echo $GLOBALS['_login']['view']['wrong_credentials_label']; ?>
						</div>
					<?php } ?>
					<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
					<input type="hidden" name="login_prompt">
				</form>
			</div>
		</div>
	</body>
</html>