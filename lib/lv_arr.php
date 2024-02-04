<?php
	/*
	 * Laravel 10 array helpers
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
	 *  lv_arr_collapse()
	 *   collapses an array of arrays into a single array
			$array=lv_arr_collapse([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);
			// [1, 2, 3, 4, 5, 6, 7, 8, 9]
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
	 *  lv_arr_exists()
	 *   checks that the given key exists in the provided array
			$array=['name'=>'John Doe', 'age'=>17];
			$exists=lv_arr_exists($array, 'name'); // true
			$exists=lv_arr_exists($array, 'salary'); // false
	 *  lv_arr_flatten()
	 *   flattens a multi-dimensional array into a single level array
			$flattened=lv_arr_flatten(['name'=>'Joe', 'languages'=>['PHP', 'Ruby']]);
			// ['Joe', 'PHP', 'Ruby']
	 *  lv_arr_forget()
	 *   removes a given key / value pair
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
	 *  lv_arr_pluck()
	 *   retrieves all of the values for a given key from an array
			$array=[
				['developer'=>['id'=>1, 'name'=>'Taylor']],
				['developer'=>['id'=>2, 'name'=>'Abigail']]
			];
			$names=lv_arr_pluck($array, 'developer.name');
			// ['Taylor', 'Abigail']
	 *   You may also specify how you wish the resulting list to be keyed
			$names=lv_arr_pluck($array, 'developer.name', 'developer.id');
			// [1=>'Taylor', 2=>'Abigail']
	 *   warning:
	 *    lv_arr_data_get function is required
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
	 *  lv_arr_set()
	 *   sets a value within a deeply nested array
	 *   using "dot" notation
			$array=['products'=>['desk'=>['price'=>100]]];
			lv_arr_set($array, 'products.desk.price', 200);
			// ['products'=>['desk'=>['price'=>200]]]
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
			//	'products'=>[
			//		['name'=>'Desk 1', 'price'=>100],
			//		['name'=>'Desk 2', 'price'=>200]
			//	]
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
			//	'products'=>[
			//		['name'=>'Desk 1', 'price'=>200],
			//		['name'=>'Desk 2', 'price'=>200]
			//	]
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
			//	'products'=>[
			//		['name'=>'Desk 1'],
			//		['name'=>'Desk 2']
			//	]
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
			});
			// false
	 *   additional arguments may be passed to the value function.
	 *   if the first argument is a closure then
	 *   the additional parameters will be passed to the closure as arguments,
	 *   otherwise they will be ignored
			$result=lv_arr_value(function(string $name){
				return $name;
			}, 'Taylor');
			// 'Taylor'
	 *
	 * Not implemented functions:
	 *  lv_arr_join()
	 *  lv_arr_map_with_keys()
	 *  lv_arr_only()
	 *  lv_arr_shuffle()
	 *  lv_arr_wrap()
	 *
	 * Sources:
	 *  https://laravel.com/docs/10.x/helpers
	 *  https://github.com/illuminate/collections/blob/master/Arr.php
	 *  https://github.com/illuminate/collections/blob/master/helpers.php
	 *  https://github.com/illuminate/collections/blob/master/Collection.php
	 *  https://github.com/illuminate/collections/blob/master/Traits/EnumeratesValues.php
	 * License: MIT
	 */

	class lv_arr_exception extends Exception {}
	function lv_arr_accessible($value)
	{
		return
			is_array($value) ||
			($value instanceof \ArrayAccess);
	}
	function lv_arr_collapse(array $array)
	{
		$results=[];

		foreach($array as $values)
		{
			// removed if($values instanceof Collection)
			if(!is_array($values))
				continue;

			$results[]=$values;
		}

		return array_merge([], ...$results);
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
			if(is_array($value) && (!empty($value)))
				$results=array_merge(
					$results,
					(__METHOD__)($value, $prepend.$key.'.')
				);
			else
				$results[$prepend.$key]=$value;

		return $results;
	}
	function lv_arr_exists($array, $key)
	{
		// removed if($array instanceof Enumerable)
		if($array instanceof \ArrayAccess)
			return $array->offsetExists($key);

		if(!is_array($array))
			throw new lv_arr_exception(__METHOD__.'(): $array is not an ArrayAccess nor array');

		if(is_float($key))
			$key=(string)$key;

		if((!is_int($key)) && (!is_string($key)))
			throw new lv_arr_exception(__METHOD__.'(): $key is not an int nor string');

		return array_key_exists($key, $array);
	}
	function lv_arr_flatten(array $array, float $depth=INF)
	{
		$result=[];

		foreach($array as $item)
			// removed $value=$item instanceof Collection ? :
			if(!is_array($item))
				$result[]=$item;
			else
			{
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
			return;

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
				)
					$array=&$array[$part];
				else
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
			)
				$array=$array[$segment];
			else
				return lv_arr_value($default);
		}

		return $array;
	}
	function lv_arr_has($array, $keys)
	{
		if((!is_array($array)) && (!$array instanceof \ArrayAccess))
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
				)
					$sub_key_array=$sub_key_array[$segment];
				else
					return false;
			}
		}

		return true;
	}
	function lv_arr_has_any($array, $keys)
	{
		if((!is_array($array)) && (!$array instanceof \ArrayAccess))
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
	function lv_arr_key_by(array $array, $key_by)
	{
		$_get_arrayable_items=function($items)
		{
			if(is_array($items))
				return $items;

			// removed if($items instanceof Arrayable)
			// removed if($items instanceof Jsonable)

			if($items instanceof \Enumerable)
				return $items->all();

			if($items instanceof \Traversable)
				return iterator_to_array($items);

			if($items instanceof \JsonSerializable)
				return (array)$items->jsonSerialize();

			if($items instanceof \UnitEnum)
				return [$items];

			return (array)$items;
		};
		$_key_by=function($items, $key_by)
		{
			// valueRetriever ->
				if((!is_string($key_by)) && is_callable($key_by)) // useAsCallable
					$key_by_callback=$key_by;
				else
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

		$items=$_get_arrayable_items($array); // Collection::make($array) // __construct // getArrayableItems
		$items=$_key_by($items, $key_by); // ->keyBy($key_by)->all() // removed __construct // removed getArrayableItems

		return $items;
	}
	function lv_arr_pluck(array $array, $value, $key=null)
	{
		$results=[];

		// explodePluckParameters ->
			if(is_string($value))
				$value=explode('.', $value);

			if(!(is_null($key) || is_array($key)))
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
				$results[]=$item_value;
			else
			{
				$item_key=lv_arr_data_get($item, $key);

				if(
					is_object($item_key) &&
					method_exists($item_key, '__toString')
				)
					$item_key=(string)$item_key;

				$results[$item_key]=$item_value;
			}
		}

		return $results;
	}
	function lv_arr_query(array $array)
	{
		return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
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
	function lv_arr_undot(array $array)
	{
		$results=[];

		foreach($array as $key=>$value)
			lv_arr_set($results, $key, $value);

		return $results;
	}
	function lv_arr_data_fill(&$target, $key, $value)
	{
		if((!is_array($key)) && (!is_string($key)))
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
				// removed if($target instanceof Collection)
				if(!is_iterable($target))
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
			)
				$target=$target[$segment];
			else if(
				is_object($target) &&
				isset($target->$segment)
			)
				$target=$target->$segment;
			else
				return lv_arr_value($default);
		}

		return $target;
	}
	function lv_arr_data_set(&$target, $key, $value, $overwrite=true)
	{
		if(is_array($key))
			$segments=$key;
		else if(is_string($key))
			$segments=explode('.', $key);
		else
			throw new lv_arr_exception(__METHOD__.'(): $key is not an array nor string');

		$segment=array_shift($segments);

		if($segment === '*')
		{
			if(!lv_arr_accessible($target))
				$target=[];

			if($segments)
			{
				foreach($target as &$inner)
					(__METHOD__)($inner, $segments, $value, $overwrite);
			}
			else if($overwrite)
			{
				foreach($target as &$inner)
					$inner=$value;
			}
		}
		else if(lv_arr_accessible($target))
		{
			if($segments)
			{
				if(!lv_arr_exists($target, $segment))
					$target[$segment]=[];

				(__METHOD__)($target[$segment], $segments, $value, $overwrite);
			}
			else if($overwrite || (!lv_arr_exists($target, $segment)))
				$target[$segment]=$value;
		}
		else if(is_object($target))
		{
			if($segments)
			{
				if(!isset($target->$segment))
					$target->$segment=[];

				(__METHOD__)($target->{$segment}, $segments, $value, $overwrite);
			}
			else if($overwrite || (!isset($target->$segment)))
				$target->$segment=$value;
		}
		else
		{
			$target=[];

			if($segments)
				(__METHOD__)($target[$segment], $segments, $value, $overwrite);
			else if($overwrite)
				$target[$segment]=$value;
		}

		return $target;
	}
	function lv_arr_data_forget(&$target, $key)
	{
		if(is_array($key))
			$segments=$key;
		else
			$segments=explode('.', $key);

		$segment=array_shift($segments);

		if(
			($segment === '*') &&
			lv_arr_accessible($target)
		){
			if($segments)
				foreach($target as &$inner)
					(__METHOD__)($inner, $segments);
		}
		else if(lv_arr_accessible($target))
		{
			if($segments && lv_arr_exists($target, $segment))
				(__METHOD__)($target[$segment], $segments);
			else
				lv_arr_forget($target, $segment);
		}
		else if(is_object($target))
		{
			if($segments && isset($target->$segment))
				(__METHOD__)($target->$segment, $segments);
			else if(isset($target->$segment))
				unset($target->$segment);
		}

		return $target;
	}
	function lv_arr_value($value, ...$args)
	{
		if($value instanceof \Closure)
			return $value(...$args);

		return $value;
	}
?>