<!DOCTYPE html>
<html<?php if(isset($view['lang'])) echo ' lang="' . $view['lang'] . '"'; ?>>
	<head>
		<title><?php if(isset($view['title'])) echo $view['title']; ?></title>
		<meta charset="utf-8">
		<!-- <meta http-equiv="Content-Security-Policy" content="script-src 'self'; style-src 'self';"> -->
		<link rel="stylesheet" href="/assets/default.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			if(isset($view['html_headers'])) echo $view['html_headers'];
			if(isset($view['styles']))
				foreach($view['styles'] as $style)
					echo '<link rel="stylesheet" href="'.$style.'">';
		?>
	</head>
	<body>
		<?php $view['content']($view); ?>
		<script src="/assets/default.js"></script>
		<?php
			if(isset($view['scripts']))
				foreach($view['scripts'] as $script)
					echo '<script src="'.$script.'"></script>';
		?>
	</body>
</html>