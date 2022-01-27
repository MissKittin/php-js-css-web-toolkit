<!DOCTYPE html>
<html<?php if(isset($view['lang'])) echo ' lang="'.$view['lang'].'"'; ?>>
	<head>
		<title><?php if(isset($view['title'])) echo $view['title']; ?></title>
		<meta charset="utf-8">
		<?php self::parse_headers($view); ?>
		<link rel="stylesheet" href="/assets/default.css">
		<meta name="viewport" content="width=device-width,initial-scale=1">
	</head>
	<body>