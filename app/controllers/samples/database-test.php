<?php
	// select database for pdo_connect
	$pdo_connect_db='sqlite';
	//$pdo_connect_db='pgsql';
	//$pdo_connect_db='mysql';

	function if2switch($source_array, $param_array)
	{
		foreach($param_array as $param)
			if(isset($source_array[$param]))
				return $param;
	}

	include './app/shared/samples/default_http_headers.php';

	if(
		isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
		(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
	)
		ob_start('ob_gzhandler');

	include './app/shared/samples/session_start.php';

	include './lib/check_var.php';
	include './lib/sec_csrf.php';
	include './lib/pdo_connect.php';
	include './lib/pdo_crud_builder.php';

	include './app/templates/samples/default/default_template.php';
	$view=new default_template();

	include './app/models/samples/database_abstract.php';
	try {
		$db_cars=new database_abstract(
			'cars',
			'id',
			'name,price',
			new pdo_crud_builder([
				'pdo_handler'=>pdo_connect(
					'./app/databases/samples/'.$pdo_connect_db,
					function($error)
					{
						echo 'Database connection error: '.$error->getMessage();
						exit();
					}
				)
			])
		);
	} catch(Exception $error) {
		echo 'pdo_connect() error: '.$error->getMessage();
		exit();
	}
	$view['db_cars']=$db_cars;

	if(csrf_check_token('post'))
		switch(if2switch($_POST, ['create', 'read', 'update', 'delete']))
		{
			case 'create':
				$db_cars->create([
					$_POST['car_name'],
					$_POST['car_price']
				]);
			break;
			case 'read':
				$view['do_read']=true;
			break;
			case 'update':
				$db_cars->update(
					$_POST['car_id'],
					[
						$_POST['car_name'],
						$_POST['car_price']
					]
				);
			break;
			case 'delete':
				if(
					($_POST['car_id'] === '') &&
					(check_post('delete_allow_db_flush') === 'allow')
				)
					$db_cars->delete();
				else
					$db_cars->delete($_POST['car_id']);
			break;
		}

	$view->view('./app/views/samples/database-test');
?>