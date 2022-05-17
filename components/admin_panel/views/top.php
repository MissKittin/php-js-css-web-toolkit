<!DOCTYPE html>
<html<?php if(isset($this->registry['__lang'])) echo ' lang="'.$this->registry['__lang'].'"'; ?>>
	<head>
		<title><?php echo $this->registry['__title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach($this->registry['__csp_header'] as $__csp_param=>$__csp_values)
			{
				echo $__csp_param;
				foreach($__csp_values as $__csp_value)
					echo ' '.$__csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $this->registry['__assets_path']; ?>/admin_panel.css">
		<?php
			if(isset($this->registry['__styles']))
				foreach($this->registry['__styles'] as $__style)
					{ ?><link rel="stylesheet" href="<?php echo $__style; ?>"><?php }
		?>
		<script src="<?php echo $this->registry['__assets_path']; ?>/admin_panel.js"></script>
		<?php
			if(isset($this->registry['__scripts']))
				foreach($this->registry['__scripts'] as $__script)
					{ ?><script src="<?php echo $__script; ?>"></script><?php }
		?>
		<meta name="robots" content="noindex,nofollow">
		<?php if(isset($this->registry['__html_headers'])) echo $this->registry['__html_headers']; ?>
	</head>
	<body>
		<div id="admin_header">
			<button id="menu_button"><?php echo $this->registry['__menu_button_label']; ?></button>
			<h1><?php echo $this->registry['__panel_label']; ?></h1>
			<div id="logout_button">
				<?php if(isset($this->registry['__show_logout_button'])) { ?>
					<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
						<input type="submit" name="logout" value="<?php echo $this->registry['__logout_button_label']; ?>">
						<input type="hidden" name="<?php echo $this->registry['__csrf_token']['name']; ?>" value="<?php echo $this->registry['__csrf_token']['value']; ?>">
					</form>
				<?php } ?>
			</div>
		</div>
		<div id="admin_content">
			<div id="admin_menu">
				<?php foreach($this->_list_modules() as $__module_name=>$__module_url) { ?>
					<div class="menu_button">
						<a href="<?php echo $__module_url; ?>"><?php echo $__module_name; ?></a>
					</div>
				<?php } ?>
			</div>
			<div id="admin_module">