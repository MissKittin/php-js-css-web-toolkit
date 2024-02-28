<!DOCTYPE html>
<html<?php if(isset($this->registry['_lang'])) echo ' lang="'.$this->registry['_lang'].'"'; ?>>
	<head>
		<title><?php echo $this->registry['_title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			if($this->registry['_inline_assets'])
			{
				$this->registry['_csp_header']['script-src'][]='\'nonce-mainscript\'';
				$this->registry['_csp_header']['style-src'][]='\'nonce-mainstyle\'';
			}

			foreach($this->registry['_csp_header'] as $_csp_param=>$_csp_values)
			{
				echo $_csp_param;
				foreach($_csp_values as $_csp_value)
					echo ' '.$_csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			if($this->registry['_inline_assets'])
			{
				?><style nonce="mainstyle"><?php
					if(is_dir(__DIR__.'/../assets/admin_panel_default.css'))
						foreach(
							array_diff(
								scandir(__DIR__.'/../assets/admin_panel_default.css'),
								['.', '..']
							)
							as $inline_style
						)
							readfile(__DIR__.'/../assets/admin_panel_default.css/'.$inline_style);
				?></style><?php
			}
			else
			{
				?><link rel="stylesheet" href="<?php echo $this->registry['_assets_path']; ?>/admin_panel_default.css"><?php
			}
		?>
		<?php
			if(isset($this->registry['_styles']))
				foreach($this->registry['_styles'] as $_style)
					{ ?><link rel="stylesheet" href="<?php echo $_style; ?>"><?php }
		?>
		<?php
			if($this->registry['_inline_assets'])
			{
				?><script nonce="mainscript"><?php
					if(is_file(__DIR__.'/../assets/admin_panel_default.js'))
						readfile(__DIR__.'/../assets/admin_panel_default.js');
				?></script><?php
			}
			else
			{
				?><script src="<?php echo $this->registry['_assets_path']; ?>/admin_panel_default.js"></script><?php
			}
		?>
		<?php
			if(isset($this->registry['_scripts']))
				foreach($this->registry['_scripts'] as $_script)
					{ ?><script src="<?php echo $_script; ?>"></script><?php }
		?>
		<meta name="robots" content="noindex,nofollow">
		<?php if(isset($this->registry['_html_headers'])) echo $this->registry['_html_headers']; ?>
	</head>
	<body>
		<div id="admin_header">
			<button id="menu_button"><?php echo $this->registry['_menu_button_label']; ?></button>
			<h1><?php echo $this->registry['_panel_label']; ?></h1>
			<div id="logout_button">
				<?php if(isset($this->registry['_show_logout_button'])) { ?>
					<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
						<input type="submit" name="logout" value="<?php echo $this->registry['_logout_button_label']; ?>">
						<input type="hidden" name="<?php echo $this->registry['_csrf_token']['name']; ?>" value="<?php echo $this->registry['_csrf_token']['value']; ?>">
					</form>
				<?php } ?>
			</div>
		</div>
		<div id="admin_content">
			<div id="admin_menu">
				<?php foreach($this->_list_modules() as $_module_name=>$_module_data) { ?>
					<div class="menu_button">
						<a href="<?php echo $_module_data['url']; ?>"><?php echo $_module_name; ?></a>
					</div>
				<?php } ?>
			</div>
			<div id="admin_module">
				<?php if(isset($_module['template_header'])) { ?>
					<h1><?php echo $_module['template_header']; ?></h1>
				<?php } ?>