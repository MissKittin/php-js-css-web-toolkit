<!DOCTYPE html>
<html lang="<?php echo login_com_reg_view::_()['lang']; ?>">
	<head>
		<title><?php echo login_com_reg_view::_()['title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach(login_com_reg_csp::get() as $csp_param=>$csp_values)
			{
				echo $csp_param;
				foreach($csp_values as $csp_value)
					echo ' '.$csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<?php
			if(login_com_reg_view::_()['inline_style'])
			{
				?><style nonce="<?php echo login_com_reg::_()['inline_style_nonce']; ?>"><?php
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
		?>
		<meta name="robots" content="noindex,nofollow">
		<?php
			echo login_com_reg_view::_()['html_headers'];

			if(login_com_reg_view::_()['favicon'] !== null)
				readfile(login_com_reg_view::_()['favicon']);
		?>
	</head>
	<body>
		<div id="login_form">
			<form method="post" action="">
				<div class="input_text"><input type="text" name="login" placeholder="<?php echo login_com_reg_view::_()['login_label']; ?>"<?php if(login_com_reg_view::_()['login_box_disabled']) echo ' disabled'; ?><?php if(isset(login_com_reg_view::_()['login_default_value'])) echo ' value="'.htmlspecialchars(login_com_reg_view::_()['login_default_value'], ENT_QUOTES, 'UTF-8').'"'; ?>></div>
				<div class="input_text"><input type="password" name="password" placeholder="<?php echo login_com_reg_view::_()['password_label']; ?>"<?php if(login_com_reg_view::_()['password_box_disabled']) echo ' disabled'; ?>></div>
				<?php if(login_com_reg_view::_()['display_remember_me_checkbox']) { ?>
					<div class="input_checkbox">
						<label class="switch">
							<input type="checkbox" name="remember_me" value="true"<?php if(login_com_reg_view::_()['remember_me_box_disabled']) echo ' disabled'; ?>>
							<span class="slider"></span>
						</label>
						<div class="input_checkbox_text"><?php echo login_com_reg_view::_()['remember_me_label']; ?></div>
					</div>
				<?php } ?>
				<?php if(isset(login_com_reg::_()['wrong_credentials'])) { ?>
					<div class="message_container">
						<?php echo login_com_reg_view::_()['wrong_credentials_label']; ?>
					</div>
				<?php } ?>
				<div class="input_button"><input type="submit" value="<?php echo login_com_reg_view::_()['submit_button_label']; ?>"<?php if(login_com_reg_view::_()['submit_button_disabled']) echo ' disabled'; ?>></div>
				<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
				<input type="hidden" name="login_prompt">
			</form>
		</div>
	</body>
</html>