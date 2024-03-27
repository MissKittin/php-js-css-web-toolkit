<!DOCTYPE html>
<html lang="<?php echo $view['lang']; ?>">
	<head>
		<title><?php echo $view['title']; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="<?php
			foreach($view['csp_header'] as $csp_param=>$csp_values)
			{
				echo $csp_param;
				foreach($csp_values as $csp_value)
					echo ' '.$csp_value;
				echo ';';
			}
		?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			if($view['inline_style'])
			{
				?><style nonce="mainstyle"><?php
					if(is_file(__DIR__.'/../../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../../lib/simpleblog_materialized.css');
					else if(is_file(__DIR__.'/../../../../lib/simpleblog_materialized.css'))
						readfile(__DIR__.'/../../../../lib/simpleblog_materialized.css');
					else
						echo '/* simpleblog_materialized.css library not found */';

					if(is_dir(__DIR__.'/assets/'.$view['middleware_form_style']))
						foreach(
							array_diff(
								scandir(__DIR__.'/assets/'.$view['middleware_form_style']),
								['.', '..']
							)
							as $inline_style
						)
							readfile(__DIR__.'/assets/'.$view['middleware_form_style'].'/'.$inline_style);
				?></style><?php
			}
			else
			{ ?>
				<link rel="stylesheet" href="<?php echo $view['assets_path']; ?>/simpleblog_materialized.css">
				<link rel="stylesheet" href="<?php echo $view['assets_path']; ?>/<?php echo $view['middleware_form_style']; ?>">
			<?php }
		?>
		<meta name="robots" content="noindex,nofollow">
		<?php
			if(isset($view['html_headers']))
				echo $view['html_headers'];
		?>
	</head>
	<body>
		<div id="middleware_form">
			<form method="post" action="">
				<?php $this->parse_fields($view); ?>
				<div class="input_button"><input class="sb_full_button" type="submit" name="middleware_form" value="<?php echo $view['submit_button_label']; ?>"></div>
				<?php if(isset($view['error_message'])) { ?>
					<div class="message_container">
						<?php echo $view['error_message']; ?>
					</div>
				<?php } ?>
				<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
			</form>
		</div>
	</body>
</html>