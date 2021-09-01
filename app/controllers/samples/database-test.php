<?php
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 0');
	header('X-Content-Type-Options: nosniff');

	include './lib/pdo_connect.php';
	include './lib/pdo_crud_builder.php';
	include './app/shared/samples/database_abstract.php';
	include './lib/sec_csrf.php';

	$db_cars=new database_abstract(
		'cars',
		'id',
		'name,price',
		new pdo_crud_builder(pdo_connect('./app/databases/samples/sqlite'))
	);

	$view['lang']='en';
	$view['title']='Database';
	$view['db_cars']=$db_cars;
	$view['print_found_records']=function(){};
	$view['print_all_records']=function($view)
	{
		if(!$view['render_table']($view['db_cars']->read()))
			echo 'No records found';
	};

	if(csrf_check_token('post'))
	{
		// Create
		if(isset($_POST['create']))
			$db_cars->create(array([$_POST['car_name'], $_POST['car_price']]));

		// Read
		if(isset($_POST['read']))
			$view['print_found_records']=function($view)
			{
				echo '<h3>Search results:</h3>';
				if(!$view['render_table']($view['db_cars']->read('name', $_POST['car_name'])))
					echo 'No records found';
			};

		// Update
		if(isset($_POST['update']))
			$db_cars->update($_POST['car_id'], array($_POST['car_name'], $_POST['car_price']));

		// Delete
		if(isset($_POST['delete']))
		{
			if(($_POST['car_id'] === '') && ($_POST['delete_allow_db_flush'] === 'allow'))
				$db_cars->delete();
			else
				$db_cars->delete($_POST['car_id']);
		}
	}

	include './app/models/samples/database-test.php';
	include './app/views/samples/default/default.php';

	unset($view['db_cars']);
	unset($db_cars);
?>