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
				$this->registry['_inline_assets'][2]=rand_str_secure(32);
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
			if($this->registry['_inline_assets'][0])
			{
				?><style nonce="<?php echo $this->registry['_inline_assets'][2]; ?>"><?php
					if(!isset(
						$this->registry['_disable_assets']['simpleblog_materialized.css']
					)){
						if(is_file(
							__DIR__.'/../../lib/simpleblog_materialized.css'
						))
							readfile(
								__DIR__.'/../../lib/simpleblog_materialized.css'
							);
						else if(is_file(
							__DIR__.'/../../../../lib/simpleblog_materialized.css'
						))
							readfile(
								__DIR__.'/../../../../lib/simpleblog_materialized.css'
							);
						else
							echo '/* simpleblog_materialized.css library not found */';
					}

					if(
						(!isset(
							$this->registry['_disable_assets']['admin_panel_materialized.css']
						)) &&
						is_file(
							__DIR__.'/assets/admin_panel_materialized.css'
						)
					)
						readfile(__DIR__.'/assets/admin_panel_materialized.css');
				?></style><?php
			}
			else
			{
				if(!isset(
					$this->registry['_disable_assets']['simpleblog_materialized.css']
				))
					{ ?><link rel="stylesheet" href="<?php echo $this->registry['_assets_path']; ?>/<?php
						if(isset(
							$this->registry['_rename_assets']['simpleblog_materialized.css']
						))
							echo $this->registry['_rename_assets']['simpleblog_materialized.css'];
						else
							echo 'simpleblog_materialized.css';
					?>"><?php }

				if(!isset(
					$this->registry['_disable_assets']['admin_panel_materialized.css']
				))
					{ ?><link rel="stylesheet" href="<?php echo $this->registry['_assets_path']; ?>/<?php
						if(isset(
							$this->registry['_rename_assets']['admin_panel_materialized.css']
						))
							echo $this->registry['_rename_assets']['admin_panel_materialized.css'];
						else
							echo 'admin_panel_materialized.css';
					?>"><?php }
			}

			if(isset($this->registry['_styles']))
				foreach($this->registry['_styles'] as $_style)
					{ ?><link rel="stylesheet" href="<?php echo $_style; ?>"><?php }

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
		<div id="header" class="sb_header">
			<h1><?php echo $this->registry['_panel_label']; ?></h1>
			<div id="logout_button">
				<?php if(isset($this->registry['_show_logout_button'])) { ?>
					<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
						<input type="submit" name="<?php echo $this->registry['_logout_button_name']; ?>" value="<?php echo $this->registry['_logout_button_label']; ?>" class="button sb_button">
						<input type="hidden" name="<?php echo $this->registry['_csrf_token']['name']; ?>" value="<?php echo $this->registry['_csrf_token']['value']; ?>">
					</form>
				<?php } ?>
			</div>
		</div>
		<div id="headlinks" class="sb_headlinks">
			<?php foreach($this->_list_modules() as $_module_name=>$_module_data) { ?>
				<div class="headlink sb_headlink">
					<a href="<?php echo $_module_data['url']; ?>"><?php echo $_module_name; ?></a>
					<?php if($_module_data['id'] === $_module['id']) { ?><div class="headlink_active sb_headlink_active"></div><?php } ?>
				</div>
			<?php } ?>
		</div>
		<?php if(isset($_module['template_header'])) { ?>
			<div id="content_header" class="sb_content_header"><h3><?php echo $_module['template_header']; ?></h3></div>
		<?php } ?>
		<div id="content" class="sb_content">
			<?php
				if(
					isset($_module['class']) &&
					isset($_module['main_method'])
				)
					$_module['class']::{$_module['main_method']}($_module);
				else
					require $_module['path'].'/'.$_module['script'];
			?>
		</div>
		<?php
			foreach($this->view_plugins_body as $view_plugin)
				$view_plugin($this);
		?>
	</body>
</html>