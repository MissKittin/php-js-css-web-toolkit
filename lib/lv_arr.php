<?php
	/*
	 * Laravel 10 array helpers & collections
	 *
	 * This library is licensed under the MIT license, see https://github.com/illuminate/collections/blob/master/LICENSE.md
	 *
	 * Note:
	 *  throws an lv_arr_exception on error
	 *
	 * Implemented functions:
	 *  lv_arr_accessible()
	 *   determines if the given value is array accessible
			$is_accessible=lv_arr_accessible(['a'=>1, 'b'=>2]); // true
			$is_accessible=lv_arr_accessible(new Collection()); // not implemented
			$is_accessible=lv_arr_accessible('abc'); // false
			$is_accessible=lv_arr_accessible(new stdClass()); // false
	 *  lv_arr_add()
	 *   adds a given key/value pair to an array if the given key
	 *   doesn't already exist in the array or is set to null
			$array=lv_arr_add(['name'=>'Desk'], 'price', 100);
			// ['name'=>'Desk', 'price'=>100]
			$array=lv_arr_add(['name'=>'Desk', 'price'=>null], 'price', 100);
			// ['name'=>'Desk', 'price'=>100]
	 *  lv_arr_collapse()
	 *   collapses an array of arrays into a single array
			$array=lv_arr_collapse([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);
			// [1, 2, 3, 4, 5, 6, 7, 8, 9]
	 *  lv_arr_collect()
	 *   returns a new lv_arr_collection instance with the items currently in the collection
			$collection_a=lv_arr_collect([1, 2, 3]);
			$collection_b=$collection_a->collect();
			$collection_b->all();
			// [1, 2, 3]
	 *   warning:
	 *    lv_arr_collection class is required
	 *  lv_arr_cross_join()
	 *   cross joins the given arrays, returning a Cartesian product
	 *   with all possible permutations
			$matrix=lv_arr_cross_join([1, 2], ['a', 'b']);
			// [[1, 'a'], [1, 'b'], [2, 'a'], [2, 'b']]
			$matrix=lv_arr_cross_join([1, 2], ['a', 'b'], ['I', 'II']);
			// [[1, 'a', 'I'], [1, 'a', 'II'], [1, 'b', 'I'], [1, 'b', 'II'], [2, 'a', 'I'], [2, 'a', 'II'], [2, 'b', 'I'], [2, 'b', 'II']]
	 *  lv_arr_divide()
	 *   returns two arrays: one containing the keys
	 *   and the other containing the values of the given array
			[$keys, $values]=lv_arr_divide(['name'=>'Desk']);
			// $keys: ['name']
			// $values: ['Desk']
	 *  lv_arr_dot()
	 *   flattens a multi-dimensional array into a single level array
	 *   that uses "dot" notation to indicate depth
			$flattened=lv_arr_dot(['products'=>['desk'=>['price'=>100]]]);
			// ['products.desk.price'=>100]
	 *  lv_arr_except()
	 *   removes the given key/value pairs from an array
			$array=['name'=>'Desk', 'price'=>100];
			$filtered=lv_arr_except($array, ['price']);
			// ['name'=>'Desk']
	 *   warning:
	 *    lv_arr_forget function is required
	 *  lv_arr_exists()
	 *   checks that the given key exists in the provided array
			$array=['name'=>'John Doe', 'age'=>17];
			$exists=lv_arr_exists($array, 'name'); // true
			$exists=lv_arr_exists($array, 'salary'); // false
	 *  lv_arr_first()
	 *   returns the first element of an array passing a given truth test
			$array=[100, 200, 300];
			$first=lv_arr_first($array, function(int $value, int $key){
				return ($value >= 150);
			}); // 200
	 *   a default value may also be passed as the third parameter to the function
	 *   this value will be returned if no value passes the truth test
			$first=lv_arr_first($array, $callback, $default);
	 *   warning:
	 *    lv_arr_value function is required
	 *  lv_arr_flatten()
	 *   flattens a multi-dimensional array into a single level array
			$flattened=lv_arr_flatten(['name'=>'Joe', 'languages'=>['PHP', 'Ruby']]);
			// ['Joe', 'PHP', 'Ruby']
	 *  lv_arr_forget()
	 *   removes a given key/value pair
	 *   from a deeply nested array using "dot" notation
			$array=['products'=>['desk'=>['price'=>100]]];
			lv_arr_forget($array, 'products.desk');
			// ['products'=>[]]
	 *   warning:
	 *    lv_arr_accessible function is required
	 *    lv_arr_exists function is required
	 *  lv_arr_get()
	 *   retrieves a value from a deeply nested array
	 *   using "dot" notation
			$array=['products'=>['desk'=>['price'=>100]]];
			$price=lv_arr_get($array, 'products.desk.price'); // 100
	 *   also accepts a default value, which will be returned
	 *   if the specified key is not present in the array
			$discount=lv_arr_get($array, 'products.desk.discount', 0); // 0
	 *   warning:
	 *    lv_arr_accessible function is required
	 *    lv_arr_exists function is required
	 *    lv_arr_value function is required
	 *  lv_arr_has()
	 *   checks whether a given item or items exists
	 *   in an array using "dot" notation
			$array=['product'=>['name'=>'Desk', 'price'=>100]];
			$contains=lv_arr_has($array, 'product.name'); // true
			$contains=lv_arr_has($array, ['product.price', 'product.discount']); // false
	 *   warning:
	 *    lv_arr_accessible function is required
	 *    lv_arr_exists function is required
	 *  lv_arr_has_any()
	 *   checks whether any item in a given set exists
	 *   in an array using "dot" notation
			$array=['product'=>['name'=>'Desk', 'price'=>100]];
			$contains=lv_arr_has_any($array, 'product.name'); // true
			$contains=lv_arr_has_any($array, ['product.name', 'product.discount']); // true
			$contains=lv_arr_has_any($array, ['category', 'product.discount']); // false
	 *   warning:
	 *    lv_arr_has function is required
	 *  lv_arr_is_assoc()
	 *   returns true if the given array is an associative array
	 *   an array is considered "associative"
	 *   if it doesn't have sequential numerical keys beginning with zero
			$is_assoc=lv_arr_is_assoc(['product'=>['name'=>'Desk', 'price'=>100]]); // true
			$is_assoc=lv_arr_is_assoc([1, 2, 3]); // false
	 *  lv_arr_is_list()
	 *   returns true if the given array's keys
	 *   are sequential integers beginning from zero
			$is_list=lv_arr_is_list(['foo', 'bar', 'baz']); // true
			$is_list=lv_arr_is_list(['product'=>['name'=>'Desk', 'price'=>100]]); // false
	 *   warning:
	 *    lv_arr_is_assoc function is required
	 *  lv_arr_join()
	 *   joins array elements with a string
	 *   using this method's second argument, you may also specify
	 *   the joining string for the final element of the array
			$array=['Tailwind', 'Alpine', 'Laravel', 'Livewire'];
			$joined=lv_arr_join($array, ', ');
			// Tailwind, Alpine, Laravel, Livewire
			$joined=lv_arr_join($array, ', ', ' and ');
			// Tailwind, Alpine, Laravel and Livewire
	 *  lv_arr_key_by()
	 *   keys the array by the given key.
	 *   if multiple items have the same key,
	 *   only the last one will appear in the new array
			$array=[
				['product_id'=>'prod-100', 'name'=>'Desk'],
				['product_id'=>'prod-200', 'name'=>'Chair']
			];
			$keyed=lv_arr_key_by($array, 'product_id');
			// [
			//	'prod-100'=>['product_id'=>'prod-100', 'name'=>'Desk'],
			//	'prod-200'=>['product_id'=>'prod-200', 'name'=>'Chair']
			// ]
	 *   warning:
	 *    lv_arr_data_get function is required
	 *  lv_arr_last()
	 *   returns the last element of an array passing a given truth test
			$array=[100, 200, 300, 110];
			$last=lv_arr_last($array, function(int $value, int $key){
				return ($value >= 150);
			}); // 300
	 *   a default value may be passed as the third argument to the function
	 *   this value will be returned if no value passes the truth test
			$last=lv_arr_last($array, $callback, $default);
	 *   warning:
	 *    lv_arr_first function is required
	 *    lv_arr_value function is required
	 *  lv_arr_lazy_collect()
	 *   returns a new lv_arr_lazy_collection instance with the items currently in the collection
	 *   warning:
	 *    lv_arr_lazy_collection class is required
	 *  lv_arr_map()
	 *   iterates through the array and passes each value and key to the given callback
	 *   the array value is replaced by the value returned by the callback
			$array=['first'=>'james', 'last'=>'kirk'];
			$mapped=lv_arr_map($array, function(string $value, string $key){
				return ucfirst($value);
			});
			// ['first'=>'James', 'last'=>'Kirk']
	 *  lv_arr_map_with_keys()
	 *   iterates through the array and passes each value to the given callback
	 *   the callback should return an associative array containing a single key/value pair
			$array=[
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
			];
			$mapped=lv_arr_map_with_keys($array, function(array $item, int $key){
				return [
					$item['email']=>$item['name']
				];
			});
			// [
			//  'john@example.com'=>'John',
			//  'jane@example.com'=>'Jane'
			// ]
	 *  lv_arr_only()
	 *   returns only the specified key/value pairs from the given array
			$array=['name'=>'Desk', 'price'=>100, 'orders'=>10];
			$slice=lv_arr_only($array, ['name', 'price']);
			// ['name'=>'Desk', 'price'=>100]
	 *  lv_arr_pluck()
	 *   retrieves all of the values for a given key from an array
			$array=[
				['developer'=>['id'=>1, 'name'=>'Taylor']],
				['developer'=>['id'=>2, 'name'=>'Abigail']]
			];
			$names=lv_arr_pluck($array, 'developer.name');
			// ['Taylor', 'Abigail']
	 *   you may also specify how you wish the resulting list to be keyed
			$names=lv_arr_pluck($array, 'developer.name', 'developer.id');
			// [1=>'Taylor', 2=>'Abigail']
	 *   warning:
	 *    lv_arr_data_get function is required
	 *  lv_arr_prepend()
	 *   push an item onto the beginning of an array
			$array=['one', 'two', 'three', 'four'];
			$array=lv_arr_prepend($array, 'zero');
			// ['zero', 'one', 'two', 'three', 'four']
	 *   if needed, you may specify the key that should be used for the value
			$array=['price'=>100];
			$array=lv_arr_prepend($array, 'Desk', 'name');
			// ['name'=>'Desk', 'price'=>100]
	 *  lv_arr_prepend_keys_with()
	 *   prepends all key names of an associative array with the given prefix
			$array=[
				'name'=>'Desk',
				'price'=>100
			];
			$keyed=lv_arr_prepend_keys_with($array, 'product.');
			// [
			//  'product.name'=>'Desk',
			//  'product.price'=>100
			// ]
	 *  lv_arr_pull()
	 *   returns and removes a key/value pair from an array
			$array=['name'=>'Desk', 'price'=>100];
			$name=lv_arr_pull($array, 'name');
			// $name: Desk
			// $array: ['price'=>100]
	 *   a default value may be passed as the third argument to the function
	 *   this value will be returned if the key doesn't exist
			$value=lv_arr_pull($array, $key, $default);
	 *   warning:
	 *    lv_arr_forget function is required
	 *    lv_arr_get function is required
	 *  lv_arr_query()
	 *   converts the array into a query string
			$array=[
				'name'=>'Taylor',
				'order'=>[
					'column'=>'created_at',
					'direction'=>'desc'
				]
			];
			$query=lv_arr_query($array);
			// name=Taylor&order[column]=created_at&order[direction]=desc
	 *  lv_arr_random()
	 *   comes from branch 10.x
	 *   returns a random value from an array
			$array=[1, 2, 3, 4, 5];
			$random=lv_arr_random($array); // 4 - (retrieved randomly)
	 *   you may also specify the number of items to return as an optional second argument
	 *   note that providing this argument will return an array even if only one item is desired
			$items=lv_arr_random($array, 2);
			// [2, 5] - (retrieved randomly)
	 *  lv_arr_select()
	 *   select an array of values from an array
	 *   note:
	 *    the lv_arr_collection::select method depends on this function
	 *    this function does not exist in the official documentation
	 *   warning:
	 *    lv_arr_accessible function is required
	 *    lv_arr_exists function is required
	 *    lv_arr_map function is required
	 *  lv_arr_set()
	 *   sets a value within a deeply nested array
	 *   using "dot" notation
			$array=['products'=>['desk'=>['price'=>100]]];
			lv_arr_set($array, 'products.desk.price', 200);
			// ['products'=>['desk'=>['price'=>200]]]
	 *  lv_arr_shuffle()
	 *   comes from branch 10.x
	 *   randomly shuffles the items in the array
			$array=lv_arr_shuffle([1, 2, 3, 4, 5]); // [3, 2, 5, 1, 4] - (generated randomly)
	 *  lv_arr_sort()
	 *   sorts an array by its values
			$array=['Desk', 'Table', 'Chair'];
			$sorted=lv_arr_sort($array);
			// ['Chair', 'Desk', 'Table']
	 *   you may also sort the array by the results of a given closure
			$array=[
				['name'=>'Desk'],
				['name'=>'Table'],
				['name'=>'Chair']
			];
			$sorted=array_values(lv_arr_sort($array, function(array $value){
				return $value['name'];
			}));
			// [
			//  ['name'=>'Chair'],
			//  ['name'=>'Desk'],
			//  ['name'=>'Table']
			// ]
	 *  lv_arr_sort_desc()
	 *   sorts an array in descending order by its values
			$array=['Desk', 'Table', 'Chair'];
			$sorted=lv_arr_sort_desc($array);
			// ['Table', 'Desk', 'Chair']
	 *   you may also sort the array by the results of a given closure
			$array=[
				['name'=>'Desk'],
				['name'=>'Table'],
				['name'=>'Chair']
			];
			$sorted=array_values(lv_arr_sort_desc($array, function(array $value){
				return $value['name'];
			}));
			// [
			//  ['name'=>'Table'],
			//  ['name'=>'Desk'],
			//  ['name'=>'Chair']
			// ]
	 *  lv_arr_sort_recursive()
	 *   recursively sorts an array using the sort function
	 *   for numerically indexed sub-arrays
	 *   and the ksort function for associative sub-arrays
			$array=[
				['Roman', 'Taylor', 'Li'],
				['PHP', 'Ruby', 'JavaScript'],
				['one'=>1, 'two'=>2, 'three'=>3]
			];
			$sorted=lv_arr_sort_recursive($array);
			// [
			//  ['JavaScript', 'PHP', 'Ruby'],
			//  ['one'=>1, 'three'=>3, 'two'=>2],
			//  ['Li', 'Roman', 'Taylor']
			// ]
	 *   if you would like the results sorted in descending order
	 *   you may use the lv_arr_sort_recursive_desc function
			$sorted=lv_arr_sort_recursive_desc($array);
	 *   warning:
	 *    lv_arr_is_assoc function is required
	 *  lv_arr_sort_recursive_desc()
	 *   warning:
	 *    lv_arr_sort_recursive function is required
	 *  lv_arr_to_css_classes()
	 *   conditionally compiles a CSS class string
	 *   the method accepts an array of classes where the array key contains
	 *   the class or classes you wish to add, while the value is a boolean expression
	 *   if the array element has a numeric key
	 *   it will always be included in the rendered class list
			$is_active=false;
			$has_error=true;
			$array=['p-4', 'font-bold'=>$is_active, 'bg-red'=>$has_error];
			$classes=lv_arr_to_css_classes($array);
			// 'p-4 bg-red'
	 *  lv_arr_undot()
	 *   expands a single-dimensional array that uses "dot" notation
	 *   into a multi-dimensional array
			$array=[
				'user.name'=>'Kevin Malone',
				'user.occupation'=>'Accountant'
			];
			$array=lv_arr_undot($array);
			// ['user'=>['name'=>'Kevin Malone', 'occupation'=>'Accountant']]
	 *   warning:
	 *    lv_arr_set function is required
	 *  lv_arr_where()
	 *   filters an array using the given closure
			$array=[100, '200', 300, '400', 500];
			$filtered=lv_arr_where($array, function(string|int $value, int $key){
				return is_string($value);
			});
			// [1=>'200', 3=>'400']
	 *  lv_arr_where_not_null()
	 *   removes all null values from the given array
			$array=[0, null];
			$filtered=lv_arr_where_not_null($array);
			// [0=>0]
	 *  lv_arr_wrap()
	 *   wraps the given value in an array
	 *   if the given value is already an array it will be returned without modification
			$string='Laravel';
			$array=lv_arr_wrap($string); // ['Laravel']
	 *   if the given value is null, an empty array will be returned
			$array=lv_arr_wrap(null); // []
	 *  lv_arr_data_fill()
	 *   sets a missing value
	 *   within a nested array or object
	 *   using "dot" notation
			$data=['products'=>['desk'=>['price'=>100]]];
			lv_arr_data_fill($data, 'products.desk.price', 200);
			// ['products'=>['desk'=>['price'=>100]]]
			lv_arr_data_fill($data, 'products.desk.discount', 10);
			// ['products'=>['desk'=>['price'=>100, 'discount'=>10]]]
	 *   this function also accepts asterisks
	 *   as wildcards and will fill the target accordingly
			$data=[
				'products'=>[
					['name'=>'Desk 1', 'price'=>100],
					['name'=>'Desk 2']
				]
			];
			lv_arr_data_fill($data, 'products.*.price', 200);
			// [
			//  'products'=>[
			//   ['name'=>'Desk 1', 'price'=>100],
			//   ['name'=>'Desk 2', 'price'=>200]
			//  ]
			// ]
	 *   warning:
	 *    lv_arr_set function is required
	 *  lv_arr_data_get()
	 *   retrieves a value from a nested array or object
	 *   using "dot" notation
			$data=['products'=>['desk'=>['price'=>100]]];
			$price=lv_arr_data_get($data, 'products.desk.price'); // 100
	 *   also accepts a default value, which will be returned
	 *   if the specified key is not found
			$discount=lv_arr_data_get($data, 'products.desk.discount', 0); // 0
	 *   also accepts wildcards using asterisks,
	 *   which may target any key of the array or object
			$data=[
				'product-one'=>['name'=>'Desk 1', 'price'=>100],
				'product-two'=>['name'=>'Desk 2', 'price'=>150]
			];
			$name=lv_arr_data_get($data, '*.name');
			// ['Desk 1', 'Desk 2']
	 *   warning:
	 *    lv_arr_accessible function is required
	 *    lv_arr_collapse function is required
	 *    lv_arr_exists function is required
	 *    lv_arr_value function is required
	 *  lv_arr_data_set()
	 *   sets a value within a nested array or object
	 *   using "dot" notation
			$data=['products'=>['desk'=>['price'=>100]]];
			lv_arr_data_set($data, 'products.desk.price', 200);
			// ['products'=>['desk'=>['price'=>200]]]
	 *   also accepts wildcards using asterisks and will
	 *   set values on the target accordingly
			$data=[
				'products'=>[
					['name'=>'Desk 1', 'price'=>100],
					['name'=>'Desk 2', 'price'=>150]
				]
			];
			lv_arr_data_set($data, 'products.*.price', 200);
			// [
			//  'products'=>[
			//   ['name'=>'Desk 1', 'price'=>200],
			//   ['name'=>'Desk 2', 'price'=>200]
			//  ]
			// ]
	 *   by default, any existing values are overwritten.
	 *   if you wish to only set a value if it doesn't exist,
	 *   you may pass false as the fourth argument to the function
			$data=['products'=>['desk'=>['price'=>100]]];
			lv_arr_data_set($data, 'products.desk.price', 200, false);
			// ['products'=>['desk'=>['price'=>100]]]
	 *   warning:
	 *    lv_arr_accessible function is required
	 *    lv_arr_exists function is required
	 *  lv_arr_data_forget()
	 *   removes a value within a nested array or object
	 *   using "dot" notation
			$data=['products'=>['desk'=>['price'=>100]]];
			lv_arr_data_forget($data, 'products.desk.price');
			// ['products'=>['desk'=>[]]]
	 *   also accepts wildcards using asterisks
	 *   and will remove values on the target accordingly
			$data=[
				'products'=>[
					['name'=>'Desk 1', 'price'=>100],
					['name'=>'Desk 2', 'price'=>150]
				]
			];
			lv_arr_data_forget($data, 'products.*.price');
			// [
			//  'products'=>[
			//   ['name'=>'Desk 1'],
			//   ['name'=>'Desk 2']
			//  ]
			// ]
	 *   warning:
	 *    lv_arr_accessible function is required
	 *    lv_arr_exists function is required
	 *    lv_arr_forget function is required
	 *  lv_arr_value()
	 *   returns the value it is given.
	 *   however, if you pass a closure to the function,
	 *   the closure will be executed and its returned value will be returned
			$result=lv_arr_value(true); // true
			$result=lv_arr_value(function(){
				return false;
			}); // false
	 *   additional arguments may be passed to the value function.
	 *   if the first argument is a closure then
	 *   the additional parameters will be passed to the closure as arguments,
	 *   otherwise they will be ignored
			$result=lv_arr_value(function(string $name){
				return $name;
			}, 'Taylor'); // 'Taylor'
	 *
	 * Implemented classes:
	 *  lv_arr_collection
	 *   implemented methods:
	 *    all()
	 *     returns the underlying array represented by the collection
			lv_arr_collect([1, 2, 3])->all(); // [1, 2, 3]
	 *    average()
	 *     alias for the avg method
	 *     warning:
	 *      avg method is required
	 *    avg()
	 *     returns the average value of a given key
			$average=lv_arr_collect([
				['foo'=>10],
				['foo'=>10],
				['foo'=>20],
				['foo'=>40]
			])->avg('foo'); // 20
			$average=lv_arr_collect([1, 1, 2, 4])->avg(); // 2
	 *     warning:
	 *      count method is required
	 *      filter method is required
	 *      map method is required
	 *      sum method is required
	 *      value_retriever method is required
	 *    chunk()
	 *     breaks the collection into multiple, smaller collections of a given size
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7]);
			$chunks=$collection->chunk(4);
			$chunks->all(); // [[1, 2, 3, 4], [5, 6, 7]]
	 *    chunk_while()
	 *     breaks the collection into multiple, smaller collections
	 *     based on the evaluation of the given callback
	 *     the $chunk variable passed to the closure may be used to
	 *     inspect the previous element
			$collection=lv_arr_collect(str_split('AABBCCCD'));
			$chunks=$collection->chunk_while(function(string $value, int $key, lv_arr_collection $chunk){
				return ($value === $chunk->last());
			});
			$chunks->all();
			// [['A', 'A'], ['B', 'B'], ['C', 'C', 'C'], ['D']]
	 *     warning:
	 *      lazy method is required
	 *    collapse()
	 *     collapses a collection of arrays into a single, flat collection
			$collection=lv_arr_collect([
				[1, 2, 3],
				[4, 5, 6],
				[7, 8, 9]
			]);
			$collapsed=$collection->collapse();
			$collapsed->all(); // [1, 2, 3, 4, 5, 6, 7, 8, 9]
	 *     warning:
	 *      lv_arr_collapse function is required
	 *    collect()
	 *     returns a new lv_arr_collection instance
	 *     with the items currently in the collection
			$collection_a=lv_arr_collect([1, 2, 3]);
			$collection_b=$collection_a->collect();
			$collection_b->all(); // [1, 2, 3]
	 *     warning:
	 *      lv_arr_collection class is required
	 *    combine()
	 *     combines the values of the collection, as keys,
	 *     with the values of another array or collection
			$collection=lv_arr_collect(['name', 'age']);
			$combined=$collection->combine(['George', 29]);
			$combined->all(); // ['name'=>'George', 'age'=>29]
	 *     warning:
	 *      all method is required
	 *      get_arrayable_items method is required
	 *    concat()
	 *     appends the given array or collection's values
	 *     onto the end of another collection
			$collection=lv_arr_collect(['John Doe']);
			$concatenated=$collection->concat(['Jane Doe'])->concat(['name'=>'Johnny Doe']);
			$concatenated->all(); // ['John Doe', 'Jane Doe', 'Johnny Doe']
	 *     warning:
	 *      push method is required
	 *    contains()
	 *     determines whether the collection contains a given item
	 *     you may pass a closure to the contains method to determine
	 *     if an element exists in the collection matching a given truth test
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->contains(function(int $value, int $key){
				return ($value > 5);
			}); // false
	 *     alternatively, you may pass a string to the contains method to determine
	 *     whether the collection contains a given item value
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>100]);
			$collection->contains('Desk'); // true
			$collection->contains('New York'); // false
	 *     you may also pass a key/value pair to the contains method,
	 *     which will determine if the given pair exists in the collection
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100]
			]);
			$collection->contains('product', 'Bookcase'); // false
	 *     the contains method uses "loose" comparisons when checking item values,
	 *     meaning a string with an integer value will be considered equal
	 *     to an integer of the same value
	 *     use the contains_strict method to filter using "strict" comparisons
	 *     for the inverse of contains, see the doesnt_contain method
	 *     warning:
	 *      first method is required
	 *      use_as_callable method is required
	 *    contains_one_item()
	 *     determines whether the collection contains a single item
			lv_arr_collect([])->contains_one_item(); // false
			lv_arr_collect(['1'])->contains_one_item(); // true
			lv_arr_collect(['1', '2'])->contains_one_item(); // false
	 *    contains_strict()
	 *     this method has the same signature as the contains method
	 *     however, all values are compared using "strict" comparisons
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      contains method is required
	 *      first method is required
	 *      use_as_callable method is required
	 *    count()
	 *     returns the total number of items in the collection
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$collection->count(); // 4
	 *    count_by()
	 *     counts the occurrences of values in the collection
	 *     by default, the method counts the occurrences of every element
	 *     allowing you to count certain "types" of elements in the collection
			$collection=lv_arr_collect([1, 2, 2, 2, 3]);
			$counted=$collection->count_by();
			$counted->all(); // [1=>1, 2=>3, 3=>1]
	 *     you pass a closure to the count_by method
	 *     to count all items by a custom value
			$collection=lv_arr_collect(['alice@gmail.com', 'bob@yahoo.com', 'carlos@gmail.com']);
			$counted=$collection->count_by(function(string $email){
				return substr(strrchr($email, "@"), 1);
			});
			$counted->all();
			// ['gmail.com'=>2, 'yahoo.com'=>1]
	 *     warning:
	 *      lazy method is required
	 *    cross_join()
	 *     cross joins the collection's values among the given arrays
	 *     or collections, returning a Cartesian product
	 *     with all possible permutations
			$collection=lv_arr_collect([1, 2]);
			$matrix=$collection->cross_join(['a', 'b']);
			$matrix->all();
			// [
			//  [1, 'a'],
			//  [1, 'b'],
			//  [2, 'a'],
			//  [2, 'b']
			// ]
			$collection=lv_arr_collect([1, 2]);
			$matrix=$collection->cross_join(['a', 'b'], ['I', 'II']);
			$matrix->all();
			// [
			//  [1, 'a', 'I'],
			//  [1, 'a', 'II'],
			//  [1, 'b', 'I'],
			//  [1, 'b', 'II'],
			//  [2, 'a', 'I'],
			//  [2, 'a', 'II'],
			//  [2, 'b', 'I'],
			//  [2, 'b', 'II']
			// ]
	 *     warning:
	 *      lv_arr_cross_join function is required
	 *    diff()
	 *     compares the collection against another collection
	 *     or a plain PHP array based on its values
	 *     this method will return the values in the original collection
	 *     that are not present in the given collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$diff=$collection->diff([2, 4, 6, 8]);
			$diff->all(); // [1, 3, 5]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    diff_assoc()
	 *     compares the collection against another collection
	 *     or a plain PHP array based on its keys and values
	 *     this method will return the key/value pairs
	 *     in the original collection that are not present
	 *     in the given collection
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
			$diff->all(); // ['color'=>'orange', 'remain'=>6]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    diff_assoc_using()
	 *     unlike diff_assoc, diff_assoc_using accepts
	 *     a user supplied callback function for the indices comparison
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
			$diff->all(); // ['color'=>'orange', 'remain'=>6]
	 *     the callback must be a comparison function that returns
	 *     an integer less than, equal to, or greater than zero
	 *     for more information, refer to the PHP documentation on
	 *     array_diff_uassoc, which is the PHP function that the
	 *     diff_assoc_using method utilizes internally
	 *     warning:
	 *      get_arrayable_items method is required
	 *    diff_keys()
	 *     compares the collection against another collection
	 *     or a plain PHP array based on its keys
	 *     this method will return the key/value pairs
	 *     in the original collection that are not present
	 *     in the given collection
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
			$diff->all(); // ['one'=>10, 'three'=>30, 'five'=>50]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    diff_keys_using()
	 *     get the items in the collection whose keys
	 *     are not present in the given items, using the callback
	 *     this method uses the array_diff_ukey PHP function
	 *     this method does not appear in the official documentation
			$collection=lv_arr_collect([
				'one'=>10,
				'two'=>20,
				'three'=>30,
				'four'=>40,
				'five'=>50
			]);
			$diff=$collection->diff_keys_using(
				[
					'two'=>2,
					'four'=>4,
					'six'=>6,
					'eight'=>8
				],
				function($a, $b)
				{
					if($a === $b)
						return 0;

					return -1;
				}
			);
			$diff->all(); // ['one'=>10, 'three'=>30, 'five'=>50]
	 *     warning:
	 *      this method was not tested
	 *      get_arrayable_items method is required
	 *    diff_using()
	 *     get the items in the collection that are not present
	 *     in the given items, using the callback
	 *     this method does not appear in the official documentation
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$diff=$collection->diff_using([2, 4, 6, 8], function($a, $b){
				if($a === $b)
					return 0;

				return -1;
			});
			$diff->all(); // [1, 3, 5]
	 *     warning:
	 *      this method was not tested
	 *      get_arrayable_items method is required
	 *    doesnt_contain()
	 *     determines whether the collection does not contain a given item
	 *     you may pass a closure to the doesnt_contain method to determine
	 *     if an element does not exist in the collection matching a given truth test
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->doesnt_contain(function(int $value, int $key){
				return ($value < 5);
			}); // false
	 *     alternatively, you may pass a string to the doesnt_contain method
	 *     to determine whether the collection does not contain a given item value
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>100]);
			$collection->doesnt_contain('Table'); // true
			$collection->doesnt_contain('Desk'); // false
	 *     You may also pass a key/value pair to the doesnt_contain method,
	 *     which will determine if the given pair does not exist in the collection
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100]
			]);
			$collection->doesnt_contain('product', 'Bookcase'); // true
	 *     the doesnt_contain method uses "loose" comparisons when checking item values,
	 *     meaning a string with an integer value will be considered equal
	 *     to an integer of the same value
	 *     warning:
	 *      contains method is required
	 *    dot()
	 *     flattens a multi-dimensional collection into a single level collection
	 *     that uses "dot" notation to indicate depth
			$collection=lv_arr_collect(['products'=>['desk'=>['price'=>100]]]);
			$flattened=$collection->dot();
			$flattened->all(); // ['products.desk.price'=>100]
	 *     warning:
	 *      lv_arr_dot function is required
	 *    duplicates()
	 *     retrieves and returns duplicate values from the collection
			$collection=lv_arr_collect(['a', 'b', 'a', 'c', 'b']);
			$collection->duplicates(); // [2=>'a', 4=>'b']
	 *     if the collection contains arrays or objects,
	 *     you can pass the key of the attributes that you wish
	 *     to check for duplicate values
			$employees=lv_arr_collect([
				['email'=>'abigail@example.com', 'position'=>'Developer'],
				['email'=>'james@example.com', 'position'=>'Designer'],
				['email'=>'victoria@example.com', 'position'=>'Developer']
			]);
			$employees->duplicates('position'); // [2=>'Developer']
	 *     warning:
	 *      duplicate_comparator method is required
	 *      first method is required
	 *      is_not_empty method is required
	 *      shift method is required
	 *      unique method is required
	 *      value_retriever method is required
	 *    duplicates_strict()
	 *     this method has the same signature as the duplicates method
	 *     however, all values are compared using "strict" comparisons
	 *     warning:
	 *      duplicates method is required
	 *    each()
	 *     iterates over the items in the collection and passes each item to a closure
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$collection->each(function(int $item, int $key){
				//
			});
	 *     if you would like to stop iterating through the items,
	 *     you may return false from your closure
			$collection->each(function(int $item, int $key){
				if(condition)
					return false;
			});
	 *    each_spread()
	 *     iterates over the collection's items,
	 *     passing each nested item value into the given callback
			$collection=lv_arr_collect([['John Doe', 35], ['Jane Doe', 33]]);
			$collection->each_spread(function(string $name, int $age){
				//
			});
	 *     you may stop iterating through the items by returning false from the callback
			$collection->each_spread(function(string $name, int $age){
				return false;
			});
	 *     warning:
	 *      each method is required
	 *    [static] empty()
	 *     create a new instance with no items
	 *    esacpe_when_casting_to_string()
	 *     indicate that the model's string representation
	 *     should be escaped when __toString is invoked
	 *     default: false
			$collection=lv_arr_collect(['a'=>1, 'b'=>2, 'c'=>3])->esacpe_when_casting_to_string(true);
			echo $collection; // '{&quot;a&quot;:1,&quot;b&quot;:2,&quot;c&quot;:3}'
			$collection->esacpe_when_casting_to_string(false);
			echo $collection; // '{"a":1,"b":2,"c":3}'
	 *    every()
	 *     may be used to verify that all elements of a collection pass a given truth test
			lv_arr_collect([1, 2, 3, 4])->every(function(int $value, int $key){
				return ($value > 2);
			}); // false
	 *     if the collection is empty, the every method will return true
			$collection=lv_arr_collect([]);
			$collection->every(function(int $value, int $key){
				return ($value > 2);
			}); // true
	 *     warning:
	 *      operator_for_where method is required
	 *      value_retriever method is required
	 *    except()
	 *     returns all items in the collection except for those with the specified keys
			$collection=lv_arr_collect(['product_id'=>1, 'price'=>100, 'discount'=>false]);
			$filtered=$collection->except(['price', 'discount']);
			$filtered->all(); // ['product_id'=>1]
	 *     warning:
	 *      lv_arr_except function is required
	 *      all method is required
	 *    filter()
	 *     filters the collection using the given callback,
	 *     keeping only those items that pass a given truth test
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$filtered=$collection->filter(function(int $value, int $key){
				return ($value > 2);
			});
			$filtered->all(); // [3, 4]
	 *     If no callback is supplied,
	 *     all entries of the collection that are equivalent to false will be removed
			$collection=lv_arr_collect([1, 2, 3, null, false, '', 0, []]);
			$collection->filter()->all(); // [1, 2, 3]
	 *     for the inverse of filter, see the reject method
	 *     warning:
	 *      lv_arr_where function is required
	 *    first()
	 *     returns the first element in the collection that passes a given truth test
			lv_arr_collect([1, 2, 3, 4])->first(function(int $value, int $key){
				return ($value > 2);
			}); // 3
	 *     you may also call the first method with no arguments
	 *     to get the first element in the collection
	 *     if the collection is empty, null is returned
			lv_arr_collect([1, 2, 3, 4])->first(); // 1
	 *     warning:
	 *      lv_arr_first function is required
	 *    first_or_fail()
	 *     the first_or_fail method is identical to the first method
	 *     however, if no result is found, an lv_arr_exception will be thrown
			lv_arr_collect([1, 2, 3, 4])->first_or_fail(function(int $value, int $key){
				return ($value > 5);
			}); // throws lv_arr_exception
	 *     you may also call the first_or_fail method with no arguments
	 *     to get the first element in the collection.
	 *     if the collection is empty, an lv_arr_exception will be thrown
			lv_arr_collect([])->first_or_fail(); // throws lv_arr_exception
	 *     warning:
	 *      offsetUnset method is required
	 *      operator_for_where method is required
	 *    first_where()
	 *     returns the first element in the collection with the given key/value pair
			$collection=lv_arr_collect([
				['name'=>'Regena', 'age'=>null],
				['name'=>'Linda', 'age'=>14],
				['name'=>'Diego', 'age'=>23],
				['name'=>'Linda', 'age'=>84]
			]);
			$collection->first_where('name', 'Linda');
			// ['name'=>'Linda', 'age'=>14]
	 *     you may also call the first_where method with a comparison operator
			$collection->first_where('age', '>=', 18);
			// ['name'=>'Diego', 'age'=>23]
	 *     like the where method, you may pass one argument
	 *     to the first_where method
	 *     in this scenario, the first_where method will return the first item
	 *     where the given item key's value is "truthy"
			$collection->first_where('age');
			// ['name'=>'Linda', 'age'=>14]
	 *     warning:
	 *      first method is required
	 *      operator_for_where method is required
	 *    flat_map()
	 *     iterates through the collection
	 *     and passes each value to the given closure
	 *     the closure is free to modify the item and return it,
	 *     thus forming a new collection of modified items
	 *     then, the array is flattened by one level
			$collection=lv_arr_collect([
				['name'=>'Sally'],
				['school'=>'Arkansas'],
				['age'=>28]
			]);
			$flattened=$collection->flat_map(function(array $values){
				return array_map('strtoupper', $values);
			});
			$flattened->all();
			// ['name'=>'SALLY', 'school'=>'ARKANSAS', 'age'=>'28']
	 *     warning:
	 *      collapse method is required
	 *      map method is required
	 *    flatten()
	 *     flattens a multi-dimensional collection into a single dimension
			$collection=lv_arr_collect([
				'name'=>'taylor',
				'languages'=>['php', 'javascript']
			]);
			$flattened=$collection->flatten();
			$flattened->all(); // ['taylor', 'php', 'javascript']
	 *     if necessary, you may pass the flatten method a "depth" argument
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
			$products->values()->all();
			// [
			//  ['name'=>'iPhone 6S', 'brand'=>'Apple'],
			//  ['name'=>'Galaxy S7', 'brand'=>'Samsung']
			// ]
	 *     in this example, calling flatten without providing the depth
	 *     would have also flattened the nested arrays,
	 *     resulting in ['iPhone 6S', 'Apple', 'Galaxy S7', 'Samsung']
	 *     providing a depth allows you to specify the number of levels
	 *     nested arrays will be flattened
	 *     warning:
	 *      lv_arr_flatten function is required
	 *    flip()
	 *     swaps the collection's keys with their corresponding values
			$collection=lv_arr_collect(['name'=>'taylor', 'framework'=>'laravel']);
			$flipped=$collection->flip();
			$flipped->all(); // ['taylor'=>'name', 'laravel'=>'framework']
	 *    forget()
	 *     removes an item from the collection by its key
			$collection=lv_arr_collect(['name'=>'taylor', 'framework'=>'laravel']);
			$collection->forget('name');
			$collection->all(); // ['framework'=>'laravel']
	 *     warning:
	 *      unlike most other collection methods,
	 *       forget does not return a new modified collection;
	 *       it modifies the collection it is called on
	 *      get_arrayable_items method is required
	 *    for_page()
	 *     returns a new collection containing the items
	 *     that would be present on a given page number
	 *     the method accepts the page number as its first argument
	 *     and the number of items
	 *     to show per page as its second argument
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
			$chunk=$collection->for_page(2, 3);
			$chunk->all(); // [4, 5, 6]
	 *     warning:
	 *      slice method is required
	 *    get()
	 *     returns the item at a given key
	 *     if the key does not exist, null is returned
			$collection=lv_arr_collect(['name'=>'taylor', 'framework'=>'laravel']);
			$value=$collection->get('name'); // taylor
	 *     you may optionally pass a default value as the second argument
			$collection=lv_arr_collect(['name'=>'taylor', 'framework'=>'laravel']);
			$value=$collection->get('age', 34); // 34
	 *     you may even pass a callback as the method's default value
	 *     the result of the callback will be returned
	 *     if the specified key does not exist
			$collection->get('email', function(){
				return 'taylor@example.com';
			}); // taylor@example.com
	 *     warning
	 *      lv_arr_value function is required
	 *    group_by()
	 *     groups the collection's items by a given key
			$collection=lv_arr_collect([
				['account_id'=>'account-x10', 'product'=>'Chair'],
				['account_id'=>'account-x10', 'product'=>'Bookcase'],
				['account_id'=>'account-x11', 'product'=>'Desk']
			]);
			$grouped=$collection->group_by('account_id');
			$grouped->all();
			// [
			//  'account-x10'=>[
			//   ['account_id'=>'account-x10', 'product'=>'Chair'],
			//   ['account_id'=>'account-x10', 'product'=>'Bookcase']
			//  ],
			//  'account-x11'=>[
			//   ['account_id'=>'account-x11', 'product'=>'Desk']
			//  ]
			// ]
	 *     instead of passing a string key, you may pass a callback
	 *     the callback should return the value you wish to key the group by
			$grouped=$collection->group_by(function(array $item, int $key){
				return substr($item['account_id'], -3);
			});
			$grouped->all();
			// [
			//  'x10'=>[
			//   ['account_id'=>'account-x10', 'product'=>'Chair'],
			//   ['account_id'=>'account-x10', 'product'=>'Bookcase']
			//  ],
			//  'x11'=>[
			//   ['account_id'=>'account-x11', 'product'=>'Desk']
			//  ]
			// ]
	 *     multiple grouping criteria may be passed as an array
	 *     each array element will be applied to the corresponding level
	 *     within a multi-dimensional array
			$data=new lv_arr_collection([
				10=>['user'=>1, 'skill'=>1, 'roles'=>['Role_1', 'Role_3']],
				20=>['user'=>2, 'skill'=>1, 'roles'=>['Role_1', 'Role_2']],
				30=>['user'=>3, 'skill'=>2, 'roles'=>['Role_1']],
				40=>['user'=>4, 'skill'=>2, 'roles'=>['Role_2']]
			]);
			$result=$data->group_by(['skill', function(array $item){
				return $item['roles'];
			}], preserve_keys: true);
			// [
			//  1=>[
			//   'Role_1'=>[
			//    10=>['user'=>1, 'skill'=>1, 'roles'=>['Role_1', 'Role_3']],
			//    20=>['user'=>2, 'skill'=>1, 'roles'=>['Role_1', 'Role_2']]
			//   ],
			//   'Role_2'=>[
			//    20=>['user'=>2, 'skill'=>1, 'roles'=>['Role_1', 'Role_2']]
			//   ],
			//   'Role_3'=>[
			//    10=>['user'=>1, 'skill'=>1, 'roles'=>['Role_1', 'Role_3']]
			//   ]
			//  ],
			//  2=>[
			//   'Role_1'=>[
			//    30=>['user'=>3, 'skill'=>2, 'roles'=>['Role_1']]
			//   ],
			//   'Role_2'=>[
			//    40=>['user'=>4, 'skill'=>2, 'roles'=>['Role_2']]
			//   ]
			//  ]
			// ]
	 *     warning:
	 *      offsetSet method is required
	 *      use_as_callable method is required
	 *      value_retriever method is required
	 *      __get and higher_order_collection_proxy methods are required
	 *    has()
	 *     determines if a given key exists in the collection
			$collection=lv_arr_collect(['account_id'=>1, 'product'=>'Desk', 'amount'=>5]);
			$collection->has('product'); // true
			$collection->has(['product', 'amount']); // true
			$collection->has(['amount', 'price']); // false
	 *    has_any()
	 *     determines whether any of the given keys exist in the collection
			$collection=lv_arr_collect(['account_id'=>1, 'product'=>'Desk', 'amount'=>5]);
			$collection->has_any(['product', 'price']); // true
			$collection->has_any(['name', 'price']); // false
	 *     warning:
	 *      has method is required
	 *      is_empty method is required
	 *    implode()
	 *     joins items in a collection
	 *     its arguments depend on the type of items in the collection
	 *     if the collection contains arrays or objects,
	 *     you should pass the key of the attributes you wish to join,
	 *     and the "glue" string you wish to place between the values
			$collection=lv_arr_collect([
				['account_id'=>1, 'product'=>'Desk'],
				['account_id'=>2, 'product'=>'Chair']
			]);
			$collection->implode('product', ', '); // Desk, Chair
	 *     if the collection contains simple strings or numeric values,
	 *     you should pass the "glue" as the only argument to the method
			lv_arr_collect([1, 2, 3, 4, 5])->implode('-');
			// '1-2-3-4-5'
	 *     you may pass a closure to the implode method
	 *     if you would like to format the values being imploded
			$collection->implode(function(array $item, int $key){
				return strtoupper($item['product']);
			}, ', '); // DESK, CHAIR
	 *     warning:
	 *      all method is required
	 *      first method is required
	 *      map method is required
	 *      pluck method is required
	 *      use_as_callable method is required
	 *    intersect()
	 *     removes any values from the original collection
	 *     that are not present in the given array or collection
	 *     the resulting collection will preserve
	 *     the original collection's keys
			$collection=lv_arr_collect(['Desk', 'Sofa', 'Chair']);
			$intersect=$collection->intersect(['Desk', 'Chair', 'Bookcase']);
			$intersect->all(); // [0=>'Desk', 2=>'Chair']
	 *     warning:
	 *      get_arrayable_items method is required
	 *    intersect_assoc()
	 *     compares the original collection against another collection or array
	 *     returning the key/value pairs that are present
	 *     in all of the given collections
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
			$intersect->all(); // ['size'=>'M']
	 *     warning:
	 *      get_arrayable_items method is required
	 *    intersect_assoc_using()
	 *     intersect the collection with the given items
	 *     with additional index check, using the callback
	 *     this method does not appear in the official documentation
			$collection=lv_arr_collect([
				'color'=>'red',
				'size'=>'M',
				'material'=>'cotton'
			]);
			$intersect=$collection->intersect_assoc_using([
				'color'=>'blue',
				'size'=>'M',
				'material'=>'polyester'
			], 'strcasecmp');
			$intersect->all(); // ['size'=>'M']
	 *     warning:
	 *      this method was not tested
	 *     warning:
	 *      get_arrayable_items method is required
	 *    intersect_by_keys()
	 *     removes any keys and their corresponding values
	 *     from the original collection that are not present
	 *     in the given array or collection
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
			$intersect->all(); // ['type'=>'screen', 'year'=>2009]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    intersect_using()
	 *     intersect the collection with the given items, using the callback
	 *     this method does not appear in the official documentation
			$collection=lv_arr_collect(['Desk', 'Sofa', 'Chair']);
			$intersect=$collection->intersect_using(['Desk', 'Chair', 'Bookcase'], 'strcasecmp');
			$intersect->all(); // [0=>'Desk', 2=>'Chair']
	 *     warning:
	 *      this method was not tested
	 *      get_arrayable_items method is required
	 *    is_empty()
	 *     returns true if the collection is empty
	 *     otherwise, false is returned
			lv_arr_collect([])->is_empty(); // true
	 *    is_not_empty()
	 *     returns true if the collection is not empty
	 *     otherwise, false is returned
			lv_arr_collect([])->is_not_empty(); // false
	 *     warning:
	 *      is_empty method is required
	 *    join()
	 *     joins the collection's values with a string
	 *     using this method's second argument,
	 *     you may also specify how the final element
	 *     should be appended to the string
			lv_arr_collect(['a', 'b', 'c'])->join(', '); // 'a, b, c'
			lv_arr_collect(['a', 'b', 'c'])->join(', ', ', and '); // 'a, b, and c'
			lv_arr_collect(['a', 'b'])->join(', ', ' and '); // 'a and b'
			lv_arr_collect(['a'])->join(', ', ' and '); // 'a'
			lv_arr_collect([])->join(', ', ' and '); // ''
	 *     warning:
	 *      last method is required
	 *      pop method is required
	 *    key_by()
	 *     keys the collection by the given key
	 *     if multiple items have the same key,
	 *     only the last one will appear in the new collection
			$collection=lv_arr_collect([
				['product_id'=>'prod-100', 'name'=>'Desk'],
				['product_id'=>'prod-200', 'name'=>'Chair']
			]);
			$keyed=$collection->key_by('product_id');
			$keyed->all();
			// [
			//  'prod-100'=>['product_id'=>'prod-100', 'name'=>'Desk'],
			//  'prod-200'=>['product_id'=>'prod-200', 'name'=>'Chair']
			// ]
	 *     you may also pass a callback to the method
	 *     the callback should return the value to key the collection by
			$keyed=$collection->key_by(function(array $item, int $key){
				return strtoupper($item['product_id']);
			});
			$keyed->all();
			// [
			//  'PROD-100'=>['product_id'=>'prod-100', 'name'=>'Desk'],
			//  'PROD-200'=>['product_id'=>'prod-200', 'name'=>'Chair']
			// ]
	 *     warning:
	 *      value_retriever method is required
	 *    keys()
	 *     returns all of the collection's keys
			$collection=lv_arr_collect([
				'prod-100'=>['product_id'=>'prod-100', 'name'=>'Desk'],
				'prod-200'=>['product_id'=>'prod-200', 'name'=>'Chair']
			]);
			$keys=$collection->keys();
			$keys->all(); // ['prod-100', 'prod-200']
	 *    last()
	 *     returns the last element in the collection
	 *     that passes a given truth test
			lv_arr_collect([1, 2, 3, 4])->last(function(int $value, int $key){
				return ($value < 3);
			}); // 2
	 *     you may also call the last method with no arguments
	 *     to get the last element in the collection
	 *     if the collection is empty, null is returned
			lv_arr_collect([1, 2, 3, 4])->last(); // 4
	 *     warning:
	 *      lv_arr_last function is required
	 *    lazy()
	 *     returns a new lv_arr_lazy_collection instance
	 *     from the underlying array of items
			$lazy_collection=lv_arr_collect([1, 2, 3, 4])->lazy();
			$lazy_collection::class; // lv_arr_lazy_collection
			$lazy_collection->all(); // [1, 2, 3, 4]
	 *     this is especially useful when you need to perform transformations
	 *     on a huge lv_arr_collection that contains many items
			$count=$huge_collection
				->lazy()
				->where('country', 'FR')
				->where('balance', '>', '100')
				->count();
	 *     by converting the collection to a lazy collection
	 *     we avoid having to allocate a ton of additional memory
	 *     though the original collection still keeps its values in memory
	 *     the subsequent filters will not
	 *     therefore, virtually no additional memory will be allocated
	 *     when filtering the collection's results
	 *     warning:
	 *      lv_arr_lazy_collection class is required
	 *    [static] make()
	 *     returns a new lv_arr_collection instance with the items currently in the collection
			$collection=lv_arr_collection::make([1, 2, 3]);
			$collection->all();
			// [1, 2, 3]
	 *    map()
	 *     iterates through the collection
	 *     and passes each value to the given callback
	 *     the callback is free to modify the item and return it,
	 *     thus forming a new collection of modified items
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$multiplied=$collection->map(function(int $item, int $key){
				return ($item*2);
			});
			$multiplied->all(); // [2, 4, 6, 8, 10]
	 *     warning:
	 *      like most other collection methods,
	 *       map returns a new collection instance
	 *       it does not modify the collection it is called on
	 *       if you want to transform the original collection,
	 *       use the transform method
	 *      lv_arr_map function is required
	 *    map_into()
	 *     iterates over the collection
	 *     creating a new instance of the given class
	 *     by passing the value into the constructor
			class currency
			{
				public function __construct(string $code) {}
			}
			$collection=lv_arr_collect(['USD', 'EUR', 'GBP']);
			$currencies=$collection->map_into(currency::class);
			$currencies->all();
			// [Currency('USD'), Currency('EUR'), Currency('GBP')]
	 *     warning:
	 *      map method is required
	 *    map_spread()
	 *     iterates over the collection's items
	 *     passing each nested item value into the given closure
	 *     the closure is free to modify the item and return it,
	 *     thus forming a new collection of modified items
			$collection=lv_arr_collect([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
			$chunks=$collection->chunk(2);
			$sequence=$chunks->map_spread(function(int $even, int $odd){
				return ($even+$odd);
			});
			$sequence->all(); // [1, 5, 9, 13, 17]
	 *     warning:
	 *      map method is required
	 *    map_to_dictionary()
	 *     reduces collection such that the final result is
	 *     a collection of key/value pairs
	 *     the resulting value for each key may be a single value
	 *     or another collection of items
	 *     the behavior of the map_to_dictionary method ensures
	 *     that all key/value pairs contain an array as the value
	 *     even if the value contains one element
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
			$scores->all();
			// [
			//  'Bob'=>[
			//   0=>0.84,
			//   1=>0.98
			//  ],
			//  'Alice'=>[
			//   0=>0.95,
			//   1=>0.92
			//  ],
			//  'Charlie'=>[
			//   0=>0.78
			//  ]
			// ]
	 *    map_to_groups()
	 *     groups the collection's items by the given closure
	 *     the closure should return an associative array
	 *     containing a single key/value pair
	 *     thus forming a new collection of grouped values
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
			$grouped->all();
			// [
			//  'Sales'=>['John Doe', 'Jane Doe'],
			//  'Marketing'=>['Johnny Doe']
			// ]
			$grouped->get('Sales')->all();
			// ['John Doe', 'Jane Doe']
	 *     warning:
	 *      map method is required
	 *      map_to_dictionary method is required
	 *    map_with_keys()
	 *     iterates through the collection
	 *     and passes each value to the given callback
	 *     the callback should return an associative array
	 *     containing a single key/value pair
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
			$keyed->all();
			// [
			//  'john@example.com'=>'John',
			//  'jane@example.com'=>'Jane'
			// ]
	 *     warning:
	 *      lv_arr_map_with_keys function is required
	 *    max()
	 *     returns the maximum value of a given key
			$max=lv_arr_collect([
				['foo'=>10],
				['foo'=>20]
			])->max('foo'); // 20
			$max=lv_arr_collect([1, 2, 3, 4, 5])->max(); // 5
	 *     warning:
	 *      filter method is required
	 *      reduce method is required
	 *      value_retriever method is required
	 *    median()
	 *     returns the median value of a given key
			$median=lv_arr_collect([
				['foo'=>10],
				['foo'=>10],
				['foo'=>20],
				['foo'=>40]
			])->median('foo'); // 15
			$median=lv_arr_collect([1, 1, 2, 4])->median(); // 1.5
	 *     warning:
	 *      average method is required
	 *      count method is required
	 *      filter method is required
	 *      get method is required
	 *      pluck method is required
	 *      sort method is required
	 *      values method is required
	 *    merge()
	 *     merges the given array or collection with the original collection
	 *     if a string key in the given items matches a string key
	 *     in the original collection, the given item's value
	 *     will overwrite the value in the original collection
			$collection=lv_arr_collect(['product_id'=>1, 'price'=>100]);
			$merged=$collection->merge(['price'=>200, 'discount'=>false]);
			$merged->all();
			// ['product_id'=>1, 'price'=>200, 'discount'=>false]
	 *     if the given item's keys are numeric
	 *     the values will be appended to the end of the collection
			$collection=lv_arr_collect(['Desk', 'Chair']);
			$merged=$collection->merge(['Bookcase', 'Door']);
			$merged->all(); // ['Desk', 'Chair', 'Bookcase', 'Door']
	 *     warning:
	 *      get_arrayable_items method is required
	 *    merge_recursive()
	 *     merges the given array or collection recursively
	 *     with the original collection
	 *     if a string key in the given items matches a string key
	 *     in the original collection, then the values for these keys
	 *     are merged together into an array, and this is done recursively
			$collection=lv_arr_collect(['product_id'=>1, 'price'=>100]);
			$merged=$collection->merge_recursive([
				'product_id'=>2,
				'price'=>200,
				'discount'=>false
			]);
			$merged->all();
			// ['product_id'=>[1, 2], 'price'=>[100, 200], 'discount'=>false]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    min()
	 *     returns the minimum value of a given key
			$min=lv_arr_collect([
				['foo'=>10],
				['foo'=>20]
			])->min('foo'); // 10
			$min=lv_arr_collect([1, 2, 3, 4, 5])->min(); // 1
	 *     warning:
	 *      filter method is required
	 *      map method is required
	 *      reduce method is required
	 *      value_retriever method is required
	 *    mode()
	 *     returns the mode value of a given key
			$mode=lv_arr_collect([
				['foo'=>10],
				['foo'=>10],
				['foo'=>20],
				['foo'=>40]
			])->mode('foo'); // [10]
			$mode=lv_arr_collect([1, 1, 2, 4])->mode(); // [1]
			$mode=lv_arr_collect([1, 1, 2, 2])->mode(); // [1, 2]
	 *     warning:
	 *      all method is required
	 *      count method is required
	 *      filter method is required
	 *      keys method is required
	 *      last method is required
	 *      pluck method is required
	 *      sort method is required
	 *    nth()
	 *     creates a new collection consisting of every n-th element
			$collection=lv_arr_collect(['a', 'b', 'c', 'd', 'e', 'f']);
			$collection->nth(4); // ['a', 'e']
	 *     you may optionally pass a starting offset as the second argument
			$collection->nth(4, 1); // ['b', 'f']
	 *     warning:
	 *      slice method is required
	 *    only()
	 *     returns the items in the collection with the specified keys
			$collection=lv_arr_collect([
				'product_id'=>1,
				'name'=>'Desk',
				'price'=>100,
				'discount'=>false
			]);
			$filtered=$collection->only(['product_id', 'name']);
			$filtered->all();
			// ['product_id'=>1, 'name'=>'Desk']
	 *     for the inverse of only, see the except method
	 *     warning:
	 *      lv_arr_only function is required
	 *      all method is required
	 *    pad()
	 *     fills the array with the given value
	 *     until the array reaches the specified size
	 *     this method behaves like the array_pad PHP function
	 *     to pad to the left, you should specify a negative size
	 *     no padding will take place if the absolute value
	 *     of the given size is less than or equal to the length of the array
			$collection=lv_arr_collect(['A', 'B', 'C']);
			$filtered=$collection->pad(5, 0);
			$filtered->all(); // ['A', 'B', 'C', 0, 0]
			$filtered=$collection->pad(-5, 0);
			$filtered->all(); // [0, 0, 'A', 'B', 'C']
	 *    partition()
	 *     may be combined with PHP array destructuring
	 *     to separate elements that pass a given truth test
	 *     from those that do not
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6]);
			[$under_three, $equal_or_above_three]=$collection->partition(function(int $i){
				return ($i < 3);
			});
			$under_three->all(); // [1, 2]
			$equal_or_above_three->all(); // [3, 4, 5, 6]
	 *     warning:
	 *      operator_for_where method is required
	 *      value_retriever method is required
	 *    percentage()
	 *     may be used to quickly determine the percentage of items
	 *     in the collection that pass a given truth test
			$collection=lv_arr_collect([1, 1, 2, 2, 2, 3]);
			$percentage=$collection->percentage(function($value){
				return ($value === 1);
			}); // 33.33
	 *     by default, the percentage will be rounded to two decimal places
	 *     however, you may customize this behavior
	 *     by providing a second argument to the method
			$percentage=$collection->percentage(function($value){
				return ($value === 1);
			}, 3); // 33.333
	 *     warning:
	 *      count method is required
	 *      filter method is required
	 *      is_empty method is required
	 *    pipe()
	 *     passes the collection to the given closure
	 *     and returns the result of the executed closure
			$collection=lv_arr_collect([1, 2, 3]);
			$piped=$collection->pipe(function(lv_arr_collection $collection){
				return $collection->sum();
			}); // 6
	 *    pipe_into()
	 *     creates a new instance of the given class and passes
	 *     the collection into the constructor
			class resource_collection
			{
				public function __construct(lv_arr_collection $collection) {}
			}
			$collection=lv_arr_collect([1, 2, 3]);
			$resource=$collection->pipe_into(resource_collection::class);
			$resource->collection->all(); // [1, 2, 3]
	 *    pipe_through()
	 *     passes the collection to the given array of closures
	 *     and returns the result of the executed closures
			$collection=lv_arr_collect([1, 2, 3]);
			$result=$collection->pipe_through([
				function(lv_arr_collection $collection)
				{
					return $collection->merge([4, 5]);
				},
				function(lv_arr_collection $collection)
				{
					return $collection->sum();
				}
			]); // 15
	 *     warning:
	 *      make method is required
	 *      reduce method is required
	 *    pluck()
	 *     retrieves all of the values for a given key
			$collection=lv_arr_collect([
				['product_id'=>'prod-100', 'name'=>'Desk'],
				['product_id'=>'prod-200', 'name'=>'Chair']
			]);
			$plucked=$collection->pluck('name');
			$plucked->all(); // ['Desk', 'Chair']
	 *     you may also specify how you wish the resulting collection to be keyed
			$plucked=$collection->pluck('name', 'product_id');
			$plucked->all();
			// ['prod-100'=>'Desk', 'prod-200'=>'Chair']
	 *     the pluck method also supports retrieving nested values using "dot" notation
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
			$plucked->all(); // [['Rosa', 'Judith'], ['Abigail', 'Joey']]
	 *     if duplicate keys exist, the last matching element
	 *     will be inserted into the plucked collection
			$collection=lv_arr_collect([
				['brand'=>'Tesla', 'color'=>'red'],
				['brand'=>'Pagani', 'color'=>'white'],
				['brand'=>'Tesla', 'color'=>'black'],
				['brand'=>'Pagani', 'color'=>'orange']
			]);
			$plucked=$collection->pluck('color', 'brand');
			$plucked->all();
			// ['Tesla'=>'black', 'Pagani'=>'orange']
	 *     warning:
	 *      lv_arr_pluck function is required
	 *    pop()
	 *     removes and returns the last item from the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->pop(); // 5
			$collection->all(); // [1, 2, 3, 4]
	 *     you may pass an integer to the pop method
	 *     to remove and return multiple items from the end of a collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->pop(3); // lv_arr_collect([5, 4, 3])
			$collection->all(); // [1, 2]
	 *     warning:
	 *      count method is required
	 *      is_empty method is required
	 *    prepend()
	 *     adds an item to the beginning of the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->prepend(0);
			$collection->all(); // [0, 1, 2, 3, 4, 5]
	 *     you may also pass a second argument
	 *     to specify the key of the prepended item
			$collection=lv_arr_collect(['one'=>1, 'two'=>2]);
			$collection->prepend(0, 'zero');
			$collection->all();
			// ['zero'=>0, 'one'=>1, 'two'=>2]
	 *     warning:
	 *      lv_arr_prepend function is required
	 *    pull()
	 *     removes and returns an item from the collection by its key
			$collection=lv_arr_collect(['product_id'=>'prod-100', 'name'=>'Desk']);
			$collection->pull('name'); // 'Desk'
			$collection->all(); // ['product_id'=>'prod-100']
	 *     warning:
	 *      lv_arr_pull function is required
	 *    push()
	 *     appends an item to the end of the collection
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$collection->push(5);
			$collection->all(); // [1, 2, 3, 4, 5]
	 *    put()
	 *     sets the given key and value in the collection
			$collection=lv_arr_collect(['product_id'=>1, 'name'=>'Desk']);
			$collection->put('price', 100);
			$collection->all();
			// ['product_id'=>1, 'name'=>'Desk', 'price'=>100]
	 *     warning:
	 *      offsetSet method is required
	 *    random()
	 *     returns a random item from the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->random(); // 4 - (retrieved randomly)
	 *     you may pass an integer to random to specify how many items
	 *     you would like to randomly retrieve
	 *     a collection of items is always returned
	 *     when explicitly passing the number of items you wish to receive
			$random=$collection->random(3);
			$random->all();
			// [2, 4, 5] - (retrieved randomly)
	 *     if the collection instance has fewer items than requested
	 *     the random method will throw an lv_arr_exception
	 *     the random method also accepts a closure
	 *     which will receive the current lv_arr_collection instance
			$random=$collection->random(function(lv_arr_collection $items){
				return min(10, count($items->all()));
			});
			$random->all();
			// [1, 2, 3, 4, 5] - (retrieved randomly)
	 *     warning:
	 *      lv_arr_random function is required
	 *    [static] range()
	 *     returns a collection containing integers
	 *     between the specified range
			$collection=lv_arr_collect()->range(3, 6);
			$collection->all(); // [3, 4, 5, 6]
	 *    reduce()
	 *     reduces the collection to a single value
	 *     passing the result of each iteration into the subsequent iteration
			$collection=lv_arr_collect([1, 2, 3]);
			$total=$collection->reduce(function(?int $carry, int $item){
				return ($carry+$item);
			}); // 6
	 *     the value for $carry on the first iteration is null
	 *     however, you may specify its initial value
	 *     by passing a second argument to reduce
			$collection->reduce(function(?int $carry, int $item){
				return ($carry+$item);
			}, 4); // 10
	 *     the reduce method also passes array keys
	 *     in associative collections to the given callback
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
			$collection->reduce(function(?int $carry, int $value, $key) use($ratio){
				return ($carry+($value*$ratio[$key]));
			}); // 4264
	 *    reduce_spread()
	 *     reduces the collection to an array of values
	 *     passing the results of each iteration into the subsequent iteration
	 *     this method is similar to the reduce method
	 *     however, it can accept multiple initial values
			[$credits_remaining, $batch]=Image::where('status', 'unprocessed')
				->get()
				->reduce_spread(function(int $credits_remaining, lv_arr_collection $batch, Image $image){
					if($credits_remaining >= $image->credits_required())
					{
						$batch->push($image);
						$credits_remaining-=$image->credits_required();
					}

					return [$credits_remaining, $batch];
				}, $credits_available, lv_arr_collect());
	 *     warning:
	 *      this method was not tested
	 *    reject()
	 *     filters the collection using the given closure
	 *     the closure should return true if the item
	 *     should be removed from the resulting collection
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$filtered=$collection->reject(function(int $value, int $key){
				return ($value > 2);
			});
			$filtered->all(); // [1, 2]
	 *     for the inverse of the reject method, see the filter method
	 *     warning:
	 *      filter method is required
	 *      use_as_callable method is required
	 *    replace()
	 *     the replace method behaves similarly to merge
	 *     however, in addition to overwriting matching items
	 *     that have string keys, the replace method
	 *     will also overwrite items in the collection
	 *     that have matching numeric keys
			$collection=lv_arr_collect(['Taylor', 'Abigail', 'James']);
			$replaced=$collection->replace([1=>'Victoria', 3=>'Finn']);
			$replaced->all();
			// ['Taylor', 'Victoria', 'James', 'Finn']
	 *     warning:
	 *      get_arrayable_items method is required
	 *    replace_recursive()
	 *     this method works like replace, but it will recur into arrays
	 *     and apply the same replacement process to the inner values
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
			$replaced->all();
			// ['Charlie', 'Abigail', ['James', 'King', 'Finn']]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    reverse()
	 *     reverses the order of the collection's items
	 *     preserving the original keys
			$collection=lv_arr_collect(['a', 'b', 'c', 'd', 'e']);
			$reversed=$collection->reverse();
			$reversed->all();
			// [
			//  4=>'e',
			//  3=>'d',
			//  2=>'c',
			//  1=>'b',
			//  0=>'a'
			// ]
	 *    search()
	 *     searches the collection for the given value
	 *     and returns its key if found
	 *     if the item is not found, false is returned
			$collection=lv_arr_collect([2, 4, 6, 8]);
			$collection->search(4); // 1
	 *     the search is done using a "loose" comparison
	 *     meaning a string with an integer value will be
	 *     considered equal to an integer of the same value
	 *     to use "strict" comparison
	 *     pass true as the second argument to the method
			lv_arr_collect([2, 4, 6, 8])->search('4', true);
			// false
	 *     alternatively, you may provide your own closure to search
	 *     for the first item that passes a given truth test
			lv_arr_collect([2, 4, 6, 8])->search(function(int $item, int $key){
				return ($item > 5);
			}); // 2
	 *     warning:
	 *      use_as_callable method is required
	 *    select()
	 *     selects the given keys from the collection
	 *     similar to an SQL SELECT statement
			$users=lv_arr_collect([
				['name'=>'Taylor Otwell', 'role'=>'Developer', 'status'=>'active'],
				['name'=>'Victoria Faith', 'role'=>'Researcher', 'status'=>'active']
			]);
			$users->select(['name', 'role']);
			// [
			//  ['name'=>'Taylor Otwell', 'role'=>'Developer'],
			//  ['name'=>'Victoria Faith', 'role'=>'Researcher']
			// ]
	 *     warning:
	 *      lv_arr_select function is required
	 *    shift()
	 *     removes and returns the first item from the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->shift(); // 1
			$collection->all(); // [2, 3, 4, 5]
	 *     you may pass an integer to the shift method to remove
	 *     and return multiple items from the beginning of a collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->shift(3); // lv_arr_collect([1, 2, 3])
			$collection->all(); // [4, 5]
	 *     warning:
	 *      count method is required
	 *      is_empty method is required
	 *    shuffle()
	 *     randomly shuffles the items in the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$shuffled=$collection->shuffle();
			$shuffled->all();
			// [3, 2, 5, 1, 4] - (generated randomly)
	 *     warning:
	 *      lv_arr_shuffle function is required
	 *    skip()
	 *     returns a new collection, with the given number of elements
	 *     removed from the beginning of the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
			$collection=$collection->skip(4);
			$collection->all(); // [5, 6, 7, 8, 9, 10]
	 *     warning:
	 *      slice method is required
	 *    skip_until()
	 *     skips over items from the collection
	 *     until the given callback returns true
	 *     and then returns the remaining items in the collection
	 *     as a new collection instance
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$subset=$collection->skip_until(function(int $item){
				return ($item >= 3);
			});
			$subset->all(); // [3, 4]
	 *     you may also pass a simple value to the skip_until method
	 *     to skip all items until the given value is found
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$subset=$collection->skip_until(3);
			$subset->all(); // [3, 4]
	 *     warning:
	 *      if the given value is not found
	 *       or the callback never returns true
	 *       the skip_until method will return an empty collection
	 *      lazy method is required
	 *    skip_while()
	 *     skips over items from the collection
	 *     while the given callback returns true
	 *     and then returns the remaining items in the collection
	 *     as a new collection
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$subset=$collection->skip_while(function(int $item){
				return ($item <= 3);
			});
			$subset->all(); // [4]
	 *     warning:
	 *      if the callback never returns false
	 *       the skip_while method will return an empty collection
	 *      lazy method is required
	 *    slice()
	 *     returns a slice of the collection starting at the given index
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
			$slice=$collection->slice(4);
			$slice->all(); // [5, 6, 7, 8, 9, 10]
	 *     if you would like to limit the size of the returned slice
	 *     pass the desired size as the second argument to the method
			$slice=$collection->slice(4, 2);
			$slice->all(); // [5, 6]
	 *     the returned slice will preserve keys by default
	 *     if you do not wish to preserve the original keys
	 *     you can use the values method to reindex them
	 *    sliding()
	 *     returns a new collection of chunks
	 *     representing a "sliding window" view of the items in the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunks=$collection->sliding(2);
			$chunks->to_array();
			// [[1, 2], [2, 3], [3, 4], [4, 5]]
	 *     this is especially useful in conjunction with the each_spread method
			$transactions->sliding(2)->each_spread(function(lv_arr_collection $previous, lv_arr_collection $current){
				$current->total=$previous->total+$current->amount;
			});
	 *     you may optionally pass a second "step" value
	 *     which determines the distance between the first item of every chunk
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunks=$collection->sliding(3, 2);
			$chunks->to_array();
			// [[1, 2, 3], [3, 4, 5]]
	 *     warning:
	 *      count method is required
	 *      slice method is required
	 *      times method is required
	 *    sole()
	 *     returns the first element in the collection
	 *     that passes a given truth test
	 *     but only if the truth test matches exactly one element
			lv_arr_collect([1, 2, 3, 4])->sole(function(int $value, int $key){
				return ($value === 2);
			}); // 2
	 *     you may also pass a key/value pair to the sole method
	 *     which will return the first element in the collection
	 *     that matches the given pair, but only if it exactly one element matches
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100]
			]);
			$collection->sole('product', 'Chair');
			// ['product'=>'Chair', 'price'=>100]
	 *     alternatively, you may also call the sole method with no argument
	 *     to get the first element in the collection if there is only one element
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200]
			]);
			$collection->sole();
			// ['product'=>'Desk', 'price'=>200]
	 *     if there are no elements in the collection
	 *     that should be returned by the sole method
	 *     an lv_arr_exception exception will be thrown
	 *     if there is more than one element that should be returned
	 *     an lv_arr_exception will be thrown
	 *     warning:
	 *      count method is required
	 *      filter method is required
	 *      first method is required
	 *      unless method is required
	 *      operator_for_where method is required
	 *    some()
	 *     alias for the contains method
	 *     warning:
	 *      contains method is required
	 *    sort()
	 *     sorts the collection
	 *     the sorted collection keeps the original array keys
	 *     so in the following example we will use the values method
	 *     to reset the keys to consecutively numbered indexes
			$collection=lv_arr_collect([5, 3, 1, 2, 4]);
			$sorted=$collection->sort();
			$sorted->values()->all();
			// [1, 2, 3, 4, 5]
	 *     if your sorting needs are more advanced
	 *     you may pass a callback to sort with your own algorithm
	 *     refer to the PHP documentation on uasort
	 *     which is what the collection's sort method calls utilizes internally
	 *     if you need to sort a collection of nested arrays or objects,
	 *     see the sort_by and sort_by_desc methods
	 *    sort_by()
	 *     sorts the collection by the given key
	 *     the sorted collection keeps the original array keys
	 *     so in the following example we will use the values method
	 *     to reset the keys to consecutively numbered indexes
			$collection=lv_arr_collect([
				['name'=> 'Desk', 'price'=>200],
				['name'=> 'Chair', 'price'=>100],
				['name'=> 'Bookcase', 'price'=>150]
			]);
			$sorted=$collection->sort_by('price');
			$sorted->values()->all();
			// [
			//  ['name'=>'Chair', 'price'=>100],
			//  ['name'=>'Bookcase', 'price'=>150],
			//  ['name'=>'Desk', 'price'=>200]
			// ]
	 *     the sort_by method accepts sort flags as its second argument
			$collection=lv_arr_collect([
				['title'=>'Item 1'],
				['title'=> 'Item 12'],
				['title'=>'Item 3']
			]);
			$sorted=$collection->sort_by('title', SORT_NATURAL);
			$sorted->values()->all();
			// [
			//  ['title'=>'Item 1'],
			//  ['title'=>'Item 3'],
			//  ['title'=>'Item 12']
			// ]
	 *     alternatively, you may pass your own closure
	 *     to determine how to sort the collection's values
			$collection=lv_arr_collect([
				['name'=>'Desk', 'colors'=>['Black', 'Mahogany']],
				['name'=>'Chair', 'colors'=>['Black']],
				['name'=>'Bookcase', 'colors'=>['Red', 'Beige', 'Brown']]
			]);
			$sorted=$collection->sort_by(function(array $product, int $key){
				return count($product['colors']);
			});
			$sorted->values()->all();
			// [
			//  ['name'=>'Chair', 'colors'=>['Black']],
			//  ['name'=>'Desk', 'colors'=>['Black', 'Mahogany']],
			//  ['name'=>'Bookcase', 'colors'=>['Red', 'Beige', 'Brown']],
			// ]
	 *     if you would like to sort your collection by multiple attributes
	 *     you may pass an array of sort operations to the sort_by method
	 *     each sort operation should be an array consisting of the attribute
	 *     that you wish to sort by and the direction of the desired sort
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
			$sorted->values()->all();
			// [
			//  ['name'=>'Abigail Otwell', 'age'=>32],
			//  ['name'=>'Abigail Otwell', 'age'=>30],
			//  ['name'=>'Taylor Otwell', 'age'=>36],
			//  ['name'=>'Taylor Otwell', 'age'=>34]
			// ]
	 *     when sorting a collection by multiple attributes,
	 *     you may also provide closures that define each sort operation
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
			$sorted->values()->all();
			// [
			//  ['name'=>'Abigail Otwell', 'age'=>32],
			//  ['name'=>'Abigail Otwell', 'age'=>30],
			//  ['name'=>'Taylor Otwell', 'age'=>36],
			//  ['name'=>'Taylor Otwell', 'age'=>34]
			// ]
	 *     warning:
	 *      sort_by_many method is required
	 *      value_retriever method is required
	 *    sort_by_desc()
	 *     this method has the same signature as the sort_by method
	 *     but will sort the collection in the opposite order
	 *     warning:
	 *      sort_by method is required
	 *    sort_desc()
	 *     sorts the collection in the opposite order as the sort method
			$collection=lv_arr_collect([5, 3, 1, 2, 4]);
			$sorted=$collection->sort_desc();
			$sorted->values()->all();
			// [5, 4, 3, 2, 1]
	 *     unlike sort, you may not pass a closure to sort_desc
	 *     instead, you should use the sort method and invert your comparison
	 *    sort_keys()
	 *     sorts the collection by the keys
	 *     of the underlying associative array
			$collection=lv_arr_collect([
				'id'=>22345,
				'first'=>'John',
				'last'=>'Doe'
			]);
			$sorted=$collection->sort_keys();
			$sorted->all();
			// [
			//  'first'=>'John',
			//  'id'=>22345,
			//  'last'=>'Doe'
			// ]
	 *    sort_keys_desc()
	 *     this method has the same signature as the sortKeys method
	 *     but will sort the collection in the opposite order
	 *     warning:
	 *      sort_keys method is required
	 *    sort_keys_using()
	 *     sorts the collection by the keys
	 *     of the underlying associative array using a callback
			$collection=lv_arr_collect([
				'ID'=>22345,
				'first'=>'John',
				'last'=>'Doe'
			]);
			$sorted=$collection->sort_keys_using('strnatcasecmp');
			$sorted->all();
			// [
			//  'first'=>'John',
			//  'ID'=>22345,
			//  'last'=>'Doe'
			// ]
	 *     the callback must be a comparison function
	 *     that returns an integer less than, equal to, or greater than zero
	 *     for more information, refer to the PHP documentation on uksort
	 *     which is the PHP function that sort_keys_using method utilizes internally
	 *    splice()
	 *     removes and returns a slice of items
	 *     starting at the specified index
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunk=$collection->splice(2);
			$chunk->all(); // [3, 4, 5]
			$collection->all(); // [1, 2]
	 *     you may pass a second argument to limit the size
	 *     of the resulting collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunk=$collection->splice(2, 1);
			$chunk->all(); // [3]
			$collection->all(); // [1, 2, 4, 5]
	 *     in addition, you may pass a third argument
	 *     containing the new items to replace the items removed
	 *     from the collection
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$chunk=$collection->splice(2, 1, [10, 11]);
			$chunk->all(); // [3]
			$collection->all(); // [1, 2, 10, 11, 4, 5]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    split()
	 *     breaks a collection into the given number of groups
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$groups=$collection->split(3);
			$groups->all();
			// [[1, 2], [3, 4], [5]]
	 *     warning:
	 *      count method is required
	 *      is_empty method is required
	 *      push method is required
	 *    split_in()
	 *     breaks a collection into the given number of groups
	 *     filling non-terminal groups completely before allocating
	 *     the remainder to the final group
			$collection=lv_arr_collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
			$groups=$collection->split_in(3);
			$groups->all();
			// [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10]]
	 *     warning:
	 *      chunk method is required
	 *      count method is required
	 *    sum()
	 *     returns the sum of all items in the collection
			lv_arr_collect([1, 2, 3, 4, 5])->sum(); // 15
	 *     if the collection contains nested arrays or objects
	 *     you should pass a key that will be used
	 *     to determine which values to sum
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
			$collection->sum('pages'); // 1272
	 *     in addition, you may pass your own closure to determine
	 *     which values of the collection to sum
			$collection=lv_arr_collect([
				['name'=>'Chair', 'colors'=>['Black']],
				['name'=>'Desk', 'colors'=>['Black', 'Mahogany']],
				['name'=>'Bookcase', 'colors'=>['Red', 'Beige', 'Brown']]
			]);
			$collection->sum(function(array $product){
				return count($product['colors']);
			}); // 6
	 *     warning:
	 *      identity method is required
	 *      reduce method is required
	 *      value_retriever method is required
	 *    take()
	 *     returns a new collection with the specified number of items
			$collection=lv_arr_collect([0, 1, 2, 3, 4, 5]);
			$chunk=$collection->take(3);
			$chunk->all(); // [0, 1, 2]
	 *     you may also pass a negative integer to take
	 *     the specified number of items from the end of the collection
			$collection=lv_arr_collect([0, 1, 2, 3, 4, 5]);
			$chunk=$collection->take(-2);
			$chunk->all(); [4, 5]
	 *     warning:
	 *      slice method is required
	 *    take_until()
	 *     returns items in the collection
	 *     until the given callback returns true
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$subset=$collection->take_until(function(int $item){
				return ($item >= 3);
			});
			$subset->all(); // [1, 2]
	 *     you may also pass a simple value to the take_until method
	 *     to get the items until the given value is found
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$subset=$collection->take_until(3);
			$subset->all(); // [1, 2]
	 *     warning:
	 *      if the given value is not found or the callback never returns true
	 *       the take_until method will return all items in the collection
	 *      lazy method is required
	 *    take_while()
	 *     returns items in the collection
	 *     until the given callback returns false
			$collection=lv_arr_collect([1, 2, 3, 4]);
			$subset=$collection->take_while(function(int $item){
				return ($item < 3);
			});
			$subset->all(); // [1, 2]
	 *     warning:
	 *      if the callback never returns false
	 *       the take_while method will return all items in the collection
	 *      lazy method is required
	 *    tap()
	 *     passes the collection to the given callback
	 *     allowing you to "tap" into the collection at a specific point
	 *     and do something with the items while not affecting the collection itself
	 *     the collection is then returned by the tap method
			lv_arr_collect([2, 4, 3, 1, 5])
				->sort()
				->tap(function(lv_arr_collection $collection){
					log::debug('Values after sorting', $collection->values()->all());
				})
				->shift();
			// 1
	 *    [static] times()
	 *     creates a new collection by invoking the given closure
	 *     a specified number of times
			$collection=lv_arr_collection::times(10, function(int $number){
				return ($number*9);
			});
			$collection->all();
			// [9, 18, 27, 36, 45, 54, 63, 72, 81, 90]
	 *     warning:
	 *      map method is required
	 *      range method is required
	 *      unless method is required
	 *    to_array()
	 *     converts the collection into a plain PHP array
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>200]);
			$collection->to_array();
			// [['name'=>'Desk', 'price'=>200]]
	 *     warning:
	 *      all method is required
	 *      map method is required
	 *    to_json()
	 *     converts the collection into a JSON serialized string
			$collection=lv_arr_collect(['name'=>'Desk', 'price'=>200]);
			$collection->to_json();
			// '{"name":"Desk", "price":200}'
	 *     warning:
	 *      jsonSerialize method is required
	 *    transform()
	 *     iterates over the collection and calls the given callback
	 *     with each item in the collection
	 *     the items in the collection will be replaced by the values
	 *     returned by the callback
			$collection=lv_arr_collect([1, 2, 3, 4, 5]);
			$collection->transform(function(int $item, int $key){
				return ($item*2);
			});
			$collection->all(); // [2, 4, 6, 8, 10]
	 *     warning:
	 *      unlike most other collection methods
	 *       transform modifies the collection itself
	 *       if you wish to create a new collection instead
	 *       use the map method
	 *      all method is required
	 *      map method is required
	 *    undot()
	 *     expands a single-dimensional collection that uses
	 *     "dot" notation into a multi-dimensional collection
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
			$person->to_array();
			// [
			//  'name'=>[
			//   'first_name'=>'Marie',
			//   'last_name'=>'Valentine'
			//  ],
			//  'address'=>[
			//   'line_1'=>'2992 Eagle Drive',
			//   'line_2'=>'',
			//   'suburb'=>'Detroit',
			//   'state'=>'MI',
			//   'postcode'=>'48219'
			//  ]
			// ]
	 *     warning:
	 *      lv_arr_undot function is required
	 *    union()
	 *     adds the given array to the collection
	 *     if the given array contains keys that are already
	 *     in the original collection, the original
	 *     collection's values will be preferred
			$collection=lv_arr_collect([1=>['a'], 2=>['b']]);
			$union=$collection->union([3=>['c'], 1=>['d']]);
			$union->all();
			// [1=>['a'], 2=>['b'], 3=>['c']]
	 *     warning:
	 *      get_arrayable_items method is required
	 *    unique()
	 *     returns all of the unique items in the collection
	 *     the returned collection keeps the original array keys
	 *     so in the following example we will use the values method
	 *     to reset the keys to consecutively numbered indexes
			$collection=lv_arr_collect([1, 1, 2, 2, 3, 4, 2]);
			$unique=$collection->unique();
			$unique->values()->all();
			// [1, 2, 3, 4]
	 *     when dealing with nested arrays or objects
	 *     you may specify the key used to determine uniqueness
			$collection=lv_arr_collect([
				['name'=>'iPhone 6', 'brand'=>'Apple', 'type'=>'phone'],
				['name'=>'iPhone 5', 'brand'=>'Apple', 'type'=>'phone'],
				['name'=>'Apple Watch', 'brand'=>'Apple', 'type'=>'watch'],
				['name'=>'Galaxy S6', 'brand'=>'Samsung', 'type'=>'phone'],
				['name'=>'Galaxy Gear', 'brand'=>'Samsung', 'type'=>'watch']
			]);
			$unique=$collection->unique('brand');
			$unique->values()->all();
			// [
			//  ['name'=>'iPhone 6', 'brand'=>'Apple', 'type'=>'phone'],
			//  ['name'=>'Galaxy S6', 'brand'=>'Samsung', 'type'=>'phone']
			// ]
	 *     finally, you may also pass your own closure to the unique method
	 *     to specify which value should determine an item's uniqueness
			$unique=$collection->unique(function(array $item){
				return $item['brand'].$item['type'];
			});
			$unique->values()->all();
			// [
			//  ['name'=>'iPhone 6', 'brand'=>'Apple', 'type'=>'phone'],
			//  ['name'=>'Apple Watch', 'brand'=>'Apple', 'type'=>'watch'],
			//  ['name'=>'Galaxy S6', 'brand'=>'Samsung', 'type'=>'phone'],
			//  ['name'=>'Galaxy Gear', 'brand'=>'Samsung', 'type'=>'watch']
			// ]
	 *     the unique method uses "loose" comparisons when checking item values
	 *     meaning a string with an integer value will be considered
	 *     equal to an integer of the same value
	 *     use the unique_strict method to filter using "strict" comparisons
	 *     warning:
	 *      reject method is required
	 *      value_retriever method is required
	 *    unique_strict()
	 *     this method has the same signature as the unique method
	 *     however, all values are compared using "strict" comparisons
	 *     warning:
	 *      unique method is required
	 *    unless()
	 *     executes the given callback unless the first argument
	 *     given to the method evaluates to true
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->unless(true, function(lv_arr_collection $collection){
				return $collection->push(4);
			});
			$collection->unless(false, function(lv_arr_collection $collection){
				return $collection->push(5);
			});
			$collection->all(); // [1, 2, 3, 5]
	 *     a second callback may be passed to the unless method
	 *     the second callback will be executed when
	 *     the first argument given to the unless method evaluates to true
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->unless(true, function(lv_arr_collection $collection){
				return $collection->push(4);
			}, function(lv_arr_collection $collection){
				return $collection->push(5);
			});
			$collection->all(); // [1, 2, 3, 5]
	 *     warning:
	 *      higher_order_when_proxy method is required
	 *    unless_empty()
	 *     alias for the when_not_empty method
	 *     warning:
	 *      when_not_empty method is required
	 *    unless_not_empty()
	 *     alias for the when_empty method
	 *     warning:
	 *      when_empty method is required
	 *    [static] unwrap()
	 *     returns the collection's underlying items
	 *     from the given value when applicable
			lv_arr_collection::unwrap(lv_arr_collect('John Doe')); // ['John Doe']
			lv_arr_collection::unwrap(['John Doe']); // ['John Doe']
			lv_arr_collection::unwrap('John Doe'); // 'John Doe'
	 *     warning:
	 *      all method is required
	 *    value()
	 *     retrieves a given value from the first element of the collection
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Speaker', 'price'=>400]
			]);
			$value=$collection->value('price'); // 200
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      lv_arr_value function is required
	 *      first_where method is required
	 *    values()
	 *     returns a new collection
	 *     with the keys reset to consecutive integers
			$collection=lv_arr_collect([
				10=>['product'=>'Desk', 'price'=>200],
				11=>['product'=>'Desk', 'price'=>200]
			]);
			$values=$collection->values();
			$values->all();
			// [
			//  0=>['product'=>'Desk', 'price'=>200],
			//  1=>['product'=>'Desk', 'price'=>200]
			// ]
	 *    when()
	 *     executes the given callback when the first argument
	 *     given to the method evaluates to true
	 *     the collection instance and the first argument
	 *     given to the when method will be provided to the closure
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->when(true, function(lv_arr_collection $collection, int $value){
				return $collection->push(4);
			});
			$collection->when(false, function(lv_arr_collection $collection, int $value){
				return $collection->push(5);
			});
			$collection->all(); // [1, 2, 3, 4]
	 *     a second callback may be passed to the when method
	 *     the second callback will be executed when the first argument
	 *     given to the when method evaluates to false
			$collection=lv_arr_collect([1, 2, 3]);
			$collection->when(false, function(lv_arr_collection $collection, int $value){
				return $collection->push(4);
			}, function(lv_arr_collection $collection){
				return $collection->push(5);
			});
			$collection->all(); // [1, 2, 3, 5]
	 *     for the inverse of when, see the unless method
	 *     warning:
	 *      higher_order_when_proxy method is required
	 *    when_empty()
	 *     executes the given callback when the collection is empty
			$collection=lv_arr_collect(['Michael', 'Tom']);
			$collection->when_empty(function(lv_arr_collection $collection){
				return $collection->push('Adam');
			});
			$collection->all(); // ['Michael', 'Tom']
			$collection=lv_arr_collect();
			$collection->when_empty(function(lv_arr_collection $collection){
				return $collection->push('Adam');
			});
			$collection->all(); // ['Adam']
	 *     a second closure may be passed to the when_empty method
	 *     that will be executed when the collection is not empty
			$collection=lv_arr_collect(['Michael', 'Tom']);
			$collection->when_empty(function(lv_arr_collection $collection){
				return $collection->push('Adam');
			}, function(lv_arr_collection $collection){
				return $collection->push('Taylor');
			});
			$collection->all(); // ['Michael', 'Tom', 'Taylor']
	 *     for the inverse of when_empty, see the when_not_empty method
	 *     warning:
	 *      is_empty method is required
	 *      when method is required
	 *    when_not_empty()
	 *     executes the given callback when the collection is not empty
			$collection=lv_arr_collect(['michael', 'tom']);
			$collection->when_not_empty(function(lv_arr_collection $collection){
				return $collection->push('adam');
			});
			$collection->all(); // ['michael', 'tom', 'adam']
			$collection=lv_arr_collect();
			$collection->when_not_empty(function(lv_arr_collection $collection){
				return $collection->push('adam');
			});
			$collection->all(); // []
	 *     a second closure may be passed to the when_not_empty method
	 *     that will be executed when the collection is empty
			$collection=lv_arr_collect();
			$collection->when_not_empty(function(lv_arr_collection $collection){
				return $collection->push('adam');
			}, function(lv_arr_collection $collection){
				return $collection->push('taylor');
			});
			$collection->all(); // ['taylor']
	 *     for the inverse of when_not_empty, see the when_empty method
	 *     warning:
	 *      is_not_empty method is required
	 *      when method is required
	 *    where()
	 *     filters the collection by a given key/value pair
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where('price', 100);
			$filtered->all();
			// [
			//  ['product'=>'Chair', 'price'=>100],
			//  ['product'=>'Door', 'price'=>100]
			// ]
	 *     the where method uses "loose" comparisons when checking item values
	 *     meaning a string with an integer value will be considered
	 *     equal to an integer of the same value
	 *     use the where_strict method to filter using "strict" comparisons
	 *     optionally, you may pass a comparison operator as the second parameter
	 *     supported operators are: '===', '!==', '!=', '==', '=',
	 *     '<>', '>', '<', '>=', and '<='
			$collection=lv_arr_collect([
				['name'=>'Jim', 'deleted_at'=>'2019-01-01 00:00:00'],
				['name'=>'Sally', 'deleted_at'=>'2019-01-02 00:00:00'],
				['name'=>'Sue', 'deleted_at'=>null]
			]);
			$filtered=$collection->where('deleted_at', '!=', null);
			$filtered->all();
			// [
			//  ['name'=>'Jim', 'deleted_at'=>'2019-01-01 00:00:00'],
			//  ['name'=>'Sally', 'deleted_at'=>'2019-01-02 00:00:00']
			// ]
	 *     warning:
	 *      filter method is required
	 *      operator_for_where method is required
	 *    where_strict()
	 *     this method has the same signature as the where method
	 *     however, all values are compared using "strict" comparisons
	 *     warning:
	 *      where method is required
	 *    where_between()
	 *     filters the collection by determining
	 *     if a specified item value is within a given range
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>80],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Pencil', 'price'=>30],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_between('price', [100, 200]);
			$filtered->all();
			// [
			//  ['product'=>'Desk', 'price'=>200],
			//  ['product'=>'Bookcase', 'price'=>150],
			//  ['product'=>'Door', 'price'=>100]
			// ]
	 *     warning:
	 *      where method is required
	 *    where_in()
	 *     removes elements from the collection
	 *     that do not have a specified item value
	 *     that is contained within the given array
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_in('price', [150, 200]);
			$filtered->all();
			// [
			//  ['product'=>'Desk', 'price'=>200],
			//  ['product'=>'Bookcase', 'price'=>150]
			// ]
	 *     the where_in method uses "loose" comparisons when checking item values
	 *     meaning a string with an integer value will be considered
	 *     equal to an integer of the same value
	 *     use the where_in_strict method to filter using "strict" comparisons
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      filter method is required
	 *      get_arrayable_items method is required
	 *    where_in_strict()
	 *     this method has the same signature as the where_in method
	 *     however, all values are compared using "strict" comparisons
	 *     warning:
	 *      where_in method is required
	 *    where_instance_of()
	 *     filters the collection by a given class type
			use app\user_model;
			use app\post_model;
			$collection=lv_arr_collect([
				new user_model(),
				new user_model(),
				new post_model()
			]);
			$filtered=$collection->where_instance_of(user_model::class);
			$filtered->all();
			// [app\user_model, app\user_model]
	 *     warning:
	 *      filter method is required
	 *    where_not_between()
	 *     filters the collection by determining
	 *     if a specified item value is outside of a given range
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>80],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Pencil', 'price'=>30],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_not_between('price', [100, 200]);
			$filtered->all();
			// [
			//  ['product'=>'Chair', 'price'=>80],
			//  ['product'=>'Pencil', 'price'=>30]
			// ]
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      filter method is required
	 *    where_not_in()
	 *     removes elements from the collection that have a specified
	 *     item value that is contained within the given array
			$collection=lv_arr_collect([
				['product'=>'Desk', 'price'=>200],
				['product'=>'Chair', 'price'=>100],
				['product'=>'Bookcase', 'price'=>150],
				['product'=>'Door', 'price'=>100]
			]);
			$filtered=$collection->where_not_in('price', [150, 200]);
			$filtered->all();
			// [
			//  ['product'=>'Chair', 'price'=>100],
			//  ['product'=>'Door', 'price'=>100]
			// ]
	 *     the where_not_in method uses "loose" comparisons when checking item values
	 *     meaning a string with an integer value will be considered
	 *     equal to an integer of the same value
	 *     use the where_not_in_strict method to filter using "strict" comparisons
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      get_arrayable_items method is required
	 *      reject method is required
	 *    where_not_in_strict()
	 *     this method has the same signature as the where_not_in method
	 *     however, all values are compared using "strict" comparisons
	 *     warning:
	 *      where_not_in method is required
	 *    where_not_null()
	 *     returns items from the collection where the given key is not null
			$collection=lv_arr_collect([
				['name'=>'Desk'],
				['name'=>null],
				['name'=>'Bookcase']
			]);
			$filtered=$collection->where_not_null('name');
			$filtered->all();
			// [
			//  ['name'=>'Desk'],
			//  ['name'=>'Bookcase']
			// ]
	 *     warning:
	 *      where method is required
	 *    where_null()
	 *     returns items from the collection where the given key is null
			$collection=lv_arr_collect([
				['name'=>'Desk'],
				['name'=>null],
				['name'=>'Bookcase']
			]);
			$filtered=$collection->where_null('name');
			$filtered->all();
			// [['name'=>null]]
	 *     warning:
	 *      where_strict method is required
	 *    [static] wrap()
	 *     wraps the given value in a collection when applicable
			$collection=lv_arr_collection::wrap('John Doe');
			$collection->all(); // ['John Doe']
			$collection=lv_arr_collection::wrap(['John Doe']);
			$collection->all(); // ['John Doe']
			$collection=lv_arr_collection::wrap(lv_arr_collect('John Doe'));
			$collection->all(); // ['John Doe']
	 *     warning:
	 *      lv_arr_wrap function is required
	 *    zip()
	 *     merges together the values of the given array with the values
	 *     of the original collection at their corresponding index
			$collection=lv_arr_collect(['Chair', 'Desk']);
			$zipped=$collection->zip([100, 200]);
			$zipped->all();
			// [['Chair', 100], ['Desk', 200]]
	 *     warning:
	 *      get_arrayable_items method is required
	 *   methods implemented in the lv_hlp component:
	 *    dd()
	 *    dump()
	 *    ensure()
	 *    macro()
	 *  lv_arr_lazy_collection
	 *   implemented methods (for more info, see lv_arr_collection above):
	 *    all()
	 *     warning:
	 *      get_iterator method is required
	 *    average()
	 *     warning:
	 *      avg method is required
	 *    avg()
	 *     warning:
	 *      collect method is required
	 *    chunk()
	 *     warning:
	 *      empty static method is required
	 *      get_iterator method is required
	 *    chunk_while()
	 *     warning:
	 *      chunk_while_collection method is required
	 *      get_iterator method is required
	 *    [protected] chunk_while_collection()
	 *     this method is overridden by the lv_hlp component
	 *     and is created for this purpose only
	 *     warning:
	 *      lv_arr_collection class is required
	 *    collapse()
	 *    collect()
	 *     warning:
	 *      lv_arr_collection class is required
	 *    combine()
	 *     warning:
	 *      make_iterator method is required
	 *    concat()
	 *    contains()
	 *     warning:
	 *      first method is required
	 *      operator_for_where method is required
	 *      use_as_callable method is required
	 *    contains_one_item()
	 *     warning:
	 *      count method is required
	 *      take method is required
	 *    contains_strict()
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      first method is required
	 *      use_as_callable method is required
	 *    count()
	 *     warning:
	 *      get_iterator method is required
	 *    count_by()
	 *     warning:
	 *      identity method is required
	 *      value_retriever method is required
	 *    cross_join()
	 *     warning:
	 *      passthru method is required
	 *    diff()
	 *     warning:
	 *      get_arrayable_items method is required
	 *    diff_assoc()
	 *     warning:
	 *      passthru method is required
	 *    diff_assoc_using()
	 *     warning:
	 *      passthru method is required
	 *    diff_keys()
	 *     warning:
	 *      passthru method is required
	 *    diff_keys_using()
	 *     warning:
	 *      this method was not tested
	 *      passthru method is required
	 *    diff_using()
	 *     warning:
	 *      this method was not tested
	 *      passthru method is required
	 *    doesnt_contain()
	 *     warning:
	 *      contains method is required
	 *    dot()
	 *     warning:
	 *      passthru method is required
	 *    duplicates()
	 *     warning:
	 *      passthru method is required
	 *    duplicates_strict()
	 *     warning:
	 *      passthru method is required
	 *    eager()
	 *     eager load all items into a new lazy collection
	 *     backed by an array
	 *     warning:
	 *      all method is required
	 *    except()
	 *     warning:
	 *      passthru method is required
	 *    filter()
	 *    first()
	 *     warning:
	 *      lv_arr_value function is required
	 *      get_iterator method is required
	 *    first_or_fail()
	 *     warning:
	 *      collect method is required
	 *      filter method is required
	 *      operator_for_where method is required
	 *      take method is required
	 *      unless method is required
	 *    first_where()
	 *     warning:
	 *      first method is required
	 *      operator_for_where method is required
	 *    flatten()
	 *    flip()
	 *    for_page()
	 *     warning:
	 *      slice method is required
	 *    get()
	 *     warning:
	 *      lv_arr_value function is required
	 *    get_iterator()
	 *     returns the iterator
			$iterator=lv_arr_collect([1, 2, 3, 4])->get_iterator();
			$iterator->current(); // 1
			$iterator->next();
			$iterator->current(); // 2
	 *     warning:
	 *      make_iterator method is required
	 *    group_by()
	 *     warning:
	 *      passthru method is required
	 *    has()
	 *    has_any()
	 *    implode()
	 *     warning:
	 *      collect method is required
	 *    intersect()
	 *     warning:
	 *      passthru method is required
	 *    intersect_assoc()
	 *     warning:
	 *      passthru method is required
	 *    intersect_assoc_using()
	 *     warning:
	 *      this method was not tested
	 *      passthru method is required
	 *    intersect_by_keys()
	 *     warning:
	 *      passthru method is required
	 *    intersect_using()
	 *     warning:
	 *      this method was not tested
	 *      passthru method is required
	 *    is_empty()
	 *     warning:
	 *      get_iterator method is required
	 *    is_not_empty()
	 *     warning:
	 *      is_empty method is required
	 *    join()
	 *     warning:
	 *      collect method is required
	 *    key_by()
	 *     warning:
	 *      value_retriever method is required
	 *    keys()
	 *    last()
	 *     warning:
	 *      lv_arr_value function is required
	 *    [static] make()
	 *    map()
	 *    map_into()
	 *     warning:
	 *      map method is required
	 *    map_to_dictionary()
	 *     warning:
	 *      passthru method is required
	 *    map_with_keys()
	 *    median()
	 *     warning:
	 *      collect method is required
	 *    merge()
	 *     warning:
	 *      passthru method is required
	 *    merge_recursive()
	 *     warning:
	 *      passthru method is required
	 *    mode()
	 *     warning:
	 *      collect method is required
	 *    nth()
	 *     warning:
	 *      slice method is required
	 *    only()
	 *    pad()
	 *     warning:
	 *      passthru method is required
	 *    percentage()
	 *     warning:
	 *      count method is required
	 *      filter method is required
	 *      is_empty method is required
	 *    pipe()
	 *    pipe_into()
	 *    pluck()
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      explode_pluck_parameters method is required
	 *    random()
	 *     warning:
	 *      collect method is required
	 *    [static] range()
	 *    reject()
	 *     warning:
	 *      filter method is required
	 *      use_as_callable method is required
	 *    remember()
	 *     returns a new lazy collection that will remember any values
	 *     that have already been enumerated and will not retrieve them
	 *     again on subsequent collection enumerations
			$collection=new lv_arr_lazy_collection(['user1', 'user2', 'user3', 'user4', 'user5']);
			$users=$collection->remember();
			// the first 3 users are hydrated from the database
			$users->take(3)->all();
			// first 3 users come from the collection's cache
			// the rest are hydrated from the database
			$users->take(5)->all();
	 *     warning:
	 *      get_iterator method is required
	 *    replace()
	 *     warning:
	 *      get_arrayable_items method is required
	 *    replace_recursive()
	 *     warning:
	 *      passthru method is required
	 *    reverse()
	 *     warning:
	 *      passthru method is required
	 *    search()
	 *     warning:
	 *      use_as_callable method is required
	 *    select()
	 *     warning:
	 *      lv_arr_accessible function is required
	 *      lv_arr_exists function is required
	 *    shuffle()
	 *     warning:
	 *      passthru method is required
	 *    skip()
	 *     warning:
	 *      get_iterator method is required
	 *    skip_until()
	 *     warning:
	 *      equality method is required
	 *      negate method is required
	 *      skip_while method is required
	 *      use_as_callable method is required
	 *    skip_while()
	 *     warning:
	 *      equality method is required
	 *      get_iterator method is required
	 *      use_as_callable method is required
	 *    slice()
	 *     warning:
	 *      passthru method is required
	 *      skip method is required
	 *      take method is required
	 *    sliding()
	 *     warning:
	 *      get_iterator method is required
	 *    sole()
	 *     warning:
	 *      collect method is required
	 *      filter method is required
	 *      operator_for_where method is required
	 *      take method is required
	 *      unless method is required
	 *    some()
	 *     warning:
	 *      contains method is required
	 *    sort()
	 *     warning:
	 *      passthru method is required
	 *    sort_by()
	 *     warning:
	 *      passthru method is required
	 *    sort_by_desc()
	 *     warning:
	 *      passthru method is required
	 *    sort_desc()
	 *     warning:
	 *      passthru method is required
	 *    sort_keys()
	 *     warning:
	 *      passthru method is required
	 *    sort_keys_desc()
	 *     warning:
	 *      passthru method is required
	 *    sort_keys_using()
	 *     warning:
	 *      passthru method is required
	 *    split()
	 *     warning:
	 *      passthru method is required
	 *    split_in()
	 *     warning:
	 *      chunk method is required
	 *      count method is required
	 *    take()
	 *     warning:
	 *      get_iterator method is required
	 *    take_until()
	 *     warning:
	 *      equality method is required
	 *      use_as_callable method is required
	 *    take_until_timeout()
	 *     returns a new lazy collection that will enumerate values
	 *     until the specified time
	 *     after that time, the collection will then stop enumerating
			$lazy_collection=$lv_lazy_collection_class::times(PHP_INT_MAX)->take_until_timeout(
				(new DateTime())->modify('+1 minute')
			);
			foreach($lazy_collection as $number)
			{
				var_dump($number);
				sleep(1);
			}
			// int(1)
			// int(2)
			// ...
			// int(58)
			// int(59)
	 *    take_while()
	 *     warning:
	 *      equality method is required
	 *      take_until method is required
	 *      use_as_callable method is required
	 *    tap()
	 *    tap_each()
	 *     while the each method calls the given callback for each item
	 *     in the collection right away, the tap_each method only calls
	 *     the given callback as the items are being pulled out
	 *     of the list one by one
			// nothing has been dumped so far
			$lazy_collection=lv_arr_lazy_collection::times(PHP_INT_MAX)->tap_each(function(int $value){
				var_dump($value);
			});
			// three items are dumped
			$array=$lazy_collection->take(3)->all(); // [1, 2, 3]
	 *    to_array()
	 *     warning:
	 *      all method is required
	 *      map method is required
	 *    to_json()
	 *     warning:
	 *      jsonSerialize method is required
	 *    undot()
	 *     warning:
	 *      passthru method is required
	 *    union()
	 *     warning:
	 *      passthru method is required
	 *    unique()
	 *     warning:
	 *      value_retriever method is required
	 *    unique_strict()
	 *     warning:
	 *      unique method is required
	 *    unless()
	 *     warning:
	 *      higher_order_when_proxy method is required
	 *    unless_empty()
	 *     warning:
	 *      when_not_empty method is required
	 *    unless_not_empty()
	 *     warning:
	 *      when_empty method is required
	 *    [static] unwrap()
	 *     warning:
	 *      all method is required
	 *    value()
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      lv_arr_value function is required
	 *      first_where method is required
	 *    values()
	 *    when()
	 *     warning:
	 *      higher_order_when_proxy method is required
	 *    when_empty()
	 *     warning:
	 *      is_empty method is required
	 *      when method is required
	 *    when_not_empty()
	 *     warning:
	 *      is_not_empty method is required
	 *      when method is required
	 *    where()
	 *     warning:
	 *      filter method is required
	 *      operator_for_where method is required
	 *    where_strict()
	 *     warning:
	 *      where method is required
	 *    where_in()
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      filter method is required
	 *      get_arrayable_items method is required
	 *    where_in_strict()
	 *     warning:
	 *      where_in method is required
	 *    where_instance_of()
	 *     warning:
	 *      filter method is required
	 *    where_not_between()
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      filter method is required
	 *    where_not_in()
	 *     warning:
	 *      lv_arr_data_get function is required
	 *      get_arrayable_items method is required
	 *      reject method is required
	 *    where_not_in_strict()
	 *     warning:
	 *      where_not_in method is required
	 *    where_not_null()
	 *     warning:
	 *      where method is required
	 *    where_null()
	 *     warning:
	 *      where_strict method is required
	 *    [static] wrap()
	 *     warning:
	 *      lv_arr_wrap function is required
	 *    zip()
	 *     warning:
	 *      lv_arr_collection class is required
	 *   methods implemented in the lv_hlp component:
	 *    dd()
	 *    dump()
	 *    ensure()
	 *    macro()
	 *
	 * Functions implemented in the lv_hlp component:
	 *  lv_arr_to_css_styles()
	 *
	 * Sources:
	 *  https://laravel.com/docs/10.x/helpers
	 *  https://github.com/illuminate/collections/blob/master/Arr.php
	 *  https://github.com/illuminate/collections/blob/master/helpers.php
	 *  https://github.com/illuminate/collections/blob/master/Collection.php
	 *  https://github.com/illuminate/collections/blob/master/Traits/EnumeratesValues.php
	 *  https://laravel.com/docs/10.x/collections
	 *  https://github.com/illuminate/conditionable/blob/master/Traits/Conditionable.php
	 *  https://github.com/illuminate/conditionable/blob/master/HigherOrderWhenProxy.php
	 *  https://github.com/illuminate/collections/blob/master/HigherOrderCollectionProxy.php
	 *  https://github.com/illuminate/collections/blob/master/Enumerable.php
	 *  https://github.com/illuminate/collections/blob/master/LazyCollection.php
	 */

	class lv_arr_exception extends Exception {}

	function lv_arr_accessible($value)
	{
		return (
			is_array($value) ||
			($value instanceof ArrayAccess)
		);
	}
	function lv_arr_add(array $array, $key, $value)
	{
		if(is_null(lv_arr_get($array, $key)))
			lv_arr_set($array, $key, $value);

		return $array;
	}
	function lv_arr_collapse(array $array)
	{
		$results=[];

		foreach($array as $values)
		{
			if($values instanceof lv_arr_enumerable)
				$values=$values->all();

			if(!is_array($values))
				continue;

			$results[]=$values;
		}

		return array_merge([], ...$results);
	}
	function lv_arr_collect($value=[])
	{
		return new lv_arr_collection($value);
	}
	function lv_arr_cross_join(array ...$arrays)
	{
		$results=[[]];

		foreach($arrays as $index=>$array)
		{
			$append=[];

			foreach($results as $product)
				foreach($array as $item)
				{
					$product[$index]=$item;
					$append[]=$product;
				}

			$results=$append;
		}

		return $results;
	}
	function lv_arr_divide(array $array)
	{
		return [array_keys($array), array_values($array)];
	}
	function lv_arr_dot(array $array, string $prepend='')
	{
		$results=[];

		foreach($array as $key=>$value)
		{
			if(is_array($value) && (!empty($value)))
			{
				$results=array_merge(
					$results,
					(__METHOD__)($value, $prepend.$key.'.')
				);

				continue;
			}

			$results[$prepend.$key]=$value;
		}

		return $results;
	}
	function lv_arr_exists($array, $key)
	{
		if($array instanceof lv_arr_enumerable)
			return $array->has($key);

		if($array instanceof ArrayAccess)
			return $array->offsetExists($key);

		if(!is_array($array))
			throw new lv_arr_exception(__METHOD__.'(): $array is not an ArrayAccess nor array');

		if(is_float($key))
			$key=(string)$key;

		if((!is_int($key)) && (!is_string($key)))
			throw new lv_arr_exception(__METHOD__.'(): $key is not an int nor string');

		return array_key_exists($key, $array);
	}
	function lv_arr_except(array $array, $keys)
	{
		lv_arr_forget($array, $keys);
		return $array;
	}
	function lv_arr_first(array $array, callable $callback=null, $default=null)
	{
		if(is_null($callback))
		{
			if(empty($array))
				return lv_arr_value($default);

			foreach($array as $item)
				return $item;

			return lv_arr_value($default);
		}

		foreach($array as $key=>$value)
			if($callback($value, $key))
				return $value;

		return lv_arr_value($default);
	}
	function lv_arr_flatten(array $array, $depth=INF)
	{
		$result=[];

		foreach($array as $item)
		{
			if($item instanceof lv_arr_collection)
				$value=$item->all();

			if(!is_array($item))
			{
				$result[]=$item;
				continue;
			}

			if($depth === 1)
				$values=array_values($item);
			else
				$values=(__METHOD__)($item, $depth-1);

			foreach($values as $value)
				$result[]=$value;
		}

		return $result;
	}
	function lv_arr_forget(array &$array, $keys)
	{
		$original=&$array;

		if(!is_array($keys))
			$keys=[$keys];

		if(count($keys) === 0)
			return null;

		foreach($keys as $key)
		{
			// if the exact key exists in the top-level, remove it
			if(lv_arr_exists($array, $key))
			{
				unset($array[$key]);
				continue;
			}

			$parts=explode('.', $key);

			// clean up before each pass
			$array=&$original;

			while(count($parts) > 1)
			{
				$part=array_shift($parts);

				if(
					isset($array[$part]) &&
					lv_arr_accessible($array[$part])
				){
					$array=&$array[$part];
					continue;
				}

				continue 2;
			}

			unset($array[array_shift($parts)]);
		}
	}
	function lv_arr_get($array, $key, $default=null)
	{
		if(!lv_arr_accessible($array))
			return lv_arr_value($default);

		if(is_null($key))
			return $array;

		if(lv_arr_exists($array, $key))
			return $array[$key];

		if(strpos($key, '.') === false) // if(!str_contains($key, '.'))
		{
			// null coalescing operator converted to if

			if(array_key_exists($key, $array))
				return $array[$key];

			return lv_arr_value($default);
		}

		foreach(explode('.', $key) as $segment)
		{
			if(
				lv_arr_accessible($array) &&
				lv_arr_exists($array, $segment)
			){
				$array=$array[$segment];
				continue;
			}

			return lv_arr_value($default);
		}

		return $array;
	}
	function lv_arr_has($array, $keys)
	{
		if((!is_array($array)) && (!$array instanceof ArrayAccess))
			throw new lv_arr_exception(__METHOD__.'(): $array is not an ArrayAccess nor array');

		if(empty($array))
			return false;

		if(empty($keys))
			return false;

		if(!is_array($keys))
			$keys=[$keys];

		foreach($keys as $key)
		{
			$sub_key_array=$array;

			if(lv_arr_exists($array, $key))
				continue;

			foreach(explode('.', $key) as $segment)
			{
				if(
					lv_arr_accessible($sub_key_array) &&
					lv_arr_exists($sub_key_array, $segment)
				){
					$sub_key_array=$sub_key_array[$segment];
					continue;
				}

				return false;
			}
		}

		return true;
	}
	function lv_arr_has_any($array, $keys)
	{
		if((!is_array($array)) && (!$array instanceof ArrayAccess))
			throw new lv_arr_exception(__METHOD__.'(): $array is not an ArrayAccess nor array');

		if(empty($array))
			return false;

		if(is_null($keys) || empty($keys))
			return false;

		if(!is_array($keys))
			$keys=[$keys];

		foreach($keys as $key)
			if(lv_arr_has($array, $key))
				return true;

		return false;
	}
	function lv_arr_head(array $array)
	{
		return reset($array);
	}
	function lv_arr_is_assoc(array $array)
	{
		$keys=array_keys($array);
		return (array_keys($keys) !== $keys);
	}
	function lv_arr_is_list(array $array)
	{
		return (!lv_arr_is_assoc($array));
	}
	function lv_arr_join(array $array, string $glue, string $final_glue='')
	{
		if($final_glue === '')
			return implode($glue, $array);

		if(count($array) === 0)
			return '';

		if(count($array) === 1)
			return end($array);

		$final_item=array_pop($array);

		return implode($glue, $array).$final_glue.$final_item;
	}
	function lv_arr_key_by(array $array, $key_by)
	{
		static $_get_arrayable_items=null;
		static $_key_by=null;

		if(!isset($_get_arrayable_items))
		{
			$_get_arrayable_items=function($items)
			{
				if(is_array($items))
					return $items;

				// removed if($items instanceof Arrayable)
				// removed if($items instanceof Jsonable)

				if($items instanceof lv_arr_enumerable)
					return $items->all();

				if($items instanceof Traversable)
					return iterator_to_array($items);

				if($items instanceof JsonSerializable)
					return (array)$items->jsonSerialize();

				if($items instanceof UnitEnum)
					return [$items];

				return (array)$items;
			};
			$_key_by=function($items, $key_by)
			{
				// valueRetriever ->
					$key_by_callback=$key_by;

					if(is_string($key_by) || (!is_callable($key_by))) // useAsCallable
						$key_by_callback=function($item) use($key_by)
						{
							return lv_arr_data_get($item, $key_by);
						};
				// <- valueRetriever

				$results=[];

				foreach($items as $key=>$item)
				{
					$resolved_key=$key_by_callback($item, $key);

					if(is_object($resolved_key))
						$resolved_key=(string)$resolved_key;

					$results[$resolved_key]=$item;
				}

				return $results;
			};
		}

		$items=$_get_arrayable_items($array); // Collection::make($array) // __construct // getArrayableItems
		$items=$_key_by($items, $key_by); // ->keyBy($key_by)->all() // removed __construct // removed getArrayableItems

		return $items;
	}
	function lv_arr_last(array $array, callable $callback=null, $default=null)
	{
		if(is_null($callback))
		{
			if(empty($array))
				return lv_arr_value($default);

			return end($array);
		}

		return lv_arr_first(array_reverse($array, true), $callback, $default);
	}
	function lv_arr_lazy_collect($value=[])
	{
		return new lv_arr_lazy_collection($value);
	}
	function lv_arr_map(array $array, callable $callback)
	{
		$keys=array_keys($array);

		try {
			$items=array_map($callback, $array, $keys);
		} catch(ArgumentCountError $error) {
			$items=array_map($callback, $array);
		}

		return array_combine($keys, $items);
	}
	function lv_arr_map_with_keys(array $array, callable $callback)
	{
		$result=[];

		foreach($array as $key=>$value)
		{
			$assoc=$callback($value, $key);

			foreach($assoc as $map_key=>$map_value)
				$result[$map_key]=$map_value;
		}

		return $result;
	}
	function lv_arr_only(array $array, $keys)
	{
		return array_intersect_key($array, array_flip((array) $keys));
	}
	function lv_arr_pluck(array $array, $value, $key=null)
	{
		$results=[];

		// explodePluckParameters ->
			if(is_string($value))
				$value=explode('.', $value);

			if(!(
				is_null($key) ||
				is_array($key)
			))
				$key=explode('.', $key);
		// <- explodePluckParameters

		foreach($array as $item)
		{
			$item_value=lv_arr_data_get($item, $value);

			/*
			 * if the key is "null", we will just append the value to the array and keep
			 * looping. otherwise we will key the array using the value of the key we
			 * received from the developer. then we'll return the final array form
			 */
			if(is_null($key))
			{
				$results[]=$item_value;
				continue;
			}

			$item_key=lv_arr_data_get($item, $key);

			if(
				is_object($item_key) &&
				method_exists($item_key, '__toString')
			)
				$item_key=(string)$item_key;

			$results[$item_key]=$item_value;
		}

		return $results;
	}
	function lv_arr_prepend(array $array, $value, $key=null)
	{
		if(func_num_args() === 3)
			return [$key=>$value]+$array;

		array_unshift($array, $value);

		return $array;
	}
	function lv_arr_prepend_keys_with(array $array, string $prepend_with)
	{
		return lv_arr_map_with_keys($array, function($item, $key) use($prepend_with){
			return [$prepend_with.$key=>$item];
		});
	}
	function lv_arr_pull(array &$array, $key, $default=null)
	{
		$value=lv_arr_get($array, $key, $default);
		lv_arr_forget($array, $key);

		return $value;
	}
	function lv_arr_query(array $array)
	{
		return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
	}
	function lv_arr_random(array $array, int $number=null, bool $preserve_keys=false)
	{
		$requested=1;
		$count=count($array);

		if(!is_null($number))
			$requested=$number;

		if($requested > $count)
			throw new lv_arr_exception('You requested '.$requested.' items, but there are only '.$count.' items available');

		if(is_null($number))
			return $array[array_rand($array)];

		if((int)$number === 0)
			return [];

		$keys=array_rand($array, $number);
		$results=[];

		if($preserve_keys)
		{
			foreach((array)$keys as $key)
				$results[$key]=$array[$key];

			return $results;
		}

		foreach((array)$keys as $key)
			$results[]=$array[$key];

		return $results;
	}
	function lv_arr_select(array $array, /*array|string*/ $keys)
	{
		return lv_arr_map($array, function($item) use($keys){
			$result=[];

			foreach($keys as $key)
			{
				if(
					lv_arr_accessible($item) &&
					lv_arr_exists($item, $key)
				){
					$result[$key]=$item[$key];
					continue;
				}

				if(is_object($item) && isset($item->$key))
					$result[$key]=$item->$key;
			}

			return $result;
		});
	}
	function lv_arr_set(array &$array, $key, $value)
	{
		if(is_null($key))
		{
			$array=$value;
			return $value;
		}

		$keys=explode('.', $key);

		foreach($keys as $i=>$key)
		{
			if(count($keys) === 1)
				break;

			unset($keys[$i]);

			/*
			 * if the key doesn't exist at this depth, we will just create an empty array
			 * to hold the next value, allowing us to create the arrays to hold final
			 * values at the correct depth. then we'll keep digging into the array
			 */
			if(
				(!isset($array[$key])) ||
				(!is_array($array[$key]))
			)
				$array[$key]=[];

			$array=&$array[$key];
		}

		$array[array_shift($keys)]=$value;

		return $array;
	}
	function lv_arr_shuffle(array $array, int $seed=null)
	{
		if(is_null($seed))
		{
			shuffle($array);
			return $array;
		}

		mt_srand($seed);
		shuffle($array);
		mt_srand();

		return $array;
	}
	function lv_arr_sort(array $array, $callback=null)
	{
		return lv_arr_collection::make($array)->sort_by($callback)->all();
	}
	function lv_arr_sort_desc(array $array, $callback=null)
	{
		return lv_arr_collection::make($array)->sort_by_desc($callback)->all();
	}
	function lv_arr_sort_recursive(array $array, int $options=SORT_REGULAR, bool $descending=false)
	{
		foreach($array as &$value)
			if(is_array($value))
				$value=(__METHOD__)($value, $options, $descending);

		if(lv_arr_is_assoc($array))
		{
			if($descending)
			{
				krsort($array, $options);
				return $array;
			}

			ksort($array, $options);

			return $array;
		}

		if($descending)
		{
			rsort($array, $options);
			return $array;
		}

		sort($array, $options);

		return $array;
	}
	function lv_arr_sort_recursive_desc($array, $options=SORT_REGULAR)
	{
		return lv_arr_sort_recursive($array, $options, true);
	}
	function lv_arr_to_css_classes(array $array)
	{
		$class_list=lv_arr_wrap($array);
		$classes=[];

		foreach($class_list as $class=>$constraint)
		{
			if(is_numeric($class))
			{
				$classes[]=$constraint;
				continue;
			}

			if($constraint)
				$classes[]=$class;
		}

		return implode(' ', $classes);
	}
	function lv_arr_undot(array $array)
	{
		$results=[];

		foreach($array as $key=>$value)
			lv_arr_set($results, $key, $value);

		return $results;
	}
	function lv_arr_where(array $array, callable $callback)
	{
		return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
	}
	function lv_arr_where_not_null(array $array)
	{
		return lv_arr_where($array, function($value){
			return (!is_null($value));
		});
	}
	function lv_arr_wrap($value)
	{
		if(is_null($value))
			return [];

		if(is_array($value))
			return $value;

		return [$value];
	}
	function lv_arr_data_fill(&$target, $key, $value)
	{
		if(
			(!is_array($key)) &&
			(!is_string($key))
		)
			throw new lv_arr_exception(__METHOD__.'(): $key is not an array nor string');

		return lv_arr_data_set($target, $key, $value, false);
	}
	function lv_arr_data_get($target, $key, $default=null)
	{
		if(is_null($key))
			return $target;

		if(!is_array($key))
			$key=explode('.', $key);

		foreach($key as $i=>$segment)
		{
			unset($key[$i]);

			if(is_null($segment))
				return $target;

			if($segment === '*')
			{
				if($target instanceof lv_arr_enumerable)
					$target=$target->all();
				else if(!is_iterable($target))
					return lv_arr_value($default);

				$result=[];

				foreach($target as $item)
					$result[]=(__METHOD__)($item, $key);

				if(in_array('*', $key))
					return lv_arr_collapse($result);

				return $result;
			}

			if(
				lv_arr_accessible($target) &&
				lv_arr_exists($target, $segment)
			){
				$target=$target[$segment];
				continue;
			}

			if(
				is_object($target) &&
				isset($target->$segment)
			){
				$target=$target->$segment;
				continue;
			}

			return lv_arr_value($default);
		}

		return $target;
	}
	function lv_arr_data_set(&$target, $key, $value, $overwrite=true)
	{
		switch(true)
		{
			case (is_array($key)):
				$segments=$key;
			break;
			case (is_string($key)):
				$segments=explode('.', $key);
			break;
			default:
				throw new lv_arr_exception(__METHOD__.'(): $key is not an array nor string');
		}

		$segment=array_shift($segments);

		if($segment === '*')
		{
			if(!lv_arr_accessible($target))
				$target=[];

			if($segments)
			{
				foreach($target as &$inner)
					(__METHOD__)($inner, $segments, $value, $overwrite);

				return $target;
			}

			if($overwrite)
				foreach($target as &$inner)
					$inner=$value;

			return $target;
		}

		if(lv_arr_accessible($target))
		{
			if($segments)
			{
				if(!lv_arr_exists($target, $segment))
					$target[$segment]=[];

				(__METHOD__)($target[$segment], $segments, $value, $overwrite);

				return $target;
			}

			if($overwrite || (!lv_arr_exists($target, $segment)))
				$target[$segment]=$value;

			return $target;
		}

		if(is_object($target))
		{
			if($segments)
			{
				if(!isset($target->$segment))
					$target->$segment=[];

				(__METHOD__)($target->{$segment}, $segments, $value, $overwrite);

				return $target;
			}

			if($overwrite || (!isset($target->$segment)))
				$target->$segment=$value;

			return $target;
		}

		$target=[];

		if($segments)
		{
			(__METHOD__)($target[$segment], $segments, $value, $overwrite);
			return $target;
		}

		if($overwrite)
			$target[$segment]=$value;

		return $target;
	}
	function lv_arr_data_forget(&$target, $key)
	{
		$segments=$key;

		if(!is_array($key))
			$segments=explode('.', $key);

		$segment=array_shift($segments);

		if(
			($segment === '*') &&
			lv_arr_accessible($target)
		){
			if($segments)
				foreach($target as &$inner)
					(__METHOD__)($inner, $segments);

			return $target;
		}

		if(lv_arr_accessible($target))
		{
			if($segments && lv_arr_exists($target, $segment))
			{
				(__METHOD__)($target[$segment], $segments);
				return $target;
			}

			lv_arr_forget($target, $segment);

			return $target;
		}

		if(is_object($target))
		{
			if($segments && isset($target->$segment))
			{
				(__METHOD__)($target->$segment, $segments);
				return $target;
			}

			if(isset($target->$segment))
				unset($target->$segment);
		}

		return $target;
	}
	function lv_arr_value($value, ...$args)
	{
		if($value instanceof Closure)
			return $value(...$args);

		return $value;
	}

	interface lv_arr_enumerable extends Countable, IteratorAggregate, JsonSerializable
	{
		// trait conditionable -> lv_arr_enumerates_values
			public function unless($value, callable $callback, callable $default=null);
			public function when($value, callable $callback=null, callable $default=null);

		// lv_arr_enumerates_values
			public static function empty();
			public static function make(array $items=[]);
			public static function times(int $number, callable $callback=null);
			public static function unwrap($value);
			public static function wrap($value);

			public function __get($key);
			public function __toString();

			public function average($callback=null);
			public function collect();
			public function each(callable $callback);
			public function each_spread(callable $callback);
			public function escape_when_casting_to_string(bool $escape=true);
			public function every($key, string $operator=null, $value=null);
			public function first_where($key, string $operator=null, $value=null);
			public function flat_map(callable $callback);
			public function for_page(int $page, int $per_page);
			public function is_not_empty();
			public function map_into(string $class);
			public function map_spread(callable $callback);
			public function map_to_groups(callable $callback);
			public function max($callback=null);
			public function min($callback=null);
			public function partition($key, string $operator=null, $value=null);
			public function percentage(callable $callback, int $precision=2);
			public function pipe(callable $callback);
			public function pipe_into(string $class);
			public function pipe_through(array $pipes);
			public function reduce(callable $callback, $initial=null);
			public function reduce_spread(callable $callback, ...$initial);
			public function reject($callback=true);
			public function some($key, string $operator=null, $value=null);
			public function sum($callback=null);
			public function tap(callable $callback);
			public function to_array();
			public function to_json(int $options=0);
			public function unique_strict($key=null);
			public function unless_empty(callable $callback, callable $default=null);
			public function unless_not_empty(callable $callback, callable $default=null);
			public function value(string $key, $default=null);
			public function when_empty(callable $callback, callable $default=null);
			public function when_not_empty(callable $callback, callable $default=null);
			public function where($key, string $operator=null, $value=null);
			public function where_strict(string $key, $value);
			public function where_between(string $key, array $values);
			public function where_in(string $key, $values, bool $strict=false);
			public function where_in_strict(string $key, $values);
			public function where_instance_of($type);
			public function where_not_between(string $key, array $values);
			public function where_not_in(string $key, $values, bool $strict=false);
			public function where_not_in_strict(string $key, $values);
			public function where_not_null(string $key=null);
			public function where_null(string $key=null);

		// collections
			public static function range(int $from, int $to);

			public function all();
			public function avg($callback=null);
			public function chunk(int $size);
			public function chunk_while(callable $callback);
			public function collapse();
			public function combine($values);
			public function concat(array $source);
			public function contains($key, string $operator=null, $value=null);
			public function contains_one_item();
			public function contains_strict($key, $value=null);
			public function count(): int;
			public function count_by($count_by=null);
			public function cross_join(...$lists);
			public function diff($items);
			public function diff_assoc($items);
			public function diff_assoc_using($items, callable $callback);
			public function diff_keys($items);
			public function diff_keys_using($items, callable $callback);
			public function diff_using($items, callable $callback);
			public function doesnt_contain($key, string $operator=null, $value=null);
			public function dot();
			public function duplicates($callback=null, bool $strict=false);
			public function duplicates_strict(callable $callback=null);
			public function except($keys);
			public function filter(callable $callback=null);
			public function first(callable $callback=null, $default=null);
			public function first_or_fail($key=null, string $operator=null, $value=null);
			public function flatten($depth=INF);
			public function flip();
			public function get($key, $default=null);
			public function group_by($group_by, bool $preserve_keys=false);
			public function has($key);
			public function has_any($key);
			public function implode($value, string $glue=null);
			public function intersect($items);
			public function intersect_assoc($items);
			public function intersect_assoc_using($items, callable $callback);
			public function intersect_by_keys($items);
			public function intersect_using($items, callable $callback);
			public function is_empty();
			public function join(string $glue, string $final_glue='');
			public function key_by($key_by);
			public function keys();
			public function last(callable $callback=null, $default=null);
			public function map(callable $callback);
			public function map_to_dictionary(callable $callback);
			public function map_with_keys(callable $callback);
			public function median($key=null);
			public function merge($items);
			public function merge_recursive($items);
			public function mode($key=null);
			public function nth(int $step, int $offset=0);
			public function only($keys);
			public function pad(int $size, $value);
			public function pluck($value, string $key=null);
			public function random($number=null);
			public function replace($items);
			public function replace_recursive($items);
			public function reverse();
			public function search($value, bool $strict=false);
			public function select($keys);
			public function shuffle();
			public function skip(int $count);
			public function skip_until($value);
			public function skip_while($value);
			public function slice(int $offset, int $length=null);
			public function sliding(int $size=2, int $step=1);
			public function sole($key=null, string $operator=null, $value=null);
			public function sort($callback=null);
			public function sort_by($callback, int $options=SORT_REGULAR, bool $descending=false);
			public function sort_by_desc($callback, int $options=SORT_REGULAR);
			public function sort_desc(int $options=SORT_REGULAR);
			public function sort_keys(int $options=SORT_REGULAR, bool $descending=false);
			public function sort_keys_desc(int $options=SORT_REGULAR);
			public function sort_keys_using(callable $callback);
			public function split(int $number_of_groups);
			public function split_in(int $number_of_groups);
			public function take(int $limit);
			public function take_until($value);
			public function take_while($value);
			public function undot();
			public function union($items);
			public function unique($key=null, bool $strict=false);
			public function values();
			public function zip($items);
	}

	trait lv_arr_enumerates_values
	{
		// use conditionable, dumpable;

		/* trait conditionable */
		/* { */
			// dev note: this trait appears in the lv_str.php library

			protected function higher_order_when_proxy($target)
			{
				return new class($target)
				{
					// class HigherOrderWhenProxy

					private $target;
					private $condition;
					private $has_condition=false;
					private $negate_condition_on_capture;

					public function __construct($target)
					{
						$this->target=$target;
					}
					public function __get($key)
					{
						if(!$this->has_condition)
						{
							$condition=$this->target->$key;

							if($this->negate_condition_on_capture)
								return $this->condition(!$condition);

							return $this->condition($condition);
						}

						if($this->condition)
							return $this->target->$key;

						return $this->target;
					}
					public function __call($method, $parameters)
					{
						if(!$this->has_condition)
						{
							$condition=$this->target->$method(...$parameters);

							if($this->negate_condition_on_capture)
								return $this->condition(!$condition);

							return $this->condition($condition);
						}

						if($this->condition)
							return $this->target->$method(...$parameters);

						return $this->target;
					}

					public function condition($condition)
					{
						$this->condition=$condition;
						$this->has_condition=true;

						return $this;
					}
					public function negate_condition_on_capture()
					{
						$this->negate_condition_on_capture=true;
						return $this;
					}
				};
			}

			public function unless($value=null, callable $callback=null, callable $default=null)
			{
				if($value instanceof Closure)
					$value=$value($this);

				if(func_num_args() === 0)
					return $this->higher_order_when_proxy($this)->negate_condition_on_capture();
				if(func_num_args() === 1)
					return $this->higher_order_when_proxy($this)->condition(!$value);

				if(!$value)
					return ($callback($this, $value) ?? $this);

				if($default)
					return ($default($this, $value) ?? $this);

				return $this;
			}
			public function when($value=null, callable $callback=null, callable $default=null)
			{
				if($value instanceof Closure)
					$value=$value($this);

				if(func_num_args() === 0)
					return new $this->higher_order_when_proxy($this);
				if(func_num_args() === 1)
					return $this->higher_order_when_proxy($this)->condition($value);

				if($value)
					return ($callback($this, $value) ?? $this);

				if($default)
					return ($default($this, $value) ?? $this);

				return $this;
			}
		/* } */

		/* trait dumpable */
		/* { */
			// implemented in the lv_hlp component
		/* } */

		protected $escape_when_casting_to_string=false;

		public static function empty()
		{
			return new static([]);
		}
		public static function make(array $items=[])
		{
			return new static($items);
		}
		public static function times(int $number, callable $callback=null)
		{
			if($number < 1)
				return new static();

			return static::range(1, $number)
			->	unless($callback == null)
			->	map($callback);
		}
		public static function unwrap($value)
		{
			if($value instanceof lv_arr_enumerable)
				return $value->all();

			return $value;
		}
		public static function wrap($value)
		{
			if($value instanceof lv_arr_enumerable)
				return new static($value);

			return new static(lv_arr_wrap($value));
		}

		public function __get($key)
		{
			return $this->higher_order_collection_proxy($this, 'map');
		}
		public function __toString()
		{
			if($this->escape_when_casting_to_string)
				return htmlspecialchars($this->to_json(), ENT_QUOTES, 'UTF-8', true);

			return $this->to_json();
		}

		protected function get_arrayable_items($items)
		{
			if(is_array($items))
				return $items;

			switch(true)
			{
				case ($items instanceof WeakMap): // PHP8
					throw new lv_arr_exception('Collections can not be created using instances of WeakMap.');
				case ($items instanceof lv_arr_enumerable):
					return $items->all();
				// removed ($items instanceof Arrayable):
				case ($items instanceof Traversable):
					return iterator_to_array($items);
				// removed ($items instanceof Jsonable):
				case ($items instanceof JsonSerializable):
					return (array)$items->jsonSerialize();
				case ($items instanceof UnitEnum): // PHP8 8.1.0
					return [$items];
				default:
					return (array)$items;
			}
		}
		protected function higher_order_collection_proxy($collection, $method)
		{
			return new class($collection, $method)
			{
				// class HigherOrderCollectionProxy

				private $collection;
				private $method;

				public function __construct($collection, $method)
				{
					$this->method=$method;
					$this->collection=$collection;
				}
				public function __get($key)
				{
					return $this->collection->{$this->method}(function($value) use($key){
						if(is_array($value))
							return $value[$key];

						return $value->$key;
					});
				}
				public function __call($method, $parameters)
				{
					return $this->collection->{$this->method}(function($value) use($method, $parameters){
						return $value->{$method}(...$parameters);
					});
				}
			};
		}
		protected function identity()
		{
			return function($value){
				return $value;
			};
		}
		protected function operator_for_where($key, /*string*/ $operator=null, $value=null)
		{
			if($this->use_as_callable($key))
				return $key;

			if(func_num_args() === 1)
			{
				$value=true;
				$operator='=';
			}
			if(func_num_args() === 2)
			{
				$value=$operator;
				$operator='=';
			}

			return function($item) use($key, $operator, $value)
			{
				$retrieved=lv_arr_data_get($item, $key);
				$strings=array_filter([$retrieved, $value], function($value){
					return (
						is_string($value) ||
						(is_object($value) && method_exists($value, '__toString'))
					);
				});

				if(
					(count($strings) < 2) &&
					(count(array_filter([$retrieved, $value], 'is_object')) == 1)
				)
					return in_array($operator, ['!=', '<>', '!==']);

				switch($operator)
				{
					default:
					case '=':
					case '==':
						return ($retrieved == $value);
					case '!=':
					case '<>':
						return ($retrieved != $value);
					case '<':
						return ($retrieved < $value);
					case '>':
						return ($retrieved > $value);
					case '<=':
						return ($retrieved <= $value);
					case '>=':
						return ($retrieved >= $value);
					case '===':
						return ($retrieved === $value);
					case '!==':
						return ($retrieved !== $value);
					case '<=>':
						return ($retrieved <=> $value);
				}
			};
		}
		protected function use_as_callable($value)
		{
			return ((!is_string($value)) && is_callable($value));
		}
		protected function value_retriever($value)
		{
			if($this->use_as_callable($value))
				return $value;

			return function($item) use($value){
				return lv_arr_data_get($item, $value);
			};
		}

		public function average($callback=null)
		{
			return $this->avg($callback);
		}
		public function collect()
		{
			return new lv_arr_collection($this->all());
		}
		public function each(callable $callback)
		{
			foreach($this->items as $key=>$item)
				if($callback($item, $key) === false)
					break;

			return $this;
		}
		public function each_spread(callable $callback)
		{
			return $this->each(function($chunk, $key) use($callback){
				$chunk[]=$key;
				return $callback(...$chunk);
			});
		}
		public function escape_when_casting_to_string(bool $escape=true)
		{
			$this->escape_when_casting_to_string=$escape;
			return $this;
		}
		public function every($key, string $operator=null, $value=null)
		{
			if(func_num_args() === 1)
			{
				$callback=$this->value_retriever($key);

				foreach($this->items as $k=>$v)
					if(!$callback($v, $k))
						return false;

				return true;
			}

			return (__METHOD__)($this->operator_for_where(...func_get_args()));
		}
		public function first_where($key, string $operator=null, $value=null)
		{
			return $this->first($this->operator_for_where(...func_get_args()));
		}
		public function flat_map(callable $callback)
		{
			return $this->map($callback)->collapse();
		}
		public function for_page(int $page, int $per_page)
		{
			$offset=max(0, ($page-1)*$per_page);
			return $this->slice($offset, $per_page);
		}
		public function is_not_empty()
		{
			return (!$this->is_empty());
		}
		public function jsonSerialize()
		{
			return array_map(
				function($value){
					if($value instanceof JsonSerializable)
						return $value->jsonSerialize();
					// removed else if($value instanceof Jsonable)
					// removes else if($value instanceof Arrayable)

					return $value;
				},
				$this->all()
			);
		}
		public function map_into(string $class)
		{
			return $this->map(function($value, $key) use($class){
				return new $class($value, $key);
			});
		}
		public function map_spread(callable $callback)
		{
			return $this->map(function($chunk, $key) use($callback){
				$chunk[]=$key;
				return $callback(...$chunk->items);
			});
		}
		public function map_to_groups(callable $callback)
		{
			$groups=$this->map_to_dictionary($callback);
			return $groups->map([$this, 'make']);
		}
		public function max($callback=null)
		{
			$callback=$this->value_retriever($callback);

			return $this
			->	filter(function($value){
					return (!is_null($value));
				})
			->	reduce(function($result, $item) use($callback){
					$value=$callback($item);

					if(is_null($result) || ($value > $result))
						return $value;

					return $result;
				});
		}
		public function min($callback=null)
		{
			$callback=$this->value_retriever($callback);

			return $this
			->	map(function($value) use($callback){
					return $callback($value);
				})
			->	filter(function($value){
					return (!is_null($value));
				})
			->	reduce(function($result, $value){
					if(is_null($result) || ($value < $result))
						return $value;

					return $result;
				});
		}
		public function partition($key, string $operator=null, $value=null)
		{
			$passed=[];
			$failed=[];

			if(func_num_args() === 1)
				$callback=$this->value_retriever($key);
			else
				$callback=$this->operator_for_where(...func_get_args());

			foreach($this->items as $key=>$item)
			{
				if($callback($item, $key))
				{
					$passed[$key]=$item;
					continue;
				}

				$failed[$key]=$item;
			}

			return new static([new static($passed), new static($failed)]);
		}
		public function percentage(callable $callback, int $precision=2)
		{
			if($this->is_empty())
				return null;

			return round(
				$this->filter($callback)->count()/$this->count()*100,
				$precision
			);
		}
		public function pipe(callable $callback)
		{
			return $callback($this);
		}
		public function pipe_into(string $class)
		{
			return new $class($this);
		}
		public function pipe_through(array $callbacks)
		{
			return static::make($callbacks)->reduce(function($carry, $callback){
				return $callback($carry);
			}, $this);
		}
		public function reduce(callable $callback, $initial=null)
		{
			$result=$initial;

			foreach($this->items as $key=>$value)
				$result=$callback($result, $value, $key);

			return $result;
		}
		public function reduce_spread(callable $callback, ...$initial)
		{
			$result=$initial;

			foreach($this->items as $key=>$value)
			{
				$result=call_user_func_array($callback, array_merge($result, [$value, $key]));

				if(!is_array($result))
					throw new lv_arr_exception(sprintf(
						"%s::reduceSpread expects reducer to return an array, but got a '%s' instead.",
						class_basename(static::class),
						gettype($result)
					));
			}

			return $result;
		}
		public function reject($callback=true)
		{
			$use_as_callable=$this->use_as_callable($callback);

			return $this->filter(function($value, $key) use($callback, $use_as_callable){
				if($use_as_callable)
					return (!$callback($value, $key));

				return ($value != $callback);
			});
		}
		public function some($key, string $operator=null, $value=null)
		{
			return $this->contains(...func_get_args());
		}
		public function sum($callback=null)
		{
			if(is_null($callback))
				$callback=$this->identity();
			else
				$callback=$this->value_retriever($callback);

			return $this->reduce(function($result, $item) use($callback){
				return ($result+$callback($item));
			}, 0);
		}
		public function tap(callable $callback)
		{
			$callback($this);
			return $this;
		}
		public function to_array()
		{
			return $this
			->	map(function($value){
					// removed if($value instanceof Arrayable)

					return $value;
				})
			->	all();
		}
		public function to_json(int $options=0)
		{
			return json_encode($this->jsonSerialize(), $options);
		}
		public function unique_strict($key=null)
		{
			return $this->unique($key, true);
		}
		public function unless_empty(callable $callback, callable $default=null)
		{
			return $this->when_not_empty($callback, $default);
		}
		public function unless_not_empty(callable $callback, callable $default=null)
		{
			return $this->when_empty($callback, $default);
		}
		public function value(string $key, $default=null)
		{
			if($value=$this->first_where($key))
				return lv_arr_data_get($value, $key, $default);

			return lv_arr_value($default);
		}
		public function when_empty(callable $callback, callable $default=null)
		{
			return $this->when($this->is_empty(), $callback, $default);
		}
		public function when_not_empty(callable $callback, callable $default=null)
		{
			return $this->when($this->is_not_empty(), $callback, $default);
		}
		public function where($key, string $operator=null, $value=null)
		{
			return $this->filter($this->operator_for_where(...func_get_args()));
		}
		public function where_strict(string $key, $value)
		{
			return $this->where($key, '===', $value);
		}
		public function where_between(string $key, array $values)
		{
			return $this->where($key, '>=', reset($values))->where($key, '<=', end($values));
		}
		public function where_in(string $key, $values, bool $strict=false)
		{
			$values=$this->get_arrayable_items($values);

			return $this->filter(function($item) use($key, $values, $strict){
				return in_array(lv_arr_data_get($item, $key), $values, $strict);
			});
		}
		public function where_in_strict(string $key, $values)
		{
			return $this->where_in($key, $values, true);
		}
		public function where_instance_of($type)
		{
			return $this->filter(function($value) use($type){
				if(is_array($type))
				{
					foreach($type as $class_type)
						if($value instanceof $class_type)
							return true;

					return false;
				}

				return ($value instanceof $type);
			});
		}
		public function where_not_between(string $key, array $values)
		{
			return $this->filter(function($item) use($key, $values){
				$data_get=lv_arr_data_get($item, $key);

				return (
					($data_get < reset($values)) ||
					($data_get > end($values))
				);
			});
		}
		public function where_not_in(string $key, $values, bool $strict=false)
		{
			$values=$this->get_arrayable_items($values);

			return $this->reject(function($item) use($key, $values, $strict){
				return in_array(lv_arr_data_get($item, $key), $values, $strict);
			});
		}
		public function where_not_in_strict(string $key, $values)
		{
			return $this->where_not_in($key, $values, true);
		}
		public function where_not_null(string $key=null)
		{
			return $this->where($key, '!==', null);
		}
		public function where_null(string $key=null)
		{
			return $this->where_strict($key, null);
		}
	}

	class lv_arr_collection implements ArrayAccess, lv_arr_enumerable
	{
		use lv_arr_enumerates_values;

		protected $items=[];

		public static function range(int $from, int $to)
		{
			return new static(range($from, $to));
		}

		public function __construct($items=[])
		{
			$this->items=$this->get_arrayable_items($items);
		}

		// IteratorAggregate
		public function getIterator()
		{
			return new ArrayIterator($this->items);
		}
		// ArrayAccess
		public function offsetExists($key): bool
		{
			return isset($this->items[$key]);
		}
		public function offsetGet($key)
		{
			return $this->items[$key];
		}
		public function offsetSet($key, $value): void
		{
			if(is_null($key))
			{
				$this->items[]=$value;
				return;
			}

			$this->items[$key]=$value;
		}
		public function offsetUnset($key): void
		{
			unset($this->items[$key]);
		}

		protected function duplicate_comparator($strict)
		{
			if($strict)
				return function($a, $b)
				{
					return ($a === $b);
				};

			return function($a, $b)
			{
				return ($a == $b);
			};
		}
		protected function sort_by_many(array $comparisons=[])
		{
			$items=$this->items;

			uasort($items, function($a, $b) use($comparisons){
				foreach($comparisons as $comparison)
				{
					$comparison=lv_arr_wrap($comparison);
					$prop=$comparison[0];

					$arr_get=lv_arr_get($comparison, 1, true);

					$ascending=(
						($arr_get === true) ||
						($arr_get === 'asc')
					);

					if((!is_string($prop)) && is_callable($prop))
						$result=$prop($a, $b);
					else
					{
						$values=[
							lv_arr_data_get($a, $prop),
							lv_arr_data_get($b, $prop)
						];

						if(!$ascending)
							$values=array_reverse($values);

						$result=($values[0] <=> $values[1]);
					}

					if($result === 0)
						continue;

					return $result;
				}
			});

			return new static($items);
		}

		public function all()
		{
			return $this->items;
		}
		public function avg($callback=null)
		{
			$callback=$this->value_retriever($callback);

			$items=$this
			->	map(function($value) use($callback){
					return $callback($value);
				})
			->	filter(function($value){
					return (!is_null($value));
				});

			$count=$items->count();

			if($count !== 0)
				return ($items->sum()/$count);
		}
		public function chunk(int $size)
		{
			if($size <= 0)
				return new static();

			$chunks=[];

			foreach(array_chunk($this->items, $size, true) as $chunk)
				$chunks[]=new static($chunk);

			return new static($chunks);
		}
		public function chunk_while(callable $callback)
		{
			$chunks=[];

			foreach(
				$this
				->	lazy()
				->	chunk_while($callback)
				->	all()
				as $chunk
			)
				$chunks[]=$chunk->all();

			return new static($chunks);
		}
		public function collapse()
		{
			return new static(lv_arr_collapse($this->items));
		}
		public function combine($values)
		{
			return new static(array_combine(
				$this->all(),
				$this->get_arrayable_items($values)
			));
		}
		public function concat(array $source)
		{
			$result=new static($this);

			foreach($source as $item)
				$result->push($item);

			return $result;
		}
		public function contains($key, string $operator=null, $value=null)
		{
			if(func_num_args() === 1)
			{
				if($this->use_as_callable($key))
				{
					$placeholder=new class(){};
					return ($this->first($key, $placeholder) !== $placeholder);
				}

				return in_array($key, $this->items);
			}

			return $this->contains($this->operator_for_where(...func_get_args()));
		}
		public function contains_one_item()
		{
			return ($this->count() === 1);
		}
		public function contains_strict($key, $value=null)
		{
			if(func_num_args() === 2)
				return $this->contains(function($item) use($key, $value){
					return (lv_arr_data_get($item, $key) === $value);
				});

			if($this->use_as_callable($key))
				return (!is_null($this->first($key)));

			foreach($this->items as $item)
				if($item === $key)
					return true;

			return false;
		}
		public function count(): int
		{
			return count($this->items);
		}
		public function count_by($count_by=null)
		{
			return new static($this
			->	lazy()
			->	count_by($count_by)
			->	all());
		}
		public function cross_join(...$lists)
		{
			return new static(lv_arr_cross_join(
				$this->items,
				...array_map([$this, 'get_arrayable_items'], $lists)
			));
		}
		public function diff($items)
		{
			return new static(array_diff(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function diff_assoc($items)
		{
			return new static(array_diff_assoc(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function diff_assoc_using($items, callable $callback)
		{
			return new static(array_diff_uassoc(
				$this->items,
				$this->get_arrayable_items($items),
				$callback
			));
		}
		public function diff_keys($items)
		{
			return new static(array_diff_key(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function diff_keys_using($items, callable $callback)
		{
			return new static(array_diff_ukey(
				$this->items,
				$this->get_arrayable_items($items),
				$callback
			));
		}
		public function diff_using($items, callable $callback)
		{
			return new static(array_udiff(
				$this->items,
				$this->get_arrayable_items($items),
				$callback
			));
		}
		public function doesnt_contain($key, string $operator=null, $value=null)
		{
			return (!$this->contains(...func_get_args()));
		}
		public function dot()
		{
			return new static(lv_arr_dot($this->all()));
		}
		public function duplicates($callback=null, bool $strict=false)
		{
			$items=$this->map($this->value_retriever($callback));
			$unique_items=$items->unique(null, $strict);
			$compare=$this->duplicate_comparator($strict);
			$duplicates=new static();

			foreach($items->items as $key=>$value)
			{
				if(
					$unique_items->is_not_empty() &&
					$compare($value, $unique_items->first())
				){
					$unique_items->shift();
					continue;
				}

				$duplicates[$key]=$value;
			}

			return $duplicates;
		}
		public function duplicates_strict(callable $callback=null)
		{
			return $this->duplicates($callback, true);
		}
		public function except($keys)
		{
			if(is_null($keys))
				return new static($this->items);

			if($keys instanceof lv_arr_enumerable)
				return new static(lv_arr_except($this->items, $keys->all()));

			if(!is_array($keys))
				$keys=func_get_args();

			return new static(lv_arr_except($this->items, $keys));
		}
		public function filter(callable $callback=null)
		{
			if($callback === null)
				return new static(array_filter($this->items));

			return new static(lv_arr_where($this->items, $callback));
		}
		public function first(callable $callback=null, $default=null)
		{
			return lv_arr_first($this->items, $callback, $default);
		}
		public function first_or_fail($key=null, string $operator=null, $value=null)
		{
			$placeholder=new class(){};
			$filter=$key;

			if(func_num_args() > 1)
				$filter=$this->operator_for_where(...func_get_args());

			$item=$this->first($filter, $placeholder);

			if($item === $placeholder)
				throw new lv_arr_exception('Item not found');

			return $item;
		}
		public function flatten($depth=INF)
		{
			return new static(lv_arr_flatten($this->items, $depth));
		}
		public function flip()
		{
			return new static(array_flip($this->items));
		}
		public function forget($keys)
		{
			foreach($this->get_arrayable_items($keys) as $key)
				$this->offsetUnset($key);

			return $this;
		}
		public function get($key, $default=null)
		{
			if(array_key_exists($key, $this->items))
				return $this->items[$key];

			return lv_arr_value($default);
		}
		public function group_by($group_by, bool $preserve_keys=false)
		{
			if(
				(!$this->use_as_callable($group_by)) &&
				is_array($group_by)
			){
				$next_groups=$group_by;
				$group_by=array_shift($next_groups);
			}

			$group_by=$this->value_retriever($group_by);
			$results=[];

			foreach($this->items as $key=>$value)
			{
				$group_keys=$group_by($value, $key);

				if(!is_array($group_keys))
					$group_keys=[$group_keys];

				foreach($group_keys as $group_key)
				{
					switch(true)
					{
						case is_bool($group_key):
							$group_key=(int)$group_key;
						break;
						case ($group_key instanceof BackedEnum): // PHP8
							$group_key=$group_key->value;
						break;
						case ($group_key instanceof Stringable): // PHP8
							$group_key=(string)$group_key;
					}

					if(!array_key_exists($group_key, $results))
						$results[$group_key]=new static();

					if($preserve_keys)
					{
						$results[$group_key]->offsetSet($key, $value);
						continue;
					}

					$results[$group_key]->offsetSet(null, $value);
				}
			}

			$result=new static($results);

			if(!empty($next_groups))
				return $result->map->group_by($next_groups, $preserve_keys);

			return $result;
		}
		public function has($key)
		{
			$keys=$key;

			if(!is_array($key))
				$keys=func_get_args();

			foreach($keys as $value)
				if(!array_key_exists($value, $this->items))
					return false;

			return true;
		}
		public function has_any($key)
		{
			if($this->is_empty())
				return false;

			$keys=$key;

			if(!is_array($key))
				$keys=func_get_args();

			foreach($keys as $value)
				if($this->has($value))
					return true;

			return false;
		}
		public function implode($value, string $glue=null)
		{
			if($glue === null)
				$glue='';

			if($this->use_as_callable($value))
				return implode($glue, $this->map($value)->all());

			$first=$this->first();

			if(
				is_array($first) ||
				(
					is_object($first) &&
					(!($first instanceof Stringable)) // PHP8
				)
			)
				return implode($glue, $this->pluck($value)->all());

			if(is_string($value))
				return implode($value, $this->items);

			return implode('', $this->items);
		}
		public function intersect($items)
		{
			return new static(array_intersect(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function intersect_assoc($items)
		{
			return new static(array_intersect_assoc(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function intersect_assoc_using($items, callable $callback)
		{
			return new static(array_intersect_uassoc(
				$this->items,
				$this->get_arrayable_items($items),
				$callback
			));
		}
		public function intersect_by_keys($items)
		{
			return new static(array_intersect_key(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function intersect_using($items, callable $callback)
		{
			return new static(array_uintersect(
				$this->items,
				$this->get_arrayable_items($items),
				$callback
			));
		}
		public function is_empty()
		{
			return empty($this->items);
		}
		public function join(string $glue, string $final_glue='')
		{
			if($final_glue === '')
				return $this->implode($glue);

			$count=$this->count();

			if($count === 0)
				return '';
			if($count === 1)
				return $this->last();

			$collection=new static($this->items);
			$final_item=$collection->pop();

			return $collection->implode($glue).$final_glue.$final_item;
		}
		public function key_by($key_by)
		{
			$key_by=$this->value_retriever($key_by);
			$results=[];

			foreach($this->items as $key=>$item)
			{
				$resolved_key=$key_by($item, $key);

				if(is_object($resolved_key))
					$resolved_key=(string)$resolved_key;

				$results[$resolved_key]=$item;
			}

			return new static($results);
		}
		public function keys()
		{
			return new static(array_keys($this->items));
		}
		public function last(callable $callback=null, $default=null)
		{
			return lv_arr_last($this->items, $callback, $default);
		}
		public function lazy()
		{
			return new lv_arr_lazy_collection($this->items);
		}
		public function map(callable $callback)
		{
			return new static(lv_arr_map($this->items, $callback));
		}
		public function map_to_dictionary(callable $callback)
		{
			$dictionary=[];

			foreach($this->items as $key=>$item)
			{
				$pair=$callback($item, $key);
				$key=key($pair);
				$value=reset($pair);

				if(!isset($dictionary[$key]))
					$dictionary[$key]=[];

				$dictionary[$key][]=$value;
			}

			return new static($dictionary);
		}
		public function map_with_keys(callable $callback)
		{
			return new static(lv_arr_map_with_keys($this->items, $callback));
		}
		public function median($key=null)
		{
			$values=$this;

			if(isset($key))
				$values=$this->pluck($key);

			$values
			->	filter(function($item){
					return (!is_null($item));
				})
			->	sort()
			->	values();

			$count=$values->count();

			if($count === 0)
				return null;

			$middle=(int)($count/2);

			if($count % 2)
				return $values->get($middle);

			return (new static([
				$values->get($middle-1),
				$values->get($middle),
			]))->average();
		}
		public function merge($items)
		{
			return new static(array_merge(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function merge_recursive($items)
		{
			return new static(array_merge_recursive(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function mode($key=null)
		{
			if($this->count() === 0)
				return null;

			$collection=$this;

			if(isset($key))
				$collection=$this->pluck($key);

			$counts=new static();

			$collection->each(function($value) use($counts){
				if(isset($counts[$value]))
				{
					$counts[$value]=$counts[$value]+1;
					return;
				}

				$counts[$value]=1;
			});

			$sorted=$counts->sort();
			$highest_value=$sorted->last();

			return $sorted
			->	filter(function($value) use($highest_value){
					return ($value == $highest_value);
				})
			->	sort()
			->	keys()
			->	all();
		}
		public function nth(int $step, int $offset=0)
		{
			$new=[];
			$position=0;

			foreach($this->slice($offset)->items as $item)
			{
				if($position%$step === 0)
					$new[]=$item;

				++$position;
			}

			return new static($new);
		}
		public function only($keys)
		{
			if(is_null($keys))
				return new static($this->items);

			if($keys instanceof lv_arr_enumerable)
				$keys=$keys->all();

			if(!is_array($keys))
				$keys=func_get_args();

			return new static(lv_arr_only($this->items, $keys));
		}
		public function pad(int $size, $value)
		{
			return new static(array_pad($this->items, $size, $value));
		}
		public function pluck($value, string $key=null)
		{
			return new static(lv_arr_pluck($this->items, $value, $key));
		}
		public function pop(int $count=1)
		{
			if($count === 1)
				return array_pop($this->items);

			if($this->is_empty())
				return new static();

			$results=[];
			$collection_count=$this->count();

			foreach(range(
				1,
				min($count, $collection_count)
			) as $item)
				array_push($results, array_pop($this->items));

			return new static($results);
		}
		public function prepend($value, $key=null)
		{
			$this->items=lv_arr_prepend($this->items, ...func_get_args());
			return $this;
		}
		public function pull($key, $default=null)
		{
			return lv_arr_pull($this->items, $key, $default);
		}
		public function push(...$values)
		{
			foreach($values as $value)
				$this->items[]=$value;

			return $this;
		}
		public function put($key, $value)
		{
			$this->offsetSet($key, $value);
			return $this;
		}
		public function random($number=null, bool $preserve_keys=false)
		{
			if(is_null($number))
				return lv_arr_random($this->items);

			if(is_callable($number))
				return new static(lv_arr_random(
					$this->items,
					$number($this),
					$preserve_keys
				));

			return new static(lv_arr_random($this->items, $number, $preserve_keys));
		}
		public function replace($items)
		{
			return new static(array_replace(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function replace_recursive($items)
		{
			return new static(array_replace_recursive(
				$this->items,
				$this->get_arrayable_items($items)
			));
		}
		public function reverse()
		{
			return new static(array_reverse($this->items, true));
		}
		public function search($value, bool $strict=false)
		{
			if(!$this->use_as_callable($value))
				return array_search($value, $this->items, $strict);

			foreach($this->items as $key=>$item)
				if($value($item, $key))
					return $key;

			return false;
		}
		public function select($keys)
		{
			if(is_null($keys))
				return new static($this->items);

			if($keys instanceof lv_arr_enumerable)
				$keys=$keys->all();

			if(!is_array($keys))
				$keys=func_get_args();

			return new static(lv_arr_select($this->items, $keys));
		}
		public function shift(int $count=1)
		{
			if($count === 1)
				return array_shift($this->items);

			if($this->is_empty())
				return new static();

			$results=[];
			$collection_count=$this->count();

			foreach(range(
				1,
				min($count, $collection_count)
			) as $item)
				array_push($results, array_shift($this->items));

			return new static($results);
		}
		public function shuffle()
		{
			return new static(lv_arr_shuffle($this->items));
		}
		public function skip(int $count)
		{
			return $this->slice($count);
		}
		public function skip_until($value)
		{
			return new static($this
			->	lazy()
			->	skip_until($value)
			->	all());
		}
		public function skip_while($value)
		{
			return new static($this
			->	lazy()
			->	skip_while($value)
			->	all());
		}
		public function slice(int $offset, int $length=null)
		{
			return new static(array_slice($this->items, $offset, $length, true));
		}
		public function sliding(int $size=2, int $step=1)
		{
			$chunks=floor(($this->count()-$size)/$step)+1;

			return static::times($chunks, function($number) use($step, $size){
				return ($this->slice(($number-1)*$step, $size));
			});
		}
		public function sole($key=null, string $operator=null, $value=null)
		{
			$filter=$key;

			if(func_num_args() > 1)
				$filter=$this->operator_for_where(...func_get_args());

			$items=$this->unless($filter == null)->filter($filter);
			$count=$items->count();

			if($count === 0)
				throw new lv_arr_exception('Item not found');

			if($count > 1)
				throw new lv_arr_exception($count.' items were found');

			return $items->first();
		}
		public function sort($callback=null)
		{
			$items=$this->items;

			if($callback && is_callable($callback))
			{
				uasort($items, $callback);
				return new static($items);
			}

			if(isset($callback))
			{
				asort($items, $callback);
				return new static($items);
			}

			asort($items, SORT_REGULAR);

			return new static($items);
		}
		public function sort_by($callback, int $options=SORT_REGULAR, bool $descending=false)
		{
			if(is_array($callback) && (!is_callable($callback)))
				return $this->sort_by_many($callback);

			$results=[];
			$callback=$this->value_retriever($callback);

			/*
			 * First we will loop through the items and get the comparator from a callback
			 * function which we were given. Then, we will sort the returned values and
			 * grab all the corresponding values for the sorted keys from this array.
			 */
			foreach($this->items as $key=>$value)
				$results[$key]=$callback($value, $key);

			if($descending)
				arsort($results, $options);
			else
				asort($results, $options);

			/*
			 * Once we have sorted all of the keys in the array, we will loop through them
			 * and grab the corresponding model so we can set the underlying items list
			 * to the sorted version. Then we'll just return the collection instance.
			 */
			foreach(array_keys($results) as $key)
				$results[$key]=$this->items[$key];

			return new static($results);
		}
		public function sort_by_desc($callback, int $options=SORT_REGULAR)
		{
			return $this->sort_by($callback, $options, true);
		}
		public function sort_desc(int $options=SORT_REGULAR)
		{
			$items=$this->items;

			arsort($items, $options);

			return new static($items);
		}
		public function sort_keys(int $options=SORT_REGULAR, bool $descending=false)
		{
			$items=$this->items;

			if($descending)
			{
				krsort($items, $options);
				return new static($items);
			}

			ksort($items, $options);

			return new static($items);
		}
		public function sort_keys_desc(int $options=SORT_REGULAR)
		{
			return $this->sort_keys($options, true);
		}
		public function sort_keys_using(callable $callback)
		{
			$items=$this->items;
			uksort($items, $callback);

			return new static($items);
		}
		public function splice(int $offset, int $length=null, array $replacement=[])
		{
			if(func_num_args() === 1)
				return new static(array_splice($this->items, $offset));

			return new static(array_splice(
				$this->items,
				$offset,
				$length,
				$this->get_arrayable_items($replacement)
			));
		}
		public function split(int $number_of_groups)
		{
			if($this->is_empty())
				return new static();

			$groups=new static();
			$group_size=floor($this->count()/$number_of_groups);
			$remain=$this->count()%$number_of_groups;
			$start=0;

			for($i=0; $i<$number_of_groups; ++$i)
			{
				$size=$group_size;

				if($i < $remain)
					++$size;

				if($size)
				{
					$groups->push(new static(array_slice($this->items, $start, $size)));
					$start+=$size;
				}
			}

			return $groups;
		}
		public function split_in(int $number_of_groups)
		{
			return $this->chunk(ceil($this->count()/$number_of_groups));
		}
		public function take(int $limit)
		{
			if($limit < 0)
				return $this->slice($limit, abs($limit));

			return $this->slice(0, $limit);
		}
		public function take_until($value)
		{
			return new static($this
			->	lazy()
			->	take_until($value)
			->	all());
		}
		public function take_while($value)
		{
			return new static($this
			->	lazy()
			->	take_while($value)
			->	all());
		}
		public function transform(callable $callback)
		{
			$this->items=$this->map($callback)->all();
			return $this;
		}
		public function undot()
		{
			return new static(lv_arr_undot($this->all()));
		}
		public function union($items)
		{
			return new static($this->items+$this->get_arrayable_items($items));
		}
		public function unique($key=null, bool $strict=false)
		{
			if(is_null($key) && ($strict === false))
				return new static(array_unique($this->items, SORT_REGULAR));

			$callback=$this->value_retriever($key);
			$exists=[];

			return $this->reject(function($item, $key) use($callback, $strict, &$exists){
				$id=$callback($item, $key);

				if(in_array($id, $exists, $strict))
					return true;

				$exists[]=$id;
			});
		}
		public function values()
		{
			return new static(array_values($this->items));
		}
		public function zip($items)
		{
			$arrayable_items=array_map(function($items){
				return $this->get_arrayable_items($items);
			}, func_get_args());

			$params=array_merge(
				[
					function()
					{
						return new static(func_get_args());
					},
					$this->items
				],
				$arrayable_items
			);

			return new static(array_map(...$params));
		}
	}
	class lv_arr_lazy_collection implements lv_arr_enumerable
	{
		use lv_arr_enumerates_values;

		/* trait enumerates_values */
		/* { */
			protected function equality($value)
			{
				return function($item) use($value)
				{
					return ($item === $value);
				};
			}
			protected function negate(Closure $callback)
			{
				return function(...$params) use($callback)
				{
					return (!$callback(...$params));
				};
			}
		/* } */

		public $source;

		public static function range(int $from, int $to)
		{
			return new static(function() use($from, $to){
				if($from <= $to)
					for(; $from<=$to; ++$from)
						yield $from;
				else
					for(; $from>=$to; --$from)
						yield $from;
			});
		}

		public function __construct($source=null)
		{
			switch(true)
			{
				case (
					($source instanceof Closure) ||
					($source instanceof self)
				):
					$this->source=$source;
				break;
				case (is_null($source)):
					$this->source=static::empty();
				break;
				case ($source instanceof Generator):
					throw new lv_arr_exception(
						'Generators should not be passed directly to '.static::class.'. Instead, pass a generator function.'
					);
				break;
				default:
					$this->source=$this->get_arrayable_items($source);
			}
		}

		// IteratorAggregate
		public function getIterator()
		{
			return $this->make_iterator($this->source);
		}

		protected function chunk_while_collection()
		{
			return new lv_arr_collection();
		}
		protected function explode_pluck_parameters($value, $key)
		{
			if(is_string($value))
				$value=explode('.', $value);

			if((!is_null($key)) && (!is_array($key)))
				$key=explode('.', $key);

			return [$value, $key];
		}
		protected function make_iterator($source)
		{
			if($source instanceof IteratorAggregate)
				return $source->getIterator();

			if(is_array($source))
				return new ArrayIterator($source);

			if(is_callable($source))
			{
				$maybe_traversable=$source();

				if($maybe_traversable instanceof Traversable)
					return $maybe_traversable;

				return new ArrayIterator(lv_arr_wrap($maybe_traversable));
			}

			return new ArrayIterator((array)$source);
		}
		protected function passthru($method, array $params)
		{
			return new static(function() use($method, $params){
				yield from $this->collect()->$method(...$params)->to_array();
			});
		}

		public function all()
		{
			if(is_array($this->source))
				return $this->source;

			return iterator_to_array($this->get_iterator());
		}
		public function avg($callback=null)
		{
			return $this->collect()->avg($callback);
		}
		public function chunk(int $size)
		{
			if($size <= 0)
				return static::empty();

			return new static(function() use($size){
				$iterator=$this->get_iterator();

				while($iterator->valid())
				{
					$chunk=[];

					while(true)
					{
						$chunk[$iterator->key()]=$iterator->current();

						if(count($chunk) < $size)
						{
							$iterator->next();

							if(!$iterator->valid())
								break;

							continue;
						}

						break;
					}

					yield new static($chunk);

					$iterator->next();
				}
			});
		}
		public function chunk_while(callable $callback)
		{
			return new static(function() use($callback){
				$iterator=$this->get_iterator();

				$chunk=$this->chunk_while_collection();

				if($iterator->valid())
				{
					$chunk[$iterator->key()]=$iterator->current();
					$iterator->next();
				}

				while($iterator->valid())
				{
					if(!$callback(
						$iterator->current(),
						$iterator->key(),
						$chunk)
					){
						yield new static($chunk);
						$chunk=$this->chunk_while_collection();
					}

					$chunk[$iterator->key()]=$iterator->current();
					$iterator->next();
				}

				if($chunk->is_not_empty())
					yield new static($chunk);
			});
		}
		public function collapse()
		{
			return new static(function(){
				foreach($this->source as $values)
					if(is_array($values) || ($values instanceof lv_arr_enumerable))
						foreach($values as $value)
							yield $value;
			});
		}
		public function combine($values)
		{
			return new static(function() use($values){
				$values=$this->make_iterator($values);
				$error_message='Both parameters should have an equal number of elements';

				foreach($this->source as $key)
				{
					if(!$values->valid())
					{
						trigger_error($error_message, E_USER_WARNING);
						break;
					}

					yield $key=>$values->current();

					$values->next();
				}

				if($values->valid())
					trigger_error($error_message, E_USER_WARNING);
			});
		}
		public function concat(array $source)
		{
			return new static(function() use($source){
				yield from $this;
				yield from $source;
			});
		}
		public function contains($key, string $operator=null, $value=null)
		{
			if(
				(func_num_args() === 1) &&
				$this->use_as_callable($key)
			){
				$placeholder=new class(){};

				return ($this->first($key, $placeholder) !== $placeholder);
			}

			if(func_num_args() === 1)
			{
				$needle=$key;

				foreach($this->source as $value)
					if($value == $needle)
						return true;

				return false;
			}

			return $this->contains($this->operator_for_where(...func_get_args()));
		}
		public function contains_one_item()
		{
			return ($this->take(2)->count() === 1);
		}
		public function contains_strict($key, $value=null)
		{
			if(func_num_args() === 2)
				return $this->contains(function($item) use($key, $value){
					return (lv_arr_data_get($item, $key) === $value);
				});

			if($this->use_as_callable($key))
				return (!is_null($this->first($key)));

			foreach($this->source as $item)
				if($item === $key)
					return true;

			return false;
		}
		public function count(): int
		{
			if(is_array($this->source))
				return count($this->source);

			return iterator_count($this->get_iterator());
		}
		public function count_by($count_by=null)
		{
			if(is_null($count_by))
				$count_by=$this->identity();
			else
				$count_by=$this->value_retriever($count_by);

			return new static(function() use($count_by){
				$counts=[];

				foreach($this->source as $key=>$value)
				{
					$group=$count_by($value, $key);

					if(empty($counts[$group]))
						$counts[$group]=0;

					++$counts[$group];
				}

				yield from $counts;
			});
		}
		public function cross_join(...$arrays)
		{
			return $this->passthru('cross_join', func_get_args());
		}
		public function diff($items)
		{
			return $this->passthru('diff', func_get_args());
		}
		public function diff_assoc($items)
		{
			return $this->passthru('diff_assoc', func_get_args());
		}
		public function diff_assoc_using($items, callable $callback)
		{
			return $this->passthru('diff_assoc_using', func_get_args());
		}
		public function diff_keys($items)
		{
			return $this->passthru('diff_keys', func_get_args());
		}
		public function diff_keys_using($items, callable $callback)
		{
			return $this->passthru('diff_keys_using', func_get_args());
		}
		public function diff_using($items, callable $callback)
		{
			return $this->passthru('diff_using', func_get_args());
		}
		public function doesnt_contain($key, string $operator=null, $value=null)
		{
			return (!$this->contains(...func_get_args()));
		}
		public function dot()
		{
			return $this->passthru('dot', []);
		}
		public function duplicates($callback=null, bool $strict=false)
		{
			return $this->passthru('duplicates', func_get_args());
		}
		public function duplicates_strict(callable $callback=null)
		{
			return $this->passthru('duplicates_strict', func_get_args());
		}
		public function eager()
		{
			return new static($this->all());
		}
		public function except($keys)
		{
			return $this->passthru('except', func_get_args());
		}
		public function filter(callable $callback=null)
		{
			if(is_null($callback))
				$callback=function($value)
				{
					return (bool)$value;
				};

			return new static(function() use($callback){
				foreach($this->source as $key=>$value)
					if($callback($value, $key))
						yield $key=>$value;
			});
		}
		public function first(callable $callback=null, $default=null)
		{
			$iterator=$this->get_iterator();

			if(is_null($callback))
			{
				if(!$iterator->valid())
					return lv_arr_value($default);

				return $iterator->current();
			}

			foreach($iterator as $key=>$value)
				if($callback($value, $key))
					return $value;

			return lv_arr_value($default);
		}
		public function first_or_fail($key=null, string $operator=null, $value=null)
		{
			$filter=$key;

			if(func_num_args() > 1)
				$filter=$this->operator_for_where(...func_get_args());

			return $this
			->	unless($filter == null)
			->	filter($filter)
			->	take(1)
			->	collect()
			->	first_or_fail();
		}
		public function flatten($depth=INF)
		{
			return new static(function() use($depth){
				foreach($this->source as $item)
					if(
						(!is_array($item)) &&
						(!($item instanceof lv_arr_enumerable))
					)
						yield $item;
					else if($depth === 1)
						yield from $item;
					else
						yield from (new static($item))->flatten($depth-1);
			});
		}
		public function flip()
		{
			return new static(function(){
				foreach($this->source as $key=>$value)
					yield $value=>$key;
			});
		}
		public function get($key, $default=null)
		{
			if(is_null($key))
				return null;

			foreach($this->source as $outer_key=>$outer_value)
				if($outer_key == $key)
					return $outer_value;

			return lv_arr_value($default);
		}
		public function get_iterator()
		{
			return $this->make_iterator($this->source);
		}
		public function group_by($group_by, bool $preserve_keys=false)
		{
			return $this->passthru('group_by', func_get_args());
		}
		public function has($key)
		{
			if(is_array($key))
				$keys=array_flip($key);
			else
				$keys=array_flip(func_get_args());

			$count=count($keys);

			foreach($this->source as $key=>$value)
				if(
					array_key_exists($key, $keys) &&
					(--$count === 0)
				)
					return true;

			return false;
		}
		public function has_any($key)
		{
			if(is_array($key))
				$keys=array_flip($key);
			else
				$keys=array_flip(func_get_args());

			foreach($this->source as $key=>$value)
				if(array_key_exists($key, $keys))
					return true;

			return false;
		}
		public function implode($value, string $glue=null)
		{
			return $this->collect()->implode(...func_get_args());
		}
		public function intersect($items)
		{
			return $this->passthru('intersect', func_get_args());
		}
		public function intersect_assoc($items)
		{
			return $this->passthru('intersect_assoc', func_get_args());
		}
		public function intersect_assoc_using($items, callable $callback)
		{
			return $this->passthru('intersect_assoc_using', func_get_args());
		}
		public function intersect_by_keys($items)
		{
			return $this->passthru('intersect_by_keys', func_get_args());
		}
		public function intersect_using($items, callable $callback)
		{
			return $this->passthru('intersect_using', func_get_args());
		}
		public function is_empty()
		{
			return (!$this->get_iterator()->valid());
		}
		public function join(string $glue, string $final_glue='')
		{
			return $this->collect()->join(...func_get_args());
		}
		public function key_by($key_by)
		{
			return new static(function() use($key_by){
				$key_by=$this->value_retriever($key_by);

				foreach($this->source as $key=>$item)
				{
					$resolved_key=$key_by($item, $key);

					if(is_object($resolved_key))
						$resolved_key=(string)$resolved_key;

					yield $resolved_key=>$item;
				}
			});
		}
		public function keys()
		{
			return new static(function(){
				foreach($this->source as $key=>$value)
					yield $key;
			});
		}
		public function last(callable $callback=null, $default=null)
		{
			$needle=new class(){};
			$placeholder=$needle;

			foreach($this->source as $key=>$value)
				if(is_null($callback) || $callback($value, $key))
					$needle=$value;

			if($needle === $placeholder)
				return lv_arr_value($default);

			return $needle;
		}
		public function map(callable $callback)
		{
			return new static(function() use($callback){
				foreach($this->source as $key=>$value)
					yield $key=>$callback($value, $key);
			});
		}
		public function map_to_dictionary(callable $callback)
		{
			return $this->passthru('map_to_dictionary', func_get_args());
		}
		public function map_with_keys(callable $callback)
		{
			return new static(function() use($callback){
				foreach($this->source as $key=>$value)
					yield from $callback($value, $key);
			});
		}
		public function median($key=null)
		{
			return $this->collect()->median($key);
		}
		public function merge($items)
		{
			return $this->passthru('merge', func_get_args());
		}
		public function merge_recursive($items)
		{
			return $this->passthru('merge_recursive', func_get_args());
		}
		public function mode($key=null)
		{
			return $this->collect()->mode($key);
		}
		public function nth(int $step, int $offset=0)
		{
			return new static(function() use($step, $offset){
				$position=0;

				foreach($this->slice($offset) as $item)
				{
					if($position%$step === 0)
						yield $item;

					++$position;
				}
			});
		}
		public function only($keys)
		{
			if($keys instanceof lv_arr_enumerable)
				$keys=$keys->all();
			else if(
				(!is_null($keys)) &&
				(!is_array($keys))
			)
				$keys=func_get_args();

			return new static(function() use($keys){
				if(is_null($keys))
					yield from $this;
				else
				{
					$keys=array_flip($keys);

					foreach($this->source as $key=>$value)
						if(array_key_exists($key, $keys))
						{
							yield $key=>$value;

							unset($keys[$key]);

							if(empty($keys))
								break;
						}
				}
			});
		}
		public function pad(int $size, $value)
		{
			if($size < 0)
				return $this->passthru('pad', func_get_args());

			return new static(function() use($size, $value){
				$yielded=0;

				foreach($this->source as $index=>$item)
				{
					yield $index=>$item;
					++$yielded;
				}

				while($yielded++ < $size)
					yield $value;
			});
		}
		public function pluck($value, string $key=null)
		{
			return new static(function() use($value, $key){
				[$value, $key]=$this->explode_pluck_parameters($value, $key);

				foreach($this->source as $item)
				{
					$item_value=lv_arr_data_get($item, $value);

					if(is_null($key))
						yield $item_value;
					else
					{
						$item_key=lv_arr_data_get($item, $key);

						if(
							is_object($item_key) &&
							method_exists($item_key, '__toString')
						)
							$item_key=(string)$item_key;

						yield $item_key=>$item_value;
					}
				}
			});
		}
		public function random($number=null)
		{
			$result=$this->collect()->random(...func_get_args());

			if(is_null($number))
				return $result;

			return new static($result);
		}
		public function replace($items)
		{
			return new static(function() use($items){
				$items=$this->get_arrayable_items($items);

				foreach($this->source as $key=>$value)
					if(array_key_exists($key, $items))
					{
						yield $key=>$items[$key];
						unset($items[$key]);
					}
					else
						yield $key=>$value;

				foreach($items as $key=>$value)
					yield $key=>$value;
			});
		}
		public function replace_recursive($items)
		{
			return $this->passthru('replace_recursive', func_get_args());
		}
		public function remember()
		{
			$iterator=$this->get_iterator();
			$iterator_index=0;
			$cache=[];

			return new static(function() use($iterator, &$iterator_index, &$cache){
				for($index=0; true; ++$index)
				{
					if(array_key_exists($index, $cache))
					{
						yield $cache[$index][0]=>$cache[$index][1];
						continue;
					}

					if($iterator_index < $index)
					{
						$iterator->next();
						++$iterator_index;
					}

					if(!$iterator->valid())
						break;

					$cache[$index]=[$iterator->key(), $iterator->current()];

					yield $cache[$index][0]=>$cache[$index][1];
				}
			});
		}
		public function reverse()
		{
			return $this->passthru('reverse', func_get_args());
		}
		public function search($value, bool $strict=false)
		{
			$predicate=$value;

			if(!$this->use_as_callable($value))
				$predicate=function($item) use($value, $strict)
				{
					if($strict)
						return ($item === $value);

					return ($item == $value);
				};

			foreach($this->source as $key=>$item)
				if($predicate($item, $key))
					return $key;

			return false;
		}
		public function select($keys)
		{
			if($keys instanceof lv_arr_enumerable)
				$keys=$keys->all();
			else if(
				(!is_null($keys)) &&
				(!is_array($keys))
			)
				$keys=func_get_args();

			return new static(function() use($keys){
				if(is_null($keys))
					yield from $this;
				else
					foreach($this->source as $item)
					{
						$result=[];

						foreach($keys as $key)
						{
							if(
								lv_arr_accessible($item) &&
								lv_arr_exists($item, $key)
							){
								$result[$key]=$item[$key];
								continue;
							}

							if(
								is_object($item) &&
								isset($item->$key)
							)
								$result[$key]=$item->$key;
						}

						yield $result;
					}
			});
		}
		public function shuffle()
		{
			return $this->passthru('shuffle', []);
		}
		public function skip(int $count)
		{
			return new static(function() use($count){
				$iterator=$this->get_iterator();

				while($iterator->valid() && $count--)
					$iterator->next();

				while($iterator->valid())
				{
					yield $iterator->key()=>$iterator->current();
					$iterator->next();
				}
			});
		}
		public function skip_until($value)
		{
			if($this->use_as_callable($value))
			{
				$callback=$value;
				return $this->skip_while($this->negate($callback));
			}

			$callback=$this->equality($value);

			return $this->skip_while($this->negate($callback));
		}
		public function skip_while($value)
		{
			$callback=$value;

			if(!$this->use_as_callable($value))
				$callback=$this->equality($value);

			return new static(function() use($callback){
				$iterator=$this->get_iterator();

				while(
					$iterator->valid() &&
					$callback(
						$iterator->current(),
						$iterator->key()
					)
				)
					$iterator->next();

				while($iterator->valid())
				{
					yield $iterator->key()=>$iterator->current();
					$iterator->next();
				}
			});
		}
		public function slice(int $offset, int $length=null)
		{
			if(($offset < 0) || ($length < 0))
				return $this->passthru('slice', func_get_args());

			$instance=$this->skip($offset);

			if(is_null($length))
				return $instance;

			return $instance->take($length);
		}
		public function sliding(int $size=2, int $step=1)
		{
			return new static(function() use($size, $step){
				$iterator=$this->get_iterator();
				$chunk=[];

				while($iterator->valid())
				{
					$chunk[$iterator->key()]=$iterator->current();

					if(count($chunk) == $size)
					{
						yield (new static($chunk))->tap(function() use(&$chunk, $step){
							$chunk=array_slice($chunk, $step, null, true);
						});

						/*
						 * if the $step between chunks is bigger than each chunk's $size
						 * we will skip the extra items (which should never be in any
						 * chunk) before we continue to the next chunk in the loop.
						 */
						if($step > $size)
						{
							$skip=$step-$size;

							for($i=0; $i<$skip && $iterator->valid(); ++$i)
								$iterator->next();
						}
					}

					$iterator->next();
				}
			});
		}
		public function sole($key=null, string $operator=null, $value=null)
		{
			$filter=$key;

			if(func_num_args() > 1)
				$filter=$this->operator_for_where(...func_get_args());

			return $this
			->	unless($filter == null)
			->	filter($filter)
			->	take(2)
			->	collect()
			->	sole();
		}
		public function sort($callback=null)
		{
			return $this->passthru('sort', func_get_args());
		}
		public function sort_by($callback, int $options=SORT_REGULAR, bool $descending=false)
		{
			return $this->passthru('sort_by', func_get_args());
		}
		public function sort_by_desc($callback, int $options=SORT_REGULAR)
		{
			return $this->passthru('sort_by_desc', func_get_args());
		}
		public function sort_desc(int $options=SORT_REGULAR)
		{
			return $this->passthru('sort_desc', func_get_args());
		}
		public function sort_keys(int $options=SORT_REGULAR, bool $descending=false)
		{
			return $this->passthru('sort_keys', func_get_args());
		}
		public function sort_keys_desc(int $options=SORT_REGULAR)
		{
			return $this->passthru('sort_keys_desc', func_get_args());
		}
		public function sort_keys_using(callable $callback)
		{
			return $this->passthru('sort_keys_using', func_get_args());
		}
		public function split(int $number_of_groups)
		{
			return $this->passthru('split', func_get_args());
		}
		public function split_in(int $number_of_groups)
		{
			return $this->chunk(ceil($this->count()/$number_of_groups));
		}
		public function take(int $limit)
		{
			if($limit < 0)
				return new static(function() use($limit){
					$limit=abs($limit);
					$ring_buffer=[];
					$position=0;

					foreach($this->source as $key=>$value)
					{
						$ring_buffer[$position]=[$key, $value];
						$position=($position+1)%$limit;
					}

					for(
						$i=0, $end=min($limit, count($ring_buffer));
						$i<$end;
						++$i
					){
						$pointer=($position+$i)%$limit;
						yield $ring_buffer[$pointer][0]=>$ring_buffer[$pointer][1];
					}
				});

			return new static(function() use($limit){
				$iterator=$this->get_iterator();

				while($limit--)
				{
					if(!$iterator->valid())
						break;

					yield $iterator->key()=>$iterator->current();

					if($limit)
						$iterator->next();
				}
			});
		}
		public function take_until($value)
		{
			$callback=$value;

			if(!$this->use_as_callable($value))
				$callback=$this->equality($value);

			return new static(function() use($callback){
				foreach($this->source as $key=>$item)
				{
					if($callback($item, $key))
						break;

					yield $key=>$item;
				}
			});
		}
		public function take_until_timeout(DateTimeInterface $timeout)
		{
			$timeout=$timeout->getTimestamp();

			return new static(function() use($timeout){
				if(time() >= $timeout)
					return;

				foreach($this as $key=>$value)
				{
					yield $key=>$value;

					if(time() >= $timeout)
						break;
				}
			});
		}
		public function take_while($value)
		{
			$callback=$value;

			if(!$this->use_as_callable($value))
				$callback=$this->equality($value);

			return $this->take_until(function($item, $key) use($callback){
				return (!$callback($item, $key));
			});
		}
		public function tap_each(callable $callback)
		{
			return new static(function() use($callback){
				foreach($this as $key=>$value)
				{
					$callback($value, $key);
					yield $key=>$value;
				}
			});
		}
		public function undot()
		{
			return $this->passthru('undot', []);
		}
		public function union($items)
		{
			return $this->passthru('union', func_get_args());
		}
		public function unique($key=null, bool $strict=false)
		{
			$callback=$this->value_retriever($key);

			return new static(function() use($callback, $strict){
				$exists=[];

				foreach($this->source as $key=>$item)
				{
					$id=$callback($item, $key);

					if(!in_array($id, $exists, $strict))
					{
						yield $key=>$item;
						$exists[]=$id;
					}
				}
			});
		}
		public function values()
		{
			return new static(function(){
				foreach($this->source as $item)
					yield $item;
			});
		}
		public function zip($items)
		{
			$iterables=func_get_args();

			return new static(function() use($iterables){
				$iterators=lv_arr_collection::make($iterables)->map(function($iterable){
					return $this->make_iterator($iterable);
				})->prepend($this->get_iterator());

				while(!empty(array_filter($iterators->contains->valid()->all(), 'strlen')))
				{
					yield new static($iterators->map->current());
					$iterators->each->next();
				}
			});
		}
	}
?>