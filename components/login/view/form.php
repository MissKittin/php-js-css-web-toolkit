<!DOCTYPE html>
<html lang="<?php echo $GLOBALS['login']['view']['lang']; ?>">
	<head>
		<title><?php echo $GLOBALS['login']['view']['title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach($GLOBALS['login']['csp_header'] as $csp_param=>$csp_values)
			{
				echo $csp_param;
				foreach($csp_values as $csp_value)
					echo ' '.$csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<link rel="stylesheet" href="<?php echo $GLOBALS['login']['view']['assets_path']; ?>/<?php echo $GLOBALS['login']['view']['login_style']; ?>">
		<meta name="robots" content="noindex,nofollow">
		<?php echo $GLOBALS['login']['view']['html_headers']; ?>
	</head>
	<body>
		<div id="login_form">
			<form method="post" action="">
				<div class="input_text"><input type="text" name="login" placeholder="<?php echo $GLOBALS['login']['view']['login_label']; ?>"></div>
				<div class="input_text"><input type="password" name="password" placeholder="<?php echo $GLOBALS['login']['view']['password_label']; ?>"></div>
				<?php if($GLOBALS['login']['view']['display_remember_me_checkbox']) { ?>
					<div class="input_checkbox">
						<label class="switch">
							<input type="checkbox" name="remember_me" value="true">
							<span class="slider"></span>
						</label>
						<div class="input_checkbox_text"><?php echo $GLOBALS['login']['view']['remember_me_label']; ?></div>
					</div>
				<?php } ?>
				<?php if(isset($GLOBALS['login']['wrong_credentials'])) { ?>
					<div class="message_container">
						<?php echo $GLOBALS['login']['view']['wrong_credentials_label']; ?>
					</div>
				<?php } ?>
				<div class="input_button"><input type="submit" value="<?php echo $GLOBALS['login']['view']['submit_button_label']; ?>"></div>
				<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
				<input type="hidden" name="login_prompt">
			</form>
		</div>
	</body>
</html>