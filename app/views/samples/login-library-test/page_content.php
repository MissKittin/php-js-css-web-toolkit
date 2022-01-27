<?php if(is_logged()) { ?>
	<form action="" method="post">
		<h1>Logout button</h1>
		<input type="submit" name="logout" value="Logout">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>
<?php } else { ?>
	<div>
		<p>Login: test<br>Password: test</p>
		<p>Second multi/callback login: test2<br>Second multi/callback login password: test2</p>
	</div>

	<form action="" method="post">
		<h1>Login single</h1>
		<input type="text" name="user" placeholder="Login"><br>
		<input type="password" name="password" placeholder="Password"><br>
		<input type="submit" value="Login">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>

	<form action="" method="post">
		<h1>Login multi</h1>
		<input type="text" name="user_multi" placeholder="Login"><br>
		<input type="password" name="password_multi" placeholder="Password"><br>
		<input type="submit" value="Login">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>

	<form action="" method="post">
		<h1>Login callback</h1>
		<input type="text" name="user_callback" placeholder="Login"><br>
		<input type="password" name="password_callback" placeholder="Password"><br>
		<input type="submit" value="Login">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>
<?php } ?>

<!-- all stuff above will be show independent if logged or not -->
<h1>Login status</h1>
<?php
	echo 'Single: ';
	if($view['login_failed_single'])
		echo 'Login failed';
	else if(is_logged())
		echo 'Logged';
	else
		echo 'Not logged';

	echo '<br>Multi: ';
	if($view['login_failed_multi'])
		echo 'Login failed';
	else if(is_logged())
		echo 'Logged';
	else
		echo 'Not logged';

	echo '<br>Callback: ';
	if($view['login_failed_callback'])
		echo 'Login failed';
	else if(is_logged())
		echo 'Logged';
	else
		echo 'Not logged';

	echo '<br>Logout: ';
	if($view['logout'])
		echo 'True';
	else
		echo 'False';
?>

<h1>Password hash</h1>
<form action="" method="post">
	<input type="password" name="passwdhash" placeholder="Password">
	<input type="submit" value="Generate hash">
	<?php
		if(isset($_POST['passwdhash']))
			echo 'Generated hash: ' . password_hash($_POST['passwdhash'], PASSWORD_BCRYPT);
	?>
</form>