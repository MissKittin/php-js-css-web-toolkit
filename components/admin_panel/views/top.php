<!DOCTYPE html>
<html<?php if(isset($this->registry['_lang'])) echo ' lang="'.$this->registry['_lang'].'"'; ?>>
	<head>
		<title><?php echo $this->registry['_title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach($this->registry['_csp_header'] as $_csp_param=>$_csp_values)
			{
				echo $_csp_param;
				foreach($_csp_values as $_csp_value)
					echo ' '.$_csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $this->registry['_assets_path']; ?>/admin_panel.css">
		<?php
			if(isset($this->registry['_styles']))
				foreach($this->registry['_styles'] as $_style)
					{ ?><link rel="stylesheet" href="<?php echo $_style; ?>"><?php }
		?>
		<script src="<?php echo $this->registry['_assets_path']; ?>/admin_panel.js"></script>
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
				<?php foreach($this->_list_modules() as $_module_name=>$_module_url) { ?>
					<div class="menu_button">
						<a href="<?php echo $_module_url; ?>"><?php echo $_module_name; ?></a>
					</div>
				<?php } ?>
			</div>
			<div id="admin_module">