<div>
	<h1>Create</h1>
	<form method="post" action="">
		Name: <input type="text" name="car_name"><br>
		Price: <input type="text" name="car_price"><br>
		<input type="submit" name="create" value="Add">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>
</div>

<div>
	<h1>Read</h1>
	<form method="post" action="">
		Name: <input type="text" name="car_name">
		<input type="submit" name="read" value="Search">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>

	<?php
		if(isset($view['do_read']))
			$view['print_found_records']($view);
	?>

	<h3>All records:</h3>
	<?php $view['print_all_records']($view); ?>
</div>

<div>
	<h1>Update</h1>
	<form method="post" action="">
		ID: <input type="text" name="car_id"><br>
		Name: <input type="text" name="car_name"><br>
		Price: <input type="text" name="car_price"><br>
		<input type="submit" name="update" value="Update">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>
</div>

<div>
	<h1>Delete</h1>
	<form method="post" action="">
		empty input &#9472;&#9654; remove all records<br>
		ID: <input type="text" name="car_id">
		Allow database flush: <input type="checkbox" name="delete_allow_db_flush" value="allow">
		<input type="submit" name="delete" value="Delete">
		<input type="hidden" name="<?php echo csrf_print_token('parameter'); ?>" value="<?php echo csrf_print_token('value'); ?>">
	</form>
</div>