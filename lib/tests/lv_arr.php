<?php
	/*
	 * lv_arr.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  var_export_contains.php library is required
	 */

	echo ' -> Including var_export_contains.php';
		if(is_file(__DIR__.'/../lib/var_export_contains.php'))
		{
			if(@(include __DIR__.'/../lib/var_export_contains.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../var_export_contains.php'))
		{
			if(@(include __DIR__.'/../var_export_contains.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing lv_arr_accessible';
		if(lv_arr_accessible(['a'=>1, 'b'=>2]))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_accessible('abc'))
		{
			echo ' [FAIL]';
			$failed=true;
		}
		else
			echo ' [ OK ]';
		if(lv_arr_accessible(new stdClass()))
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Testing lv_arr_collapse';
		if(var_export_contains(
			lv_arr_collapse([[1, 2, 3], [4, 5, 6], [7, 8, 9]]),
			'array(0=>1,1=>2,2=>3,3=>4,4=>5,5=>6,6=>7,7=>8,8=>9,)'
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_cross_join';
		if(var_export_contains(
			lv_arr_cross_join([1, 2], ['a', 'b']),
			"array(0=>array(0=>1,1=>'a',),1=>array(0=>1,1=>'b',),2=>array(0=>2,1=>'a',),3=>array(0=>2,1=>'b',),)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(var_export_contains(
			lv_arr_cross_join([1, 2], ['a', 'b'], ['I', 'II']),
			"array(0=>array(0=>1,1=>'a',2=>'I',),1=>array(0=>1,1=>'a',2=>'II',),2=>array(0=>1,1=>'b',2=>'I',),3=>array(0=>1,1=>'b',2=>'II',),4=>array(0=>2,1=>'a',2=>'I',),5=>array(0=>2,1=>'a',2=>'II',),6=>array(0=>2,1=>'b',2=>'I',),7=>array(0=>2,1=>'b',2=>'II',),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_divide';
		if(lv_arr_divide(['name'=>'Desk'])[0][0] === 'name')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_divide(['name'=>'Desk'])[1][0] === 'Desk')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_dot';
		if(var_export_contains(
			lv_arr_dot(['products'=>['desk'=>['price'=>100]]]),
			"array('products.desk.price'=>100,)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_exists';
		if(lv_arr_exists(['name'=>'John Doe', 'age'=>17], 'name'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_exists(['name'=>'John Doe', 'age'=>17], 'salary'))
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Testing lv_arr_flatten';
		if(var_export_contains(
			lv_arr_flatten(['name'=>'Joe', 'languages'=>['PHP', 'Ruby']]),
			"array(0=>'Joe',1=>'PHP',2=>'Ruby',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_forget';
		$lv_arr_forget_array=['products'=>['desk'=>['price'=>100]]];
		lv_arr_forget($lv_arr_forget_array, 'products.desk');
		if(var_export_contains($lv_arr_forget_array, "array('products'=>array(),)"))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_get';
		if(lv_arr_get(
			['products'=>['desk'=>['price'=>100]]],
			'products.desk.price')
		=== 100)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_get(
			['products'=>['desk'=>['price'=>100]]],
			'products.desk.discount', 0)
		=== 0)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_has';
		if(lv_arr_has(
			['product'=>['name'=>'Desk', 'price'=>100]],
			'product.name'
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_has(
			['product'=>['name'=>'Desk', 'price'=>100]],
			['product.price', 'product.discount']
		)){
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Testing lv_arr_has_any';
		if(lv_arr_has_any(
			['product'=>['name'=>'Desk', 'price'=>100]],
			'product.name'
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_has_any(
			['product'=>['name'=>'Desk', 'price'=>100]],
			['product.price', 'product.discount']
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_has_any(
			['product'=>['name'=>'Desk', 'price'=>100]],
			['category', 'product.discount']
		)){
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
		else
			echo ' [ OK ]'.PHP_EOL;
	echo ' -> Testing lv_arr_key_by';
		if(var_export_contains(
			lv_arr_key_by(
				[
					['product_id'=>'prod-100', 'name'=>'Desk'],
					['product_id'=>'prod-200', 'name'=>'Chair']
				],
				'product_id'
			),
			"array('prod-100'=>array('product_id'=>'prod-100','name'=>'Desk',),'prod-200'=>array('product_id'=>'prod-200','name'=>'Chair',),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_pluck';
		if(var_export_contains(
			lv_arr_pluck(
				[
					['developer'=>['id'=>1, 'name'=>'Taylor']],
					['developer'=>['id'=>2, 'name'=>'Abigail']]
				],
				'developer.name'
			),
			"array(0=>'Taylor',1=>'Abigail',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_query';
		if(lv_arr_query([
				'name'=>'Taylor',
				'order'=>[
					'column'=>'created_at',
					'direction'=>'desc'
				]
			])
			===
			'name=Taylor&order%5Bcolumn%5D=created_at&order%5Bdirection%5D=desc'
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_set';
		$lv_arr_set_array=['products'=>['desk'=>['price'=>100]]];
		lv_arr_set($lv_arr_set_array, 'products.desk.price', 200);
		if(var_export_contains(
			$lv_arr_set_array,
			"array('products'=>array('desk'=>array('price'=>200,),),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_undot';
		if(var_export_contains(
			lv_arr_undot([
				'user.name'=>'Kevin Malone',
				'user.occupation'=>'Accountant'
			]),
			"array('user'=>array('name'=>'KevinMalone','occupation'=>'Accountant',),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_data_fill'; // NIE DZIALA
		$lv_arr_data_fill_array=['products'=>['desk'=>['price'=>100]]];
		lv_arr_data_fill($lv_arr_data_fill_array, 'products.desk.price', 200);
		if(var_export_contains(
			$lv_arr_data_fill_array,
			"array('products'=>array('desk'=>array('price'=>100,),),)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		lv_arr_data_fill($lv_arr_data_fill_array, 'products.desk.discount', 10);
		if(var_export_contains(
			$lv_arr_data_fill_array,
			"array('products'=>array('desk'=>array('price'=>100,'discount'=>10,),),)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		$lv_arr_data_fill_array=[
			'products'=>[
				['name'=>'Desk 1', 'price'=>100],
				['name'=>'Desk 2']
			]
		];
		lv_arr_data_fill($lv_arr_data_fill_array, 'products.*.price', 200);
		if(var_export_contains(
			$lv_arr_data_fill_array,
			"array('products'=>array(0=>array('name'=>'Desk1','price'=>100,),1=>array('name'=>'Desk2','price'=>200,),),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_data_get';
		if(lv_arr_data_get(['products'=>['desk'=>['price'=>100]]], 'products.desk.price') === 100)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_data_get(['products'=>['desk'=>['price'=>100]]], 'products.desk.discount', 0) === 0)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(var_export_contains(
			lv_arr_data_get(
				[
					'product-one'=>['name'=>'Desk 1', 'price'=>100],
					'product-two'=>['name'=>'Desk 2', 'price'=>150]
				],
				'*.name'
			),
			"array(0=>'Desk1',1=>'Desk2',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_data_set';
		$lv_arr_data_set_array=['products'=>['desk'=>['price'=>100]]];
		lv_arr_data_set($lv_arr_data_set_array, 'products.desk.price', 200);
		if(var_export_contains(
			$lv_arr_data_set_array,
			"array('products'=>array('desk'=>array('price'=>200,),),)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		$lv_arr_data_set_array=[
			'products'=>[
				['name'=>'Desk 1', 'price'=>100],
				['name'=>'Desk 2', 'price'=>150]
			]
		];
		lv_arr_data_set($lv_arr_data_set_array, 'products.*.price', 200);
		if(var_export_contains(
			$lv_arr_data_set_array,
			"array('products'=>array(0=>array('name'=>'Desk1','price'=>200,),1=>array('name'=>'Desk2','price'=>200,),),)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		$lv_arr_data_set_array=['products'=>['desk'=>['price'=>100]]];
		lv_arr_data_set($data, 'products.desk.price', 200, false);
		if(var_export_contains(
			$lv_arr_data_set_array,
			"array('products'=>array('desk'=>array('price'=>100,),),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_data_forget';
		$lv_arr_data_forget_array=['products'=>['desk'=>['price'=>100]]];
		lv_arr_data_forget($lv_arr_data_forget_array, 'products.desk.price');
		if(var_export_contains(
			$lv_arr_data_forget_array,
			"array('products'=>array('desk'=>array(),),)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		$lv_arr_data_forget_array=[
			'products'=>[
				['name'=>'Desk 1', 'price'=>100],
				['name'=>'Desk 2', 'price'=>150]
			]
		];
		lv_arr_data_forget($lv_arr_data_forget_array, 'products.*.price');
		if(var_export_contains(
			$lv_arr_data_forget_array,
			"array('products'=>array(0=>array('name'=>'Desk1',),1=>array('name'=>'Desk2',),),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_value';
		if(lv_arr_value(true))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(lv_arr_value(function(){
			return false;
		})){
			echo ' [FAIL]';
			$failed=true;
		}
		else
			echo ' [ OK ]';
		if(lv_arr_value(
			function(string $name){
				return $name;
			},
			'Taylor'
		) === 'Taylor')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>