<?php $view['content']=function($view) { ?>
	<h1>PHP preprocessing test</h1>
	<h2>Is server windows? <?php echo $view['windows']? 'Yes' : 'No'; ?></h2>
	<h2>Is HTTP server is built-in php? <?php echo $view['builtin_server']? 'Yes' : 'No'; ?></h2>

	<?php if($view['cache-created']) echo '<h3>Cache file was created</h3>'; ?>
<?php }; ?>