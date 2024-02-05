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
		<link rel="stylesheet" href="<?php echo $this->registry['_assets_path']; ?>/admin_panel_materialized.css">
		<link rel="stylesheet" href="<?php echo $this->registry['_assets_path']; ?>/simpleblog_materialized.css">
		<?php
			if(isset($this->registry['_styles']))
				foreach($this->registry['_styles'] as $_style)
					{ ?><link rel="stylesheet" href="<?php echo $_style; ?>"><?php }
		?>
		<!--<script src="<?php echo $this->registry['_assets_path']; ?>/admin_panel_default.js"></script>-->
		<?php
			if(isset($this->registry['_scripts']))
				foreach($this->registry['_scripts'] as $_script)
					{ ?><script src="<?php echo $_script; ?>"></script><?php }
		?>
		<meta name="robots" content="noindex,nofollow">
		<?php if(isset($this->registry['_html_headers'])) echo $this->registry['_html_headers']; ?>
	</head>
	<body>
		<div id="header">
			<h1><?php echo $this->registry['_panel_label']; ?></h1>
			<div id="logout_button">
				<?php if(isset($this->registry['_show_logout_button'])) { ?>
					<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
						<input type="submit" name="logout" value="<?php echo $this->registry['_logout_button_label']; ?>" class="button">
						<input type="hidden" name="<?php echo $this->registry['_csrf_token']['name']; ?>" value="<?php echo $this->registry['_csrf_token']['value']; ?>">
					</form>
				<?php } ?>
			</div>
		</div>
		<div id="headlinks">
			<?php foreach($this->_list_modules() as $_module_name=>$_module_data) { ?>
				<div class="headlink">
					<a href="<?php echo $_module_data['url']; ?>"><?php echo $_module_name; ?></a>
					<?php if($_module_data['id'] === $_module['id']) { ?><div class="headlink_active"></div><?php } ?>
				</div>
			<?php } ?>
		</div>
		<?php if(isset($_module['template_header'])) { ?>
			<div id="content_header"><h3><?php echo $_module['template_header']; ?></h3></div>
		<?php } ?>
		<div id="content">