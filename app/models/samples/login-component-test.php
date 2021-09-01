<?php $view['content']=function($view) { ?>
	<h1>Logged</h1>
	You successfully logged in.<br>
	Now you can click this fancy button below:<br>

	<form method="post" action="">
		<input type="submit" name="logout" value="Logout">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>
<?php }; ?>