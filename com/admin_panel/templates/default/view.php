<!DOCTYPE html>
<html<?php if(isset($this->registry['_lang'])) echo ' lang="'.$this->registry['_lang'].'"'; ?>>
	<head>
		<title><?php echo $this->registry['_title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach($this->view_plugins_csp as $view_plugin)
				$view_plugin($this);

			if($this->registry['_inline_assets'][0])
			{
				$this->registry['_inline_assets'][1]=rand_str_secure(32);
				$this->registry['_inline_assets'][2]=rand_str_secure(32);

				$this->registry['_csp_header']['script-src'][]='\'nonce-'.$this->registry['_inline_assets'][1].'\'';
				$this->registry['_csp_header']['style-src'][]='\'nonce-'.$this->registry['_inline_assets'][2].'\'';
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
			if(!isset(
				$this->registry['_disable_assets']['admin_panel_default.css']
			)){
				if($this->registry['_inline_assets'][0])
				{
					?><style nonce="<?php echo $this->registry['_inline_assets'][2]; ?>"><?php
						if(is_dir(__DIR__.'/assets/admin_panel_default.css'))
							foreach(
								array_diff(
									scandir(__DIR__.'/assets/admin_panel_default.css'),
									['.', '..']
								)
								as $inline_style
							)
								readfile(__DIR__.'/assets/admin_panel_default.css/'.$inline_style);
					?></style><?php
				}
				else
					{ ?><link rel="stylesheet" href="<?php echo $this->registry['_assets_path']; ?>/<?php
						if(isset(
							$this->registry['_rename_assets']['admin_panel_default.css']
						))
							echo $this->registry['_rename_assets']['admin_panel_default.css'];
						else
							echo 'admin_panel_default.css';
					?>"><?php }
			}

			if(isset($this->registry['_styles']))
				foreach($this->registry['_styles'] as $_style)
					{ ?><link rel="stylesheet" href="<?php echo $_style; ?>"><?php }

			if(!isset(
				$this->registry['_disable_assets']['admin_panel_default.js']
			)){
				if($this->registry['_inline_assets'][0])
				{
					?><script nonce="<?php echo $this->registry['_inline_assets'][1]; ?>"><?php
						if(is_file(__DIR__.'/assets/admin_panel_default.js'))
							readfile(__DIR__.'/assets/admin_panel_default.js');
					?></script><?php
				}
				else
					{ ?><script src="<?php echo $this->registry['_assets_path']; ?>/<?php
						if(isset(
							$this->registry['_rename_assets']['admin_panel_default.js']
						))
							echo $this->registry['_rename_assets']['admin_panel_default.js'];
						else
							echo 'admin_panel_default.js';
					?>"></script><?php }
			}

			if(isset($this->registry['_scripts']))
				foreach($this->registry['_scripts'] as $_script)
					{ ?><script src="<?php echo $_script; ?>"></script><?php }
		?>
		<meta name="robots" content="noindex,nofollow">
		<?php
			if(isset($this->registry['_html_headers']))
				echo $this->registry['_html_headers'];

			if(isset($this->registry['_favicon']))
				readfile($this->registry['_favicon']);

			foreach($this->view_plugins_head as $view_plugin)
				$view_plugin($this);
		?>
	</head>
	<body>
		<div id="admin_header">
			<button id="menu_button"><?php echo $this->registry['_menu_button_label']; ?></button>
			<h1><?php echo $this->registry['_panel_label']; ?></h1>
			<div id="logout_button">
				<?php if(isset($this->registry['_show_logout_button'])) { ?>
					<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
						<input type="submit" name="<?php echo $this->registry['_logout_button_name']; ?>" value="<?php echo $this->registry['_logout_button_label']; ?>">
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
				<?php }
					if(
						isset($_module['class']) &&
						isset($_module['main_method'])
					)
						$_module['class']::{$_module['main_method']}($_module);
					else
						require $_module['path'].'/'.$_module['script'];
				?>
			</div>
		</div>
		<?php
			foreach($this->view_plugins_body as $view_plugin)
				$view_plugin($this);
		?>
	</body>
</html>