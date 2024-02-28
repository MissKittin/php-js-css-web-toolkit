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
	echo ' -> Testing lv_arr_collect [LATR]'.PHP_EOL;
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
	echo ' -> Testing lv_arr_except';
		if(var_export_contains(
			lv_arr_except(['name'=>'Desk', 'price'=>100], ['price']),
			"array('name'=>'Desk',)"
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
	echo ' -> Testing lv_arr_first';
		if(lv_arr_first([100, 200, 300], function(int $value, int $key){
			return ($value >= 150);
		}) === 200)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
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
	echo ' -> Testing lv_arr_last';
		if(lv_arr_last([100, 200, 300, 110], function(int $value, int $key){
			return ($value >= 150);
		}) === 300)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_map';
		if(var_export_contains(
			lv_arr_map(['first'=>'james', 'last'=>'kirk'], function(string $value, string $key){
				return ucfirst($value);
			}),
			"array('first'=>'James','last'=>'Kirk',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_map_with_keys';
		if(var_export_contains(
			lv_arr_map_with_keys(
				[
					[
						'name'=>'John',
						'department'=>'Sales',
						'email'=>'john@example.com',
					],
					[
						'name'=>'Jane',
						'department'=>'Marketing',
						'email'=>'jane@example.com',
					]
				],
				function(array $item, int $key){
					return [
						$item['email']=>$item['name']
					];
				}
			),
			"array('john@example.com'=>'John','jane@example.com'=>'Jane',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_only';
		if(var_export_contains(
			lv_arr_only(
				['name'=>'Desk', 'price'=>100, 'orders'=>10],
				['name', 'price']
			),
			"array('name'=>'Desk','price'=>100,)"
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
	echo ' -> Testing lv_arr_prepend';
		if(var_export_contains(
			lv_arr_prepend(['one', 'two', 'three', 'four'], 'zero'),
			"array(0=>'zero',1=>'one',2=>'two',3=>'three',4=>'four',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_pull';
		$lv_arr_pull_array=['name'=>'Desk', 'price'=>100];
		if(lv_arr_pull($lv_arr_pull_array, 'name') === 'Desk')
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$failed=true;
		}
		if(var_export_contains($lv_arr_pull_array, "array('price'=>100,)"))
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
	echo ' -> Testing lv_arr_random';
		$lv_arr_random_number=lv_arr_random([1, 2, 'A']);
		if($lv_arr_random_number === 1)
			echo ' [OK 1]'.PHP_EOL;
		else if($lv_arr_random_number === 2)
			echo ' [OK 2]'.PHP_EOL;
		else if($lv_arr_random_number === 'A')
			echo ' [OK A]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_select [LATR]'.PHP_EOL;
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
	echo ' -> Testing lv_arr_shuffle';
		switch(var_export_contains(lv_arr_shuffle([1, 2, 3]), '', true))
		{
			case "array(0=>1,1=>2,2=>3,)":
				echo ' [OK 1]'.PHP_EOL;
			break;
			case "array(0=>2,1=>1,2=>3,)":
				echo ' [OK 2]'.PHP_EOL;
			break;
			case "array(0=>2,1=>3,2=>1,)":
				echo ' [OK 3]'.PHP_EOL;
			break;
			case "array(0=>3,1=>2,2=>1,)":
				echo ' [OK 4]'.PHP_EOL;
			break;
			case "array(0=>3,1=>1,2=>2,)":
				echo ' [OK 5]'.PHP_EOL;
			break;
			case "array(0=>1,1=>3,2=>2,)":
				echo ' [OK 6]'.PHP_EOL;
			break;
			default:
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
	echo ' -> Testing lv_arr_where';
		if(var_export_contains(
			lv_arr_where([100, '200', 300, '400', 500], function($value, int $key){
				return is_string($value);
			}),
			"array(1=>'200',3=>'400',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_wrap';
		if(var_export_contains(
			lv_arr_wrap('Laravel'),
			"array(0=>'Laravel',)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}
	echo ' -> Testing lv_arr_data_fill';
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

	echo ' -> Testing lv_arr_collection'.PHP_EOL;
		echo '  -> all';
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3])->all(), '', true).']';
			if(var_export_contains(lv_arr_collect([1, 2, 3])->all(), "array(0=>1,1=>2,2=>3,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> average [SKIP]'.PHP_EOL;
		echo '  -> avg';
			//echo ' ['.var_export_contains(lv_arr_collect([
			//	['foo'=>10],
			//	['foo'=>10],
			//	['foo'=>20],
			//	['foo'=>40]
			//])->avg('foo'), '', true).']';
			if(lv_arr_collect([
				['foo'=>10],
				['foo'=>10],
				['foo'=>20],
				['foo'=>40]
			])->avg('foo') === 20)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($average=lv_arr_collect([1, 1, 2, 4])->avg(), '', true).']';
			if($average=lv_arr_collect([1, 1, 2, 4])->avg() === 2)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> chunk';
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7]);
			$chunks=$collection->chunk(4);
			//echo ' ['.var_export_contains($chunks->all(), '', true).']';
			if(var_export_contains(
				$chunks->all(),
				"array(0=>lv_arr_collection::__set_state(array('items'=>array(0=>1,1=>2,2=>3,3=>4,),)),1=>lv_arr_collection::__set_state(array('items'=>array(4=>5,5=>6,6=>7,),)),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> collapse';
			$collection=lv_arr_collect([
				[1, 2, 3],
				[4, 5, 6],
				[7, 8, 9]
			]);
			$collapsed=$collection->collapse();
			//echo ' ['.var_export_contains($collapsed->all(), '', true).']';
			if(var_export_contains(
				$collapsed->all(),
				"array(0=>1,1=>2,2=>3,3=>4,4=>5,5=>6,6=>7,7=>8,8=>9,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> collect';
			$collection_a=lv_arr_collect([1, 2, 3]);
			$collection_b=$collection_a->collect();
			//echo ' ['.var_export_contains($collection_b->all(), '', true).']';
			if(var_export_contains($collection_b->all(), "array(0=>1,1=>2,2=>3,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> combine';
			$collection=lv_arr_collect(['name', 'age']);
			$combined=$collection->combine(['George', 29]);
			//echo ' ['.var_export_contains($combined->all(), '', true).']';
			if(var_export_contains(
				$combined->all(),
				"array('name'=>'George','age'=>29,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> concat';
			$collection=lv_arr_collect(['John Doe']);
			$concatenated=$collection->concat(['Jane Doe'])->concat(['name'=>'Johnny Doe']);
			//echo ' ['.var_export_contains($concatenated->all(), '', true).']';
			if(var_export_contains(
				$concatenated->all(),
				"array(0=>'JohnDoe',1=>'JaneDoe',2=>'JohnnyDoe',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> contains';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			//echo ' ['.var_export_contains($collection->contains(function(int $value, int $key){
			//	return ($value > 5);
			//}), '', true).']';
			if($collection->contains(function(int $value, int $key){
				return ($value > 5);
			}) == false)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>100]);
			//echo ' ['.var_export_contains($collection->contains('Desk'), '', true).']';
			if($collection->contains('Desk') === true)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->contains('New York'), '', true).']';
			if($collection->contains('New York') === false)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100]
			]);
			//echo ' ['.var_export_contains($collection->contains('product', 'Bookcase'), '', true).']';
			if($collection->contains('product', 'Bookcase') === false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> contains_one_item';
			//echo ' ['.var_export_contains(lv_arr_collect([])->contains_one_item(), '', true).']';
			if(lv_arr_collect([])->contains_one_item() === false)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect(['1'])->contains_one_item(), '', true).']';
			if(lv_arr_collect(['1'])->contains_one_item() === true)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect(['1', '2'])->contains_one_item(), '', true).']';
			if(lv_arr_collect(['1', '2'])->contains_one_item() === false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> contains_strict [SKIP]'.PHP_EOL;
		echo '  -> count';
			$collection=lv_arr_collect([1, 2, 3, 4]);
			//echo ' ['.var_export_contains($collection->count(), '', true).']';
			if($collection->count() === 4)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> cross_join';
			$collection=lv_arr_collect([1, 2]);
			$matrix=$collection->cross_join(['a', 'b']);
			//echo ' ['.var_export_contains($matrix->all(), '', true).']';
			if(var_export_contains(
				$matrix->all(),
				"array(0=>array(0=>1,1=>'a',),1=>array(0=>1,1=>'b',),2=>array(0=>2,1=>'a',),3=>array(0=>2,1=>'b',),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2]);
			$matrix=$collection->cross_join(['a', 'b'], ['I', 'II']);
			//echo ' ['.var_export_contains($matrix->all(), '', true).']';
			if(var_export_contains(
				$matrix->all(),
				"array(0=>array(0=>1,1=>'a',2=>'I',),1=>array(0=>1,1=>'a',2=>'II',),2=>array(0=>1,1=>'b',2=>'I',),3=>array(0=>1,1=>'b',2=>'II',),4=>array(0=>2,1=>'a',2=>'I',),5=>array(0=>2,1=>'a',2=>'II',),6=>array(0=>2,1=>'b',2=>'I',),7=>array(0=>2,1=>'b',2=>'II',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> diff';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$diff=$collection->diff([2, 4, 6, 8]);
			//echo ' ['.var_export_contains($diff->all(), '', true).']';
			if(var_export_contains($diff->all(), "array(0=>1,2=>3,4=>5,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> diff_assoc';
			$collection=lv_arr_collect([
				'color'=>'orange',
				'type'=>'fruit',
				'remain'=>6
			]);
			$diff=$collection->diff_assoc([
				'color'=>'yellow',
				'type'=>'fruit',
				'remain'=>3,
				'used'=>6
			]);
			//echo ' ['.var_export_contains($diff->all(), '', true).']';
			if(var_export_contains(
				$diff->all(),
				"array('color'=>'orange','remain'=>6,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> diff_assoc_using';
			$collection=lv_arr_collect([
				'color'=>'orange',
				'type'=>'fruit',
				'remain'=>6
			]);
			$diff=$collection->diff_assoc_using([
				'Color'=>'yellow',
				'Type'=>'fruit',
				'Remain'=>3
			], 'strnatcasecmp');
			//echo ' ['.var_export_contains($diff->all(), '', true).']';
			if(var_export_contains(
				$diff->all(),
				"array('color'=>'orange','remain'=>6,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> diff_keys';
			$collection=lv_arr_collect([
				'one'=>10,
				'two'=>20,
				'three'=>30,
				'four'=>40,
				'five'=>50
			]);
			$diff=$collection->diff_keys([
				'two'=>2,
				'four'=>4,
				'six'=>6,
				'eight'=>8
			]);
			//echo ' ['.var_export_contains($diff->all(), '', true).']';
			if(var_export_contains(
				$diff->all(),
				"array('one'=>10,'three'=>30,'five'=>50,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> doesnt_contain';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			//echo ' ['.var_export_contains($collection->doesnt_contain(function(int $value, int $key){
			//	return ($value < 5);
			//}), '', true).']';
			if($collection->doesnt_contain(function(int $value, int $key){
				return ($value < 5);
			}) === false)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>100]);
			//echo ' ['.var_export_contains($collection->doesnt_contain('Table'), '', true).']';
			if($collection->doesnt_contain('Table') === true)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->doesnt_contain('Desk'), '', true).']';
			if($collection->doesnt_contain('Desk') === false)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100]
			]);
			//echo ' ['.var_export_contains($collection->doesnt_contain('product', 'Bookcase'), '', true).']';
			if($collection->doesnt_contain('product', 'Bookcase') === true)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> dot';
			$collection=lv_arr_collect(['products'=>['desk'=>['price'=>100]]]);
			$flattened=$collection->dot();
			//echo ' ['.var_export_contains($flattened->all(), '', true).']';
			if(var_export_contains(
				$flattened->all(),
				"array('products.desk.price'=>100,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> duplicates';
			$collection=lv_arr_collect(['a', 'b', 'a', 'c', 'b']);
			//echo ' ['.var_export_contains($collection->duplicates(), '', true).']';
			if(var_export_contains(
				$collection->duplicates(),
				"lv_arr_collection::__set_state(array('items'=>array(2=>'a',4=>'b',),))"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$employees=lv_arr_collect([
				['email'=>'abigail@example.com', 'position'=>'Developer'],
				['email'=>'james@example.com', 'position'=>'Designer'],
				['email'=>'victoria@example.com', 'position'=>'Developer']
			]);
			//echo ' ['.var_export_contains($employees->duplicates('position'), '', true).']';
			if(var_export_contains(
				$employees->duplicates('position'),
				"lv_arr_collection::__set_state(array('items'=>array(2=>'Developer',),))"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> duplicates_strict [SKIP]'.PHP_EOL;
		echo '  -> each';
			$GLOBALS['each_f']='';
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$collection->each(function(int $item, int $key){
				$GLOBALS['each_f'].=$key.$item;
			});
			//echo ' ['.var_export_contains($GLOBALS['each_f'], '', true).']';
			if($GLOBALS['each_f'] === '01122334')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> each_spread';
			$GLOBALS['each_spread_ret']='';
			$collection=lv_arr_collect([['John Doe', 35], ['Jane Doe', 33]]);
			$collection->each_spread(function(string $name, int $age){
				$GLOBALS['each_spread_ret'].=$name.$age;
			});
			//echo $GLOBALS['each_spread_ret'];
			if($GLOBALS['each_spread_ret'] === 'John Doe35Jane Doe33')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> ensure';
			try {
				//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3])->ensure('int'), '', true).']';
				if(var_export_contains(
					lv_arr_collect([1, 2, 3])->ensure('int'),
					"lv_arr_collection::__set_state(array('items'=>array(0=>1,1=>2,2=>3,),))"
				))
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
			} catch(lv_arr_exception $error) {
				echo ' [FAIL]';
				$failed=true;
			}
			try {
				lv_arr_collect([1, 2, 3])->ensure('float');
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			} catch(lv_arr_exception $error) {
				echo ' [ OK ]'.PHP_EOL;
			}
		echo '  -> every';
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4])->every(function(int $value, int $key){
			//	return ($value > 2);
			//}), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4])->every(function(int $value, int $key){
				return ($value > 2);
			}) === false)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([]);
			//echo ' ['.var_export_contains($collection->every(function(int $value, int $key){
			//	return ($value > 2);
			//}), '', true).']';
			if($collection->every(function(int $value, int $key){
				return ($value > 2);
			}) === true)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> except';
			$collection=lv_arr_collect(['product_id'=>1, 'price'=>100, 'discount'=>false]);
			$filtered=$collection->except(['price', 'discount']);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains($filtered->all(), "array('product_id'=>1,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> filter';
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$filtered=$collection->filter(function(int $value, int $key){
				return ($value > 2);
			});
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains($filtered->all(), "array(2=>3,3=>4,)"))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3, null, false, '', 0, []]);
			//echo ' ['.var_export_contains($collection->filter()->all(), '', true).']';
			if(var_export_contains($collection->filter()->all(), "array(0=>1,1=>2,2=>3,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> first';
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4])->first(function(int $value, int $key){
			//	return ($value > 2);
			//}), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4])->first(function(int $value, int $key){
				return ($value > 2);
			}) === 3)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4])->first(), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4])->first() === 1)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> first_or_fail';
			try {
				lv_arr_collect([1, 2, 3, 4])->first_or_fail(function(int $value, int $key){
					return ($value > 5);
				});
				echo ' [FAIL]';
				$failed=true;
			} catch(lv_arr_exception $error) {
				echo ' [ OK ]';
			}
			try {
				lv_arr_collect([])->first_or_fail();
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			} catch(lv_arr_exception $error) {
				echo ' [ OK ]'.PHP_EOL;
			}
		echo '  -> first_where';
			$collection=lv_arr_collect([
				['name'=>'Regena', 'age'=>null],
				['name'=>'Linda', 'age'=>14],
				['name'=>'Diego', 'age'=>23],
				['name'=>'Linda', 'age'=>84]
			]);
			//echo ' ['.var_export_contains($collection->first_where('name', 'Linda'), '', true).']';
			if(var_export_contains(
				$collection->first_where('name', 'Linda'),
				"array('name'=>'Linda','age'=>14,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->first_where('age', '>=', 18), '', true).']';
			if(var_export_contains(
				$collection->first_where('age', '>=', 18),
				"array('name'=>'Diego','age'=>23,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->first_where('age'), '', true).']';
			if(var_export_contains(
				$collection->first_where('age'),
				"array('name'=>'Linda','age'=>14,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> flat_map';
			$collection=lv_arr_collect([
				['name'=>'Sally'],
				['school'=>'Arkansas'],
				['age'=>28]
			]);
			$flattened=$collection->flat_map(function(array $values){
				return array_map('strtoupper', $values);
			});
			//echo ' ['.var_export_contains($flattened->all(), '', true).']';
			if(var_export_contains(
				$flattened->all(),
				"array('name'=>'SALLY','school'=>'ARKANSAS','age'=>'28',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> flatten';
			$collection=lv_arr_collect([
				'name'=>'taylor',
				'languages'=>['php', 'javascript']
			]);
			$flattened=$collection->flatten();
			//echo ' ['.var_export_contains($flattened->all(), '', true).']';
			if(var_export_contains(
				$flattened->all(),
				"array(0=>'taylor',1=>'php',2=>'javascript',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				'Apple'=>[
					[
						'name'=>'iPhone 6S',
						'brand'=>'Apple'
					]
				],
				'Samsung'=>[
					[
						'name'=>'Galaxy S7',
						'brand'=>'Samsung'
					]
				]
			]);
			$products=$collection->flatten(1);
			//echo ' ['.var_export_contains($products->values()->all(), '', true).']';
			if(var_export_contains(
				$products->values()->all(),
				"array(0=>array('name'=>'iPhone6S','brand'=>'Apple',),1=>array('name'=>'GalaxyS7','brand'=>'Samsung',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> flip';
			$collection=lv_arr_collect(['name'=>'taylor', 'framework'=>'laravel']);
			$flipped=$collection->flip();
			//echo ' ['.var_export_contains($flipped->all(), '', true).']';
			if(var_export_contains(
				$flipped->all(),
				"array('taylor'=>'name','laravel'=>'framework',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> forget';
			$collection=lv_arr_collect(['name'=>'taylor', 'framework'=>'laravel']);
			$collection->forget('name');
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains($collection->all(), "array('framework'=>'laravel',)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> for_page';
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
			$chunk=$collection->for_page(2, 3);
			//echo ' ['.var_export_contains($chunk->all(), '', true).']';
			if(var_export_contains($chunk->all(), "array(3=>4,4=>5,5=>6,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> get';
			$collection=lv_arr_collect(['name'=>'taylor', 'framework'=>'laravel']);
			//echo ' ['.var_export_contains($collection->get('name'), '', true).']';
			if($collection->get('name') === 'taylor')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->get('age', 34), '', true).']';
			if($collection->get('age', 34) === 34)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->get('email', function(){
			//	return 'taylor@example.com';
			//}), '', true).']';
			if($collection->get('email', function(){
				return 'taylor@example.com';
			}) === 'taylor@example.com')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> group_by';
			$collection=lv_arr_collect([
				['account_id'=>'account-x10', 'product'=>'Chair'],
				['account_id'=>'account-x10', 'product'=>'Bookcase'],
				['account_id'=>'account-x11', 'product'=>'Desk']
			]);
			$grouped=$collection->group_by('account_id');
			//echo ' ['.var_export_contains($grouped->all(), '', true).']';
			if(var_export_contains(
				$grouped->all(),
				"array('account-x10'=>lv_arr_collection::__set_state(array('items'=>array(0=>array('account_id'=>'account-x10','product'=>'Chair',),1=>array('account_id'=>'account-x10','product'=>'Bookcase',),),)),'account-x11'=>lv_arr_collection::__set_state(array('items'=>array(0=>array('account_id'=>'account-x11','product'=>'Desk',),),)),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->group_by(function(array $item, int $key){
			//	return substr($item['account_id'], -3);
			//}), '', true).']';
			if(var_export_contains(
				$collection->group_by(function(array $item, int $key){
					return substr($item['account_id'], -3);
				}),
				"lv_arr_collection::__set_state(array('items'=>array('x10'=>lv_arr_collection::__set_state(array('items'=>array(0=>array('account_id'=>'account-x10','product'=>'Chair',),1=>array('account_id'=>'account-x10','product'=>'Bookcase',),),)),'x11'=>lv_arr_collection::__set_state(array('items'=>array(0=>array('account_id'=>'account-x11','product'=>'Desk',),),)),),))"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$data=new lv_arr_collection([
				10=>['user'=>1, 'skill'=>1, 'roles'=>['Role_1', 'Role_3']],
				20=>['user'=>2, 'skill'=>1, 'roles'=>['Role_1', 'Role_2']],
				30=>['user'=>3, 'skill'=>2, 'roles'=>['Role_1']],
				40=>['user'=>4, 'skill'=>2, 'roles'=>['Role_2']]
			]);
			//echo ' ['.var_export_contains($data->group_by(['skill', function(array $item){
			//	return $item['roles'];
			//}], true), '', true).']';
			if(var_export_contains(
				$data->group_by(['skill', function(array $item){
					return $item['roles'];
				}], true),
				"lv_arr_collection::__set_state(array('items'=>array(1=>lv_arr_collection::__set_state(array('items'=>array('Role_1'=>lv_arr_collection::__set_state(array('items'=>array(10=>array('user'=>1,'skill'=>1,'roles'=>array(0=>'Role_1',1=>'Role_3',),),20=>array('user'=>2,'skill'=>1,'roles'=>array(0=>'Role_1',1=>'Role_2',),),),)),'Role_3'=>lv_arr_collection::__set_state(array('items'=>array(10=>array('user'=>1,'skill'=>1,'roles'=>array(0=>'Role_1',1=>'Role_3',),),),)),'Role_2'=>lv_arr_collection::__set_state(array('items'=>array(20=>array('user'=>2,'skill'=>1,'roles'=>array(0=>'Role_1',1=>'Role_2',),),),)),),)),2=>lv_arr_collection::__set_state(array('items'=>array('Role_1'=>lv_arr_collection::__set_state(array('items'=>array(30=>array('user'=>3,'skill'=>2,'roles'=>array(0=>'Role_1',),),),)),'Role_2'=>lv_arr_collection::__set_state(array('items'=>array(40=>array('user'=>4,'skill'=>2,'roles'=>array(0=>'Role_2',),),),)),),)),),))"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> has';
			$collection=lv_arr_collect(['account_id'=>1, 'product'=>'Desk', 'amount'=>5]);
			//echo ' ['.var_export_contains($collection->has('product'), '', true).']';
			if($collection->has('product') === true)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->has(['product', 'amount']), '', true).']';
			if($collection->has(['product', 'amount']) === true)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->has(['amount', 'price']), '', true).']';
			if($collection->has(['amount', 'price']) === false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> has_any';
			$collection=lv_arr_collect(['account_id'=>1, 'product'=>'Desk', 'amount'=>5]);
			//echo ' ['.var_export_contains($collection->has_any(['product', 'price']), '', true).']';
			if($collection->has_any(['product', 'price']) === true)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->has_any(['name', 'price']), '', true).']';
			if($collection->has_any(['name', 'price']) ===false)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> implode';
			$collection=lv_arr_collect([
				['account_id'=>1, 'product'=>'Desk'],
				['account_id'=>2, 'product'=>'Chair']
			]);
			//echo ' ['.var_export_contains($collection->implode('product', ', '), '', true).']';
			if($collection->implode('product', ', ') === 'Desk, Chair')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4, 5])->implode('-'), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4, 5])->implode('-') === '1-2-3-4-5')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->implode(function(array $item, int $key){
			//	return strtoupper($item['product']);
			//}, ', '), '', true).']';
			if($collection->implode(function(array $item, int $key){
				return strtoupper($item['product']);
			}, ', ') === 'DESK, CHAIR')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> intersect';
			$collection=lv_arr_collect(['Desk', 'Sofa', 'Chair']);
			$intersect=$collection->intersect(['Desk', 'Chair', 'Bookcase']);
			//echo ' ['.var_export_contains($intersect->all(), '', true).']';
			if(var_export_contains($intersect->all(), "array(0=>'Desk',2=>'Chair',)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> intersect_assoc';
			$collection=lv_arr_collect([
				'color'=>'red',
				'size'=>'M',
				'material'=>'cotton'
			]);
			$intersect=$collection->intersect_assoc([
				'color'=>'blue',
				'size'=>'M',
				'material'=>'polyester'
			]);
			//echo ' ['.var_export_contains($intersect->all(), '', true).']';
			if(var_export_contains($intersect->all(), "array('size'=>'M',)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> intersect_by_keys';
			$collection=lv_arr_collect([
				'serial'=>'UX301',
				'type'=>'screen',
				'year'=>2009
			]);
			$intersect=$collection->intersect_by_keys([
				'reference'=>'UX404',
				'type'=>'tab',
				'year'=>2011
			]);
			//echo ' ['.var_export_contains($intersect->all(), '', true).']';
			if(var_export_contains(
				$intersect->all(),
				"array('type'=>'screen','year'=>2009,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> is_empty';
			if(lv_arr_collect([])->is_empty())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> is_not_empty';
			if(lv_arr_collect([])->is_not_empty())
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
			else
				echo ' [ OK ]'.PHP_EOL;
		echo '  -> join';
			//echo ' ['.var_export_contains(lv_arr_collect(['a', 'b', 'c'])->join(', '), '', true).']';
			if(lv_arr_collect(['a', 'b', 'c'])->join(', ') === 'a, b, c')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect(['a', 'b', 'c'])->join(', ', ', and '), '', true).']';
			if(lv_arr_collect(['a', 'b', 'c'])->join(', ', ', and ') === 'a, b, and c')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect(['a', 'b'])->join(', ', ' and '), '', true).']';
			if(lv_arr_collect(['a', 'b'])->join(', ', ' and ') === 'a and b')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect(['a'])->join(', ', ' and '), '', true).']';
			if(lv_arr_collect(['a'])->join(', ', ' and ') === 'a')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([])->join(', ', ' and '), '', true).']';
			if(lv_arr_collect([])->join(', ', ' and ') === '')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> key_by';
			$collection=lv_arr_collect([
				['product_id'=>'prod-100', 'name'=>'Desk'],
				['product_id'=>'prod-200', 'name'=>'Chair']
			]);
			$keyed=$collection->key_by('product_id');
			//echo ' ['.var_export_contains($keyed->all(), '', true).']';
			if(var_export_contains(
				$keyed->all(),
				"array('prod-100'=>array('product_id'=>'prod-100','name'=>'Desk',),'prod-200'=>array('product_id'=>'prod-200','name'=>'Chair',),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$keyed=$collection->key_by(function(array $item, int $key){
				return strtoupper($item['product_id']);
			});
			//echo ' ['.var_export_contains($keyed->all(), '', true).']';
			if(var_export_contains(
				$keyed->all(),
				"array('PROD-100'=>array('product_id'=>'prod-100','name'=>'Desk',),'PROD-200'=>array('product_id'=>'prod-200','name'=>'Chair',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> keys';
			$collection=lv_arr_collect([
				'prod-100'=>['product_id'=>'prod-100', 'name'=>'Desk'],
				'prod-200'=>['product_id'=>'prod-200', 'name'=>'Chair']
			]);
			$keys=$collection->keys();
			//echo ' ['.var_export_contains($keys->all(), '', true).']';
			if(var_export_contains(
				$keys->all(),
				"array(0=>'prod-100',1=>'prod-200',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> last';
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4])->last(function(int $value, int $key){
			//	return ($value < 3);
			//}), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4])->last(function(int $value, int $key){
				return ($value < 3);
			}) === 2)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4])->last(), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4])->last() === 4)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> macro';
			lv_arr_collection::macro('to_upper', function(){
				return $this->map(function(string $value){
					return strtoupper($value);
				});
			});
			$collection=lv_arr_collect(['first', 'second']);
			//echo ' ['.var_export_contains($collection->to_upper(), '', true).']';
			if(var_export_contains(
				$collection->to_upper(),
				"lv_arr_collection::__set_state(array('items'=>array(0=>'FIRST',1=>'SECOND',),))"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> make';
			$collection=lv_arr_collection::make([1, 2, 3]);
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains($collection->all(), "array(0=>1,1=>2,2=>3,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> map';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$multiplied=$collection->map(function(int $item, int $key){
				return ($item * 2);
			});
			//echo ' ['.var_export_contains($multiplied->all(), '', true).']';
			if(var_export_contains(
				$multiplied->all(),
				"array(0=>2,1=>4,2=>6,3=>8,4=>10,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> map_into';
			class currency
			{
				public function __construct(string $code) {}
			}
			$collection=lv_arr_collect(['USD', 'EUR', 'GBP']);
			$currencies=$collection->map_into(currency::class);
			//echo ' ['.var_export_contains($currencies->all(), '', true).']';
			if(var_export_contains(
				$currencies->all(),
				"array(0=>currency::__set_state(array()),1=>currency::__set_state(array()),2=>currency::__set_state(array()),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> map_spread';
			$collection=lv_arr_collect([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
			$chunks=$collection->chunk(2);
			$sequence=$chunks->map_spread(function(int $even, int $odd){
				return ($even + $odd);
			});
			//echo ' ['.var_export_contains($sequence->all(), '', true).']';
			if(var_export_contains(
				$sequence->all(),
				"array(0=>1,1=>5,2=>9,3=>13,4=>17,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> map_to_dictionary';
			$collection=lv_arr_collect([
				['score'=>0.84, 'name'=>'Bob'],
				['score'=>0.95, 'name'=>'Alice'],
				['score'=>0.78, 'name'=>'Charlie'],
				['score'=>0.92, 'name'=>'Alice'],
				['score'=>0.98, 'name'=>'Bob']
			]);
			$scores=$collection->map_to_dictionary(function($item, $key){
				return [$item['name']=>$item['score']];
			});
			//echo ' ['.var_export_contains($scores->all(), '', true).']';
			if(var_export_contains(
				$scores->all(),
				"array('Bob'=>array(0=>0.84,1=>0.98,),'Alice'=>array(0=>0.95,1=>0.92,),'Charlie'=>array(0=>0.78,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> map_to_groups';
			$collection=lv_arr_collect([
				[
					'name'=>'John Doe',
					'department'=>'Sales'
				],
				[
					'name'=>'Jane Doe',
					'department'=>'Sales'
				],
				[
					'name'=>'Johnny Doe',
					'department'=>'Marketing'
				]
			]);
			$grouped=$collection->map_to_groups(function(array $item, int $key){
				return [$item['department']=>$item['name']];
			});
			//echo ' ['.var_export_contains($grouped->all(), '', true).']';
			if(var_export_contains(
				$grouped->all(),
				"array('Sales'=>lv_arr_collection::__set_state(array('items'=>array(0=>'JohnDoe',1=>'JaneDoe',),)),'Marketing'=>lv_arr_collection::__set_state(array('items'=>array(0=>'JohnnyDoe',),)),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($grouped->get('Sales')->all(), '', true).']';
			if(var_export_contains(
				$grouped->get('Sales')->all(),
				"array(0=>'JohnDoe',1=>'JaneDoe',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> map_with_keys';
			$collection=lv_arr_collect([
				[
					'name'=>'John',
					'department'=>'Sales',
					'email'=>'john@example.com'
				],
				[
					'name'=>'Jane',
					'department'=>'Marketing',
					'email'=>'jane@example.com'
				]
			]);
			$keyed=$collection->map_with_keys(function(array $item, int $key){
				return [$item['email']=>$item['name']];
			});
			//echo ' ['.var_export_contains($keyed->all(), '', true).']';
			if(var_export_contains(
				$keyed->all(),
				"array('john@example.com'=>'John','jane@example.com'=>'Jane',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> max';
			//echo ' ['.var_export_contains(lv_arr_collect([
			//	['foo'=>10],
			//	['foo'=>20]
			//])->max('foo'), '', true).']';
			if(lv_arr_collect([
				['foo'=>10],
				['foo'=>20]
			])->max('foo') === 20)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4, 5])->max(), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4, 5])->max() === 5)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> median';
			//echo ' ['.var_export_contains(lv_arr_collect([
			//	['foo'=>10],
			//	['foo'=>10],
			//	['foo'=>20],
			//	['foo'=>40]
			//])->median('foo'), '', true).']';
			if(lv_arr_collect([
				['foo'=>10],
				['foo'=>10],
				['foo'=>20],
				['foo'=>40]
			])->median('foo') === 15)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 1, 2, 4])->median(), '', true).']';
			if(var_export_contains(lv_arr_collect([1, 1, 2, 4])->median(), '1.5'))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> merge';
			$collection=lv_arr_collect(['product_id'=>1, 'price'=>100]);
			$merged=$collection->merge(['price'=>200, 'discount'=>false]);
			//echo ' ['.var_export_contains($merged->all(), '', true).']';
			if(var_export_contains(
				$merged->all(),
				"array('product_id'=>1,'price'=>200,'discount'=>false,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect(['Desk', 'Chair']);
			$merged=$collection->merge(['Bookcase', 'Door']);
			//echo ' ['.var_export_contains($merged->all(), '', true).']';
			if(var_export_contains(
				$merged->all(),
				"array(0=>'Desk',1=>'Chair',2=>'Bookcase',3=>'Door',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> merge_recursive';
			$collection=lv_arr_collect(['product_id'=>1, 'price'=>100]);
			$merged=$collection->merge_recursive([
				'product_id'=>2,
				'price'=>200,
				'discount'=>false
			]);
			//echo ' ['.var_export_contains($merged->all(), '', true).']';
			if(var_export_contains(
				$merged->all(),
				"array('product_id'=>array(0=>1,1=>2,),'price'=>array(0=>100,1=>200,),'discount'=>false,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> min';
			//echo ' ['.var_export_contains(lv_arr_collect([
			//	['foo'=>10],
			//	['foo'=>20]
			//])->min('foo'), '', true).']';
			if(lv_arr_collect([
				['foo'=>10],
				['foo'=>20]
			])->min('foo') === 10)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4, 5])->min(), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4, 5])->min() === 1)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> mode';
			//echo ' ['.var_export_contains(lv_arr_collect([
			//	['foo'=>10],
			//	['foo'=>10],
			//	['foo'=>20],
			//	['foo'=>40]
			//])->mode('foo'), '', true).']';
			if(var_export_contains(lv_arr_collect([
				['foo'=>10],
				['foo'=>10],
				['foo'=>20],
				['foo'=>40]
			])->mode('foo'), "array(0=>10,)"))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 1, 2, 4])->mode(), '', true).']';
			if(var_export_contains(
				lv_arr_collect([1, 1, 2, 4])->mode(),
				"array(0=>1,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 1, 2, 2])->mode(), '', true).']';
			if(var_export_contains(
				lv_arr_collect([1, 1, 2, 2])->mode(),
				"array(0=>1,1=>2,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> nth';
			$collection=lv_arr_collect(['a', 'b', 'c', 'd', 'e', 'f']);
			//echo ' ['.var_export_contains($collection->nth(4), '', true).']';
			if(var_export_contains(
				$collection->nth(4),
				"lv_arr_collection::__set_state(array('items'=>array(0=>'a',1=>'e',),))"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->nth(4, 1), '', true).']';
			if(var_export_contains(
				$collection->nth(4, 1),
				"lv_arr_collection::__set_state(array('items'=>array(0=>'b',1=>'f',),))"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> only';
			$collection=lv_arr_collect([
				'product_id'=>1,
				'name'=>'Desk',
				'price'=>100,
				'discount'=>false
			]);
			$filtered=$collection->only(['product_id', 'name']);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array('product_id'=>1,'name'=>'Desk',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> pad';
			$collection=lv_arr_collect(['A', 'B', 'C']);
			$filtered=$collection->pad(5, 0);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(0=>'A',1=>'B',2=>'C',3=>0,4=>0,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$filtered=$collection->pad(-5, 0);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(0=>0,1=>0,2=>'A',3=>'B',4=>'C',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> partition';
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6]);
			[$under_three, $equal_or_above_three]=$collection->partition(function(int $i){
				return ($i < 3);
			});
			//echo ' ['.var_export_contains($under_three->all(), '', true).']';
			if(var_export_contains(
				$under_three->all(),
				"array(0=>1,1=>2,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($equal_or_above_three->all(), '', true).']';
			if(var_export_contains(
				$equal_or_above_three->all(),
				"array(2=>3,3=>4,4=>5,5=>6,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> percentage';
			$collection=lv_arr_collect([1, 1, 2, 2, 2, 3]);
			//echo ' ['.var_export_contains($collection->percentage(function($value){
			//	return ($value === 1);
			//}), '', true).']';
			if((string)$collection->percentage(function($value){
				return ($value === 1);
			}) === '33.33')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->percentage(function($value){
			//	return ($value === 1);
			//}, 3), '', true).']';
			if((string)$collection->percentage(function($value){
				return ($value === 1);
			}, 3) === '33.333')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> pipe';
			$collection=lv_arr_collect([1, 2, 3]);
			//echo ' ['.var_export_contains($collection->pipe(function(lv_arr_collection $collection){
			//	return $collection->sum();
			//}), '', true).']';
			if($collection->pipe(function(lv_arr_collection $collection){
				return $collection->sum();
			}) === 6)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> pipe_into';
			class resource_collection
			{
				public $collection;
				public function __construct(lv_arr_collection $collection)
				{
					$this->collection=$collection;
				}
			}
			$collection=lv_arr_collect([1, 2, 3]);
			$resource=$collection->pipe_into(resource_collection::class);
			//echo ' ['.var_export_contains($resource->collection->all(), '', true).']';
			if(var_export_contains(
				$resource->collection->all(),
				"array(0=>1,1=>2,2=>3,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> pipe_through';
			$collection=lv_arr_collect([1, 2, 3]);
			//echo ' ['.var_export_contains($collection->pipe_through([
			//	function(lv_arr_collection $collection)
			//	{
			//		return $collection->merge([4, 5]);
			//	},
			//	function(lv_arr_collection $collection)
			//	{
			//		return $collection->sum();
			//	}
			//]), '', true).']';
			if($collection->pipe_through([
				function(lv_arr_collection $collection)
				{
					return $collection->merge([4, 5]);
				},
				function(lv_arr_collection $collection)
				{
					return $collection->sum();
				}
			]) === 15)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> pluck';
			$collection=lv_arr_collect([
				['product_id'=>'prod-100', 'name'=>'Desk'],
				['product_id'=>'prod-200', 'name'=>'Chair']
			]);
			$plucked=$collection->pluck('name');
			//echo ' ['.var_export_contains($plucked->all(), '', true).']';
			if(var_export_contains(
				$plucked->all(),
				"array(0=>'Desk',1=>'Chair',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$plucked=$collection->pluck('name', 'product_id');
			//echo ' ['.var_export_contains($plucked->all(), '', true).']';
			if(var_export_contains(
				$plucked->all(),
				"array('prod-100'=>'Desk','prod-200'=>'Chair',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				[
					'name'=>'Laracon',
					'speakers'=>[
						'first_day'=>['Rosa', 'Judith']
					]
				],
				[
					'name'=>'VueConf',
					'speakers'=>[
						'first_day'=>['Abigail', 'Joey']
					]
				]
			]);
			$plucked=$collection->pluck('speakers.first_day');
			//echo ' ['.var_export_contains($plucked->all(), '', true).']';
			if(var_export_contains(
				$plucked->all(),
				"array(0=>array(0=>'Rosa',1=>'Judith',),1=>array(0=>'Abigail',1=>'Joey',),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['brand'=>'Tesla', 'color'=>'red'],
				['brand'=>'Pagani', 'color'=>'white'],
				['brand'=>'Tesla', 'color'=>'black'],
				['brand'=>'Pagani', 'color'=>'orange']
			]);
			$plucked=$collection->pluck('color', 'brand');
			//echo ' ['.var_export_contains($plucked->all(), '', true).']';
			if(var_export_contains(
				$plucked->all(),
				"array('Tesla'=>'black','Pagani'=>'orange',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> pop';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			//echo ' ['.var_export_contains($collection->pop(), '', true).']';
			if($collection->pop() === 5)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains($collection->all(), "array(0=>1,1=>2,2=>3,3=>4,)"))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			//echo ' ['.var_export_contains($collection->pop(3), '', true).']';
			if(var_export_contains(
				$collection->pop(3),
				"lv_arr_collection::__set_state(array('items'=>array(0=>5,1=>4,2=>3,),))"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains($collection->all(), "array(0=>1,1=>2,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> prepend';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->prepend(0);
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect(['one'=>1, 'two'=>2]);
			$collection->prepend(0, 'zero');
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array('zero'=>0,'one'=>1,'two'=>2,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> pull';
			$collection=lv_arr_collect(['product_id'=>'prod-100', 'name'=>'Desk']);
			//echo ' ['.var_export_contains($collection->pull('name'), '', true).']';
			if($collection->pull('name') === 'Desk')
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array('product_id'=>'prod-100',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> push';
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$collection->push(5);
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,2=>3,3=>4,4=>5,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> put';
			$collection=lv_arr_collect(['product_id'=>1, 'name'=>'Desk']);
			$collection->put('price', 100);
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array('product_id'=>1,'name'=>'Desk','price'=>100,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> random';
			$collection=lv_arr_collect([1, 2, 3]);
			$random=$collection->random();
			//echo ' ['.var_export_contains($random, '', true).']';
			if(
				($random === 1) ||
				($random === 2) ||
				($random === 3)
			)
				echo ' [OK '.$random.']';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$random=$collection->random(2);
			$random=var_export_contains($random->all(), '', true);
			//echo ' ['.$random.']';
			if(
				($random === "array(0=>1,1=>2,)") ||
				($random === "array(0=>2,1=>3,)") ||
				($random === "array(0=>2,1=>1,)") ||
				($random === "array(0=>1,1=>3,)")
			)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$random=$collection->random(function(lv_arr_collection $items){
				return min(10, count($items->all()));
			});
			//echo ' ['.var_export_contains($random->all(), '', true).']';
			if(var_export_contains(
				$random->all(),
				"array(0=>1,1=>2,2=>3,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> range';
			$collection=lv_arr_collect()->range(3, 6);
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>3,1=>4,2=>5,3=>6,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> reduce';
			$collection=lv_arr_collect([1, 2, 3]);
			//echo ' ['.var_export_contains($collection->reduce(function(?int $carry, int $item){
			//	return ($carry + $item);
			//}), '', true).']';
			if($collection->reduce(function(?int $carry, int $item){
				return ($carry + $item);
			}) === 6)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->reduce(function(?int $carry, int $item){
			//	return ($carry + $item);
			//}, 4), '', true).']';
			if($collection->reduce(function(?int $carry, int $item){
				return ($carry + $item);
			}, 4) === 10)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				'usd'=>1400,
				'gbp'=>1200,
				'eur'=>1000
			]);
			$ratio=[
				'usd'=>1,
				'gbp'=>1.37,
				'eur'=>1.22
			];
			//echo ' ['.var_export_contains($collection->reduce(function(?int $carry, int $value, $key) use($ratio){
			//	return ($carry + ($value * $ratio[$key]));
			//}), '', true).']';
			if((int)$collection->reduce(function(?int $carry, int $value, $key) use($ratio){
				return ($carry + ($value * $ratio[$key]));
			}) === 4264)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> reduce_spread [SKIP]'.PHP_EOL;
		echo '  -> reject';
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$filtered=$collection->reject(function(int $value, int $key){
				return ($value > 2);
			});
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains($filtered->all(), "array(0=>1,1=>2,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> replace';
			$collection=lv_arr_collect(['Taylor', 'Abigail', 'James']);
			$replaced=$collection->replace([1=>'Victoria', 3=>'Finn']);
			//echo ' ['.var_export_contains($replaced->all(), '', true).']';
			if(var_export_contains(
				$replaced->all(),
				"array(0=>'Taylor',1=>'Victoria',2=>'James',3=>'Finn',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> replace_recursive';
			$collection=lv_arr_collect([
				'Taylor',
				'Abigail',
				[
					'James',
					'Victoria',
					'Finn'
				]
			]);
			$replaced=$collection->replace_recursive([
				'Charlie',
				2=>[1=>'King']
			]);
			//echo ' ['.var_export_contains($replaced->all(), '', true).']';
			if(var_export_contains(
				$replaced->all(),
				"array(0=>'Charlie',1=>'Abigail',2=>array(0=>'James',1=>'King',2=>'Finn',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> reverse';
			$collection=lv_arr_collect(['a', 'b', 'c', 'd', 'e']);
			$reversed=$collection->reverse();
			//echo ' ['.var_export_contains($reversed->all(), '', true).']';
			if(var_export_contains(
				$reversed->all(),
				"array(4=>'e',3=>'d',2=>'c',1=>'b',0=>'a',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> search';
			$collection=lv_arr_collect([2, 4, 6, 8]);
			//echo ' ['.var_export_contains($collection->search(4), '', true).']';
			if($collection->search(4) === 1)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([2, 4, 6, 8])->search('4', true), '', true).']';
			if(lv_arr_collect([2, 4, 6, 8])->search('4', true) === false)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([2, 4, 6, 8])->search(function(int $item, int $key){
			//	return ($item > 5);
			//}), '', true).']';
			if(lv_arr_collect([2, 4, 6, 8])->search(function(int $item, int $key){
				return ($item > 5);
			}) === 2)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> select/lv_arr_select';
			$users=lv_arr_collect([
				['name'=>'Taylor Otwell', 'role'=>'Developer', 'status'=>'active'],
				['name'=>'Victoria Faith', 'role'=>'Researcher', 'status'=>'active']
			]);
			//echo ' ['.var_export_contains($users->select(['name', 'role']), '', true).']';
			if(var_export_contains(
				$users->select(['name', 'role']),
				"lv_arr_collection::__set_state(array('items'=>array(0=>array('name'=>'TaylorOtwell','role'=>'Developer',),1=>array('name'=>'VictoriaFaith','role'=>'Researcher',),),))"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> shift';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			//echo ' ['.var_export_contains($collection->shift(), '', true).']';
			if($collection->shift() === 1)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>2,1=>3,2=>4,3=>5,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			//echo ' ['.var_export_contains($collection->shift(3), '', true).']';
			if(var_export_contains(
				$collection->shift(3),
				"lv_arr_collection::__set_state(array('items'=>array(0=>1,1=>2,2=>3,),))"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains($collection->all(), "array(0=>4,1=>5,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> shuffle';
			$collection=lv_arr_collect([1, 2, 3]);
			$shuffled=$collection->shuffle();
			//echo ' ['.var_export_contains($shuffled->all(), '', true).']';
			switch(var_export_contains($shuffled->all(), '', true))
			{
				case "array(0=>1,1=>2,2=>3,)":
					echo ' [OK 1]'.PHP_EOL;
				break;
				case "array(0=>1,1=>3,2=>2,)":
					echo ' [OK 2]'.PHP_EOL;
				break;
				case "array(0=>3,1=>1,2=>2,)":
					echo ' [OK 3]'.PHP_EOL;
				break;
				case "array(0=>2,1=>1,2=>3,)":
					echo ' [OK 4]'.PHP_EOL;
				break;
				case "array(0=>2,1=>3,2=>1,)":
					echo ' [OK 5]'.PHP_EOL;
				break;
				case "array(0=>3,1=>2,2=>1,)":
					echo ' [OK 6]'.PHP_EOL;
				break;
				default:
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
			}
		echo '  -> skip';
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
			$collection=$collection->skip(4);
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(4=>5,5=>6,6=>7,7=>8,8=>9,9=>10,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> slice';
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
			$slice=$collection->slice(4);
			//echo ' ['.var_export_contains($slice->all(), '', true).']';
			if(var_export_contains(
				$slice->all(),
				"array(4=>5,5=>6,6=>7,7=>8,8=>9,9=>10,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$slice=$collection->slice(4, 2);
			//echo ' ['.var_export_contains($slice->all(), '', true).']';
			if(var_export_contains($slice->all(), "array(4=>5,5=>6,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sliding';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunks=$collection->sliding(2);
			//echo ' ['.var_export_contains($chunks->to_array(), '', true).']';
			if(var_export_contains(
				$chunks->to_array(),
				"array(0=>lv_arr_collection::__set_state(array('items'=>array(0=>1,1=>2,),)),1=>lv_arr_collection::__set_state(array('items'=>array(1=>2,2=>3,),)),2=>lv_arr_collection::__set_state(array('items'=>array(2=>3,3=>4,),)),3=>lv_arr_collection::__set_state(array('items'=>array(3=>4,4=>5,),)),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3])->all(), '', true).']';
			if(var_export_contains(
				lv_arr_collect([1, 2, 3])->all(),
				"array(0=>1,1=>2,2=>3,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunks=$collection->sliding(3, 2);
			//echo ' ['.var_export_contains($chunks->to_array(), '', true).']';
			if(var_export_contains(
				$chunks->to_array(),
				"array(0=>lv_arr_collection::__set_state(array('items'=>array(0=>1,1=>2,2=>3,),)),1=>lv_arr_collection::__set_state(array('items'=>array(2=>3,3=>4,4=>5,),)),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sole';
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4])->sole(function(int $value, int $key){
			//	return ($value === 2);
			//}), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4])->sole(function(int $value, int $key){
				return ($value === 2);
			}) === 2)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100]
			]);
			//echo ' ['.var_export_contains($collection->sole('product', 'Chair'), '', true).']';
			if(var_export_contains(
				$collection->sole('product', 'Chair'),
				"array('product'=>'Chair','price'=>100,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200]
			]);
			//echo ' ['.var_export_contains($collection->sole(), '', true).']';
			if(var_export_contains(
				$collection->sole(),
				"array('product'=>'Desk','price'=>200,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> some [SKIP]'.PHP_EOL;
		echo '  -> sort';
			$collection=lv_arr_collect([5, 3, 1, 2, 4]);
			$sorted=$collection->sort();
			//echo ' ['.var_export_contains($sorted->values()->all(), '', true).']';
			if(var_export_contains(
				$sorted->values()->all(),
				"array(0=>1,1=>2,2=>3,3=>4,4=>5,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sort_by';
			$collection=lv_arr_collect([
				['name'=> 'Desk', 'price'=>200],
				['name'=> 'Chair', 'price'=>100],
				['name'=> 'Bookcase', 'price'=>150]
			]);
			$sorted=$collection->sort_by('price');
			//echo ' ['.var_export_contains($sorted->values()->all(), '', true).']';
			if(var_export_contains(
				$sorted->values()->all(),
				"array(0=>array('name'=>'Chair','price'=>100,),1=>array('name'=>'Bookcase','price'=>150,),2=>array('name'=>'Desk','price'=>200,),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['title'=>'Item 1'],
				['title'=> 'Item 12'],
				['title'=>'Item 3']
			]);
			$sorted=$collection->sort_by('title', SORT_NATURAL);
			//echo ' ['.var_export_contains($sorted->values()->all(), '', true).']';
			if(var_export_contains(
				$sorted->values()->all(),
				"array(0=>array('title'=>'Item1',),1=>array('title'=>'Item3',),2=>array('title'=>'Item12',),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['name'=>'Desk', 'colors'=>['Black', 'Mahogany']],
				['name'=>'Chair', 'colors'=>['Black']],
				['name'=>'Bookcase', 'colors'=>['Red', 'Beige', 'Brown']]
			]);
			$sorted=$collection->sort_by(function(array $product, int $key){
				return count($product['colors']);
			});
			//echo ' ['.var_export_contains($sorted->values()->all(), '', true).']';
			if(var_export_contains(
				$sorted->values()->all(),
				"array(0=>array('name'=>'Chair','colors'=>array(0=>'Black',),),1=>array('name'=>'Desk','colors'=>array(0=>'Black',1=>'Mahogany',),),2=>array('name'=>'Bookcase','colors'=>array(0=>'Red',1=>'Beige',2=>'Brown',),),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['name'=>'Taylor Otwell', 'age'=>34],
				['name'=>'Abigail Otwell', 'age'=>30],
				['name'=>'Taylor Otwell', 'age'=>36],
				['name'=>'Abigail Otwell', 'age'=>32]
			]);
			$sorted=$collection->sort_by([
				['name', 'asc'],
				['age', 'desc']
			]);
			//echo ' ['.var_export_contains($sorted->values()->all(), '', true).']';
			if(var_export_contains(
				$sorted->values()->all(),
				"array(0=>array('name'=>'AbigailOtwell','age'=>32,),1=>array('name'=>'AbigailOtwell','age'=>30,),2=>array('name'=>'TaylorOtwell','age'=>36,),3=>array('name'=>'TaylorOtwell','age'=>34,),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['name'=>'Taylor Otwell', 'age'=>34],
				['name'=>'Abigail Otwell', 'age'=>30],
				['name'=>'Taylor Otwell', 'age'=>36],
				['name'=>'Abigail Otwell', 'age'=>32]
			]);
			$sorted=$collection->sort_by([
				function(array $a, array $b)
				{
					return ($a['name'] <=> $b['name']);
				},
				function(array $a, array $b)
				{
					return ($b['age'] <=> $a['age']);
				}
			]);
			//echo ' ['.var_export_contains($sorted->values()->all(), '', true).']';
			if(var_export_contains(
				$sorted->values()->all(),
				"array(0=>array('name'=>'AbigailOtwell','age'=>32,),1=>array('name'=>'AbigailOtwell','age'=>30,),2=>array('name'=>'TaylorOtwell','age'=>36,),3=>array('name'=>'TaylorOtwell','age'=>34,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sort_by_desc [SKIP]'.PHP_EOL;
		echo '  -> sort_desc';
			$collection=lv_arr_collect([5, 3, 1, 2, 4]);
			$sorted=$collection->sort_desc();
			//echo ' ['.var_export_contains($sorted->values()->all(), '', true).']';
			if(var_export_contains(
				$sorted->values()->all(),
				"array(0=>5,1=>4,2=>3,3=>2,4=>1,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sort_keys';
			$collection=lv_arr_collect([
				'id'=>22345,
				'first'=>'John',
				'last'=>'Doe'
			]);
			$sorted=$collection->sort_keys();
			//echo ' ['.var_export_contains($sorted->all(), '', true).']';
			if(var_export_contains(
				$sorted->all(),
				"array('first'=>'John','id'=>22345,'last'=>'Doe',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sort_keys_desc [SKIP]'.PHP_EOL;
		echo '  -> sort_keys_using';
			$collection=lv_arr_collect([
				'ID'=>22345,
				'first'=>'John',
				'last'=>'Doe'
			]);
			$sorted=$collection->sort_keys_using('strnatcasecmp');
			//echo ' ['.var_export_contains($sorted->all(), '', true).']';
			if(var_export_contains(
				$sorted->all(),
				"array('first'=>'John','ID'=>22345,'last'=>'Doe',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> splice';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunk=$collection->splice(2);
			//echo ' ['.var_export_contains($chunk->all(), '', true).']';
			if(var_export_contains(
				$chunk->all(),
				"array(0=>3,1=>4,2=>5,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunk=$collection->splice(2, 1);
			//echo ' ['.var_export_contains($chunk->all(), '', true).']';
			if(var_export_contains(
				$chunk->all(),
				"array(0=>3,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,2=>4,3=>5,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunk=$collection->splice(2, 1, [10, 11]);
			//echo ' ['.var_export_contains($chunk->all(), '', true).']';
			if(var_export_contains(
				$chunk->all(),
				"array(0=>3,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,2=>10,3=>11,4=>4,5=>5,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> split';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$groups=$collection->split(3);
			//echo ' ['.var_export_contains($groups->all(), '', true).']';
			if(var_export_contains(
				$groups->all(),
				"array(0=>lv_arr_collection::__set_state(array('items'=>array(0=>1,1=>2,),)),1=>lv_arr_collection::__set_state(array('items'=>array(0=>3,1=>4,),)),2=>lv_arr_collection::__set_state(array('items'=>array(0=>5,),)),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> split_in';
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
			$groups=$collection->split_in(3);
			//echo ' ['.var_export_contains($groups->all(), '', true).']';
			if(var_export_contains(
				$groups->all(),
				"array(0=>lv_arr_collection::__set_state(array('items'=>array(0=>1,1=>2,2=>3,3=>4,),)),1=>lv_arr_collection::__set_state(array('items'=>array(4=>5,5=>6,6=>7,7=>8,),)),2=>lv_arr_collection::__set_state(array('items'=>array(8=>9,9=>10,),)),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> sum';
			//echo ' ['.var_export_contains(lv_arr_collect([1, 2, 3, 4, 5])->sum(), '', true).']';
			if(lv_arr_collect([1, 2, 3, 4, 5])->sum() === 15)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				[
					'name'=>'JavaScript: The Good Parts',
					'pages'=>176
				],
				[
					'name'=>'JavaScript: The Definitive Guide',
					'pages'=>1096
				]
			]);
			//echo ' ['.var_export_contains($collection->sum('pages'), '', true).']';
			if($collection->sum('pages') === 1272)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['name'=>'Chair', 'colors'=>['Black']],
				['name'=>'Desk', 'colors'=>['Black', 'Mahogany']],
				['name'=>'Bookcase', 'colors'=>['Red', 'Beige', 'Brown']]
			]);
			//echo ' ['.var_export_contains($collection->sum(function(array $product){
			//	return count($product['colors']);
			//}), '', true).']';
			if($collection->sum(function(array $product){
				return count($product['colors']);
			}) === 6)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> take';
			$collection=lv_arr_collect([0, 1, 2, 3, 4, 5]);
			$chunk=$collection->take(3);
			//echo ' ['.var_export_contains($chunk->all(), '', true).']';
			if(var_export_contains($chunk->all(), "array(0=>0,1=>1,2=>2,)"))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([0, 1, 2, 3, 4, 5]);
			$chunk=$collection->take(-2);
			//echo ' ['.var_export_contains($chunk->all(), '', true).']';
			if(var_export_contains($chunk->all(), "array(4=>4,5=>5,)"))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> tap';
			$GLOBALS['tap_va']='';
			//echo ' ['.var_export_contains(lv_arr_collect([2, 4, 3, 1, 5])
			//	->sort()
			//	->tap(function(lv_arr_collection $collection){
			//		$GLOBALS['tap_va']=$collection->values()->all();
			//	})
			//	->shift(), '', true).']';
			if(lv_arr_collect([2, 4, 3, 1, 5])
				->sort()
				->tap(function(lv_arr_collection $collection){
					$GLOBALS['tap_va']=$collection->values()->all();
				})
				->shift()
			)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains($GLOBALS['tap_va'], '', true).']';
			if(var_export_contains(
				$GLOBALS['tap_va'],
				"array(0=>1,1=>2,2=>3,3=>4,4=>5,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> times';
			$collection=lv_arr_collection::times(10, function(int $number){
				return ($number * 9);
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>9,1=>18,2=>27,3=>36,4=>45,5=>54,6=>63,7=>72,8=>81,9=>90,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> to_array';
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>200]);
			//echo ' ['.var_export_contains($collection->to_array(), '', true).']';
			if(var_export_contains(
				$collection->to_array(),
				"array('name'=>'Desk','price'=>200,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> to_json';
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>200]);
			//echo ' ['.var_export_contains($collection->to_json(), '', true).']';
			if($collection->to_json() === '{"name":"Desk","price":200}')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> transform';
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->transform(function(int $item, int $key){
				return ($item * 2);
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>2,1=>4,2=>6,3=>8,4=>10,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> undot';
			$person=lv_arr_collect([
				'name.first_name'=>'Marie',
				'name.last_name'=>'Valentine',
				'address.line_1'=>'2992 Eagle Drive',
				'address.line_2'=>'',
				'address.suburb'=>'Detroit',
				'address.state'=>'MI',
				'address.postcode'=>'48219'
			]);
			$person=$person->undot();
			//echo ' ['.var_export_contains($person->to_array(), '', true).']';
			if(var_export_contains(
				$person->to_array(),
				"array('name'=>array('first_name'=>'Marie','last_name'=>'Valentine',),'address'=>array('line_1'=>'2992EagleDrive','line_2'=>'','suburb'=>'Detroit','state'=>'MI','postcode'=>'48219',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> union';
			$collection=lv_arr_collect([1=>['a'], 2=>['b']]);
			$union=$collection->union([3=>['c'], 1=>['d']]);
			//echo ' ['.var_export_contains($union->all(), '', true).']';
			if(var_export_contains(
				$union->all(),
				"array(1=>array(0=>'a',),2=>array(0=>'b',),3=>array(0=>'c',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> unique';
			$collection=lv_arr_collect([1, 1, 2, 2, 3, 4, 2]);
			$unique=$collection->unique();
			//echo ' ['.var_export_contains($unique->values()->all(), '', true).']';
			if(var_export_contains(
				$unique->values()->all(),
				"array(0=>1,1=>2,2=>3,3=>4,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['name'=>'iPhone 6', 'brand'=>'Apple', 'type'=>'phone'],
				['name'=>'iPhone 5', 'brand'=>'Apple', 'type'=>'phone'],
				['name'=>'Apple Watch', 'brand'=>'Apple', 'type'=>'watch'],
				['name'=>'Galaxy S6', 'brand'=>'Samsung', 'type'=>'phone'],
				['name'=>'Galaxy Gear', 'brand'=>'Samsung', 'type'=>'watch']
			]);
			$unique=$collection->unique('brand');
			//echo ' ['.var_export_contains($unique->values()->all(), '', true).']';
			if(var_export_contains(
				$unique->values()->all(),
				"array(0=>array('name'=>'iPhone6','brand'=>'Apple','type'=>'phone',),1=>array('name'=>'GalaxyS6','brand'=>'Samsung','type'=>'phone',),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$unique=$collection->unique(function(array $item){
				return $item['brand'].$item['type'];
			});
			//echo ' ['.var_export_contains($unique->values()->all(), '', true).']';
			if(var_export_contains(
				$unique->values()->all(),
				"array(0=>array('name'=>'iPhone6','brand'=>'Apple','type'=>'phone',),1=>array('name'=>'AppleWatch','brand'=>'Apple','type'=>'watch',),2=>array('name'=>'GalaxyS6','brand'=>'Samsung','type'=>'phone',),3=>array('name'=>'GalaxyGear','brand'=>'Samsung','type'=>'watch',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> unique_strict [SKIP]'.PHP_EOL;
		echo '  -> unless';
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->unless(true, function(lv_arr_collection $collection){
				return $collection->push(4);
			});
			$collection->unless(false, function(lv_arr_collection $collection){
				return $collection->push(5);
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,2=>3,3=>5,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->unless(true, function(lv_arr_collection $collection){
				return $collection->push(4);
			}, function(lv_arr_collection $collection){
				return $collection->push(5);
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,2=>3,3=>5,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> unless_empty [SKIP]'.PHP_EOL;
		echo '  -> unless_not_empty [SKIP]'.PHP_EOL;
		echo '  -> unwrap';
			//echo ' ['.var_export_contains(lv_arr_collection::unwrap(lv_arr_collect('John Doe')), '', true).']';
			if(var_export_contains(
				lv_arr_collection::unwrap(lv_arr_collect('John Doe')),
				"array(0=>'JohnDoe',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collection::unwrap(['John Doe']), '', true).']';
			if(var_export_contains(
				lv_arr_collection::unwrap(['John Doe']),
				"array(0=>'JohnDoe',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			//echo ' ['.var_export_contains(lv_arr_collection::unwrap('John Doe'), '', true).']';
			if(lv_arr_collection::unwrap('John Doe') === 'John Doe')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> value';
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Speaker', 'price'=>400]
			]);
			//echo ' ['.var_export_contains($collection->value('price'), '', true).']';
			if($collection->value('price') === 200)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> values';
			$collection=lv_arr_collect([
				10=>['product'=>'Desk', 'price'=>200],
				11=>['product'=>'Desk', 'price'=>200]
			]);
			$values=$collection->values();
			//echo ' ['.var_export_contains($values->all(), '', true).']';
			if(var_export_contains(
				$values->all(),
				"array(0=>array('product'=>'Desk','price'=>200,),1=>array('product'=>'Desk','price'=>200,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> when';
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->when(true, function(lv_arr_collection $collection, int $value){
				return $collection->push(4);
			});
			$collection->when(false, function(lv_arr_collection $collection, int $value){
				return $collection->push(5);
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,2=>3,3=>4,)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->when(false, function(lv_arr_collection $collection, int $value){
				return $collection->push(4);
			}, function(lv_arr_collection $collection){
				return $collection->push(5);
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>1,1=>2,2=>3,3=>5,)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> when_empty';
			$collection=lv_arr_collect(['Michael', 'Tom']);
			$collection->when_empty(function(lv_arr_collection $collection){
				return $collection->push('Adam');
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'Michael',1=>'Tom',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect();
			$collection->when_empty(function(lv_arr_collection $collection){
				return $collection->push('Adam');
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'Adam',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect(['Michael', 'Tom']);
			$collection->when_empty(function(lv_arr_collection $collection){
				return $collection->push('Adam');
			}, function(lv_arr_collection $collection){
				return $collection->push('Taylor');
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'Michael',1=>'Tom',2=>'Taylor',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> when_not_empty';
			$collection=lv_arr_collect(['michael', 'tom']);
			$collection->when_not_empty(function(lv_arr_collection $collection){
				return $collection->push('adam');
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'michael',1=>'tom',2=>'adam',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect();
			$collection->when_not_empty(function(lv_arr_collection $collection){
				return $collection->push('adam');
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains($collection->all(), "array()"))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect();
			$collection->when_not_empty(function(lv_arr_collection $collection){
				return $collection->push('adam');
			}, function(lv_arr_collection $collection){
				return $collection->push('taylor');
			});
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'taylor',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where';
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where('price', 100);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(1=>array('product'=>'Chair','price'=>100,),3=>array('product'=>'Door','price'=>100,),)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collect([
				['name'=>'Jim', 'deleted_at'=>'2019-01-01 00:00:00'],
				['name'=>'Sally', 'deleted_at'=>'2019-01-02 00:00:00'],
				['name'=>'Sue', 'deleted_at'=>null]
			]);
			$filtered=$collection->where('deleted_at', '!=', null);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(0=>array('name'=>'Jim','deleted_at'=>'2019-01-0100:00:00',),1=>array('name'=>'Sally','deleted_at'=>'2019-01-0200:00:00',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where_strict [SKIP]'.PHP_EOL;
		echo '  -> where_between';
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>80],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Pencil', 'price'=>30],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_between('price', [100, 200]);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(0=>array('product'=>'Desk','price'=>200,),2=>array('product'=>'Bookcase','price'=>150,),4=>array('product'=>'Door','price'=>100,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where_in';
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_in('price', [150, 200]);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(0=>array('product'=>'Desk','price'=>200,),2=>array('product'=>'Bookcase','price'=>150,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where_in_strict [SKIP]'.PHP_EOL;
		echo '  -> where_instance_of';
			class where_instance_of_u {}
			class where_instance_of_p {}
			$collection=lv_arr_collect([
				new where_instance_of_u(),
				new where_instance_of_u(),
				new where_instance_of_p()
			]);
			$filtered=$collection->where_instance_of(where_instance_of_u::class);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(0=>where_instance_of_u::__set_state(array()),1=>where_instance_of_u::__set_state(array()),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where_not_between';
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>80],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Pencil', 'price'=>30],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_not_between('price', [100, 200]);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(1=>array('product'=>'Chair','price'=>80,),3=>array('product'=>'Pencil','price'=>30,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where_not_in';
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_not_in('price', [150, 200]);
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(1=>array('product'=>'Chair','price'=>100,),3=>array('product'=>'Door','price'=>100,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where_not_in_strict [SKIP]'.PHP_EOL;
		echo '  -> where_not_null';
			$collection=lv_arr_collect([
				['name'=>'Desk'],
				['name'=>null],
				['name'=>'Bookcase']
			]);
			$filtered=$collection->where_not_null('name');
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(0=>array('name'=>'Desk',),2=>array('name'=>'Bookcase',),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> where_null';
			$collection=lv_arr_collect([
				['name'=>'Desk'],
				['name'=>null],
				['name'=>'Bookcase']
			]);
			$filtered=$collection->where_null('name');
			//echo ' ['.var_export_contains($filtered->all(), '', true).']';
			if(var_export_contains(
				$filtered->all(),
				"array(1=>array('name'=>NULL,),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> wrap';
			$collection=lv_arr_collection::wrap('John Doe');
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'JohnDoe',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collection::wrap(['John Doe']);
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'JohnDoe',)"
			))
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$failed=true;
			}
			$collection=lv_arr_collection::wrap(lv_arr_collect('John Doe'));
			//echo ' ['.var_export_contains($collection->all(), '', true).']';
			if(var_export_contains(
				$collection->all(),
				"array(0=>'JohnDoe',)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> zip';
			$collection=lv_arr_collect(['Chair', 'Desk']);
			$zipped=$collection->zip([100, 200]);
			//echo ' ['.var_export_contains($zipped->all(), '', true).']';
			if(var_export_contains(
				$zipped->all(),
				"array(0=>lv_arr_collection::__set_state(array('items'=>array(0=>'Chair',1=>100,),)),1=>lv_arr_collection::__set_state(array('items'=>array(0=>'Desk',1=>200,),)),)"
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>