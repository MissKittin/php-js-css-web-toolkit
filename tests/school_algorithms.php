<?php
	/*
	 * school_algorithms.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 */

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	function is_float_equal($input, $expected_result)
	{
		if(abs($input-$expected_result) < 0.00001)
			return true;

		return false;
	}
	function var_export_contains($input, $content, $print=false)
	{
		if($print)
			return str_replace(["\n", ' '], '', var_export($input, true));

		if(str_replace(["\n", ' '], '', var_export($input, true)) === $content)
			return true;

		return false;
	}

	$errors=[];

	echo ' -> Testing is_prime_number';
		if(is_prime_number(3))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='is_prime_number(3)';
		}
		if(!is_prime_number(4))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='is_prime_number(4)';
		}

	echo ' -> Testing is_perfect_number';
		if(is_perfect_number(6))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='is_perfect_number(6)';
		}
		if(!is_perfect_number(5))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='is_perfect_number(5)';
		}

	echo ' -> Testing factorization';
		if(var_export_contains(
			factorization(2000),
			"array(0=>0,1=>0,2=>0,3=>2,)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='factorization(2000)';
		}

	echo ' -> Testing is_narcissistic';
		if(is_narcissistic(370))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='is_narcissistic(370)';
		}
		if(!is_narcissistic(10))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='is_narcissistic(10)';
		}

	foreach([
		'greatest_common_divisor_iteratively',
		'greatest_common_divisor_recursively'
	] as $function)
	{
		echo ' -> Testing '.$function;
			if($function(54, 24) === 6)
				echo ' [ OK ]';
			else
			{
				echo ' [FAIL]';
				$errors[]=$function.'(54, 24)';
			}
			if($function(2, 3) === 1)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$function.'(2, 3)';
			}
	}

	echo ' -> Testing prime_factorization';
		if(var_export_contains(
			prime_factorization(6),
			"array(0=>2,1=>3,)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='prime_factorization(6)';
		}

	echo ' -> Testing dec2bin';
		if(dec2bin(23) === 10111)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='dec2bin(23)';
		}

	echo ' -> Testing bin2dec';
		if(bin2dec(10111) === 23)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='bin2dec(10111)';
		}

	echo ' -> Testing find_associated_number';
		if(find_associated_number(140) === 195)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='find_associated_number(140)';
		}
		if(find_associated_number(40) === false)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='find_associated_number(40)';
		}

	echo ' -> Testing is_palindrome';
		if(is_palindrome('kxxk'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='is_palindrome("kxxk")';
		}
		if(!is_palindrome('kxak'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='is_palindrome("kxak")';
		}

	echo ' -> Testing are_anagrams';
		if(are_anagrams('kasar', 'raksa'))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='are_anagrams("kasar", "raksa")';
		}
		if(!are_anagrams('abcd', 'ghijk'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='are_anagrams("abcd", "ghijk")';
		}

	echo ' -> Testing count_pattern_matches';
		if(count_pattern_matches('dupa', 'i dupa, dupa, dupa totlorto valava ailande i dupa') === 4)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='count_pattern_matches("dupa", "i dupa, dupa, dupa totlorto valava ailande i dupa")';
		}

	echo ' -> Testing morse_code_encrypt';
		if(morse_code_encrypt('Ala ma kot&') === '.- .-.. .-     -- .-     -.- --- - ???')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='morse_code_encrypt("Ala ma kot&")';
		}

	echo ' -> Testing morse_code_decrypt';
		if(morse_code_decrypt('.- .-.. .-     -- .-     -.- --- - .-') === 'ala ma kota')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='morse_code_decrypt(".- .-.. .-     -- .-     -.- --- - .-")';
		}

	/*
	 * Charmap:
	 * for($i=63; $i<=123; ++$i) echo $i.' '.chr($i).PHP_EOL;
	 */

	echo ' -> Testing caesar_cipher_encrypt';
		if(caesar_cipher_encrypt('a b z', 2) === 'c d b')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='caesar_cipher_encrypt("a b z", 2)';
		}

	echo ' -> Testing caesar_cipher_decrypt';
		if(caesar_cipher_decrypt('c d b', 2) === 'a b z')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='caesar_cipher_encrypt("c b d", 2)';
		}

	echo ' -> Testing is_triangle';
		if(var_export_contains(
			is_triangle(3, 4, 5),
			"array('result'=>true,'area'=>6.0,'perimeter'=>12.0,)"
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='is_triangle(3, 4, 5)';
		}
		if(var_export_contains(
			is_triangle(1, 30, 100),
			"array('result'=>false,)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='is_triangle(1, 30, 100)';
		}

	echo ' -> Testing straight_line_passes_through_point';
		if(straight_line_passes_through_point(3, -5, 2, 1))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='straight_line_passes_through_point(3, -5, 2, 1)';
		}
		if(!straight_line_passes_through_point(3, -5, 1, 1))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='straight_line_passes_through_point(3, -5, 1, 1)';
		}

	echo ' -> Testing line_segment_length';
		if(is_float_equal(line_segment_length(0,0,3,4), 5))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='line_segment_length(0, 0, 3, 4)';
		}
		if(is_float_equal(line_segment_length(0,0,6,8), 10))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='line_segment_length(0, 0, 6, 8)';
		}

	echo ' -> Testing point_is_on_line_segment';
		if(point_is_on_line_segment(2, 2, 2, 4, 2, -4))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='point_is_on_line_segment(2, 2, 2, 4, 2, -4)';
		}

	echo ' -> Testing line_segments_intersect';
		if(line_segments_intersect(2, 2, -2, -2, -2, 2, 2, -2))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='line_segments_intersect(2, 2, -2, -2, -2, 2, 2, -2)';
		}

	echo ' -> Testing point_is_in_polygon';
		if(point_is_in_polygon(
			1,1,
			[
				[2,2], [2,-2], [-2,-2], [-2,2]
			]
		))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='point_is_in_polygon(1,1, [[2,2], [2,-2], [-2,-2], [-2,2]])';
		}
		if(!point_is_in_polygon(
			-2,0,
			[
				[2,2], [2,-2], [-2,-2], [-2,2]
			]
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='point_is_in_polygon(-2,0, [[2,2], [2,-2], [-2,-2], [-2,2]])';
		}

	echo ' -> Testing analysis_of_quadratic_function';
		if(var_export_contains(
			analysis_of_quadratic_function(1, -2, -8),
			"array(0=>true,1=>-2.0,2=>4.0,'p'=>1,'q'=>-9,)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='analysis_of_quadratic_function(1, -2, -8)';
		}

	echo ' -> Testing find_divisors';
		if(var_export_contains(
			find_divisors(200),
			'array(0=>1,1=>2,2=>4,3=>5,4=>8,5=>10,6=>20,7=>25,8=>40,9=>50,10=>100,11=>200,)'
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='find_divisors(200)';
		}

	echo ' -> Testing least_common_multiple';
		if(least_common_multiple(12, 15) === 3)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='least_common_multiple(12, 15)';
		}

	echo ' -> Testing factorial';
		if(factorial(5) === 120)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='factorial(5)';
		}

	echo ' -> Testing power';
		if(is_float_equal(power(2,4), 16))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='power(2, 4)';
		}

	echo ' -> Testing newton_sqrt';
		if(is_float_equal(newton_sqrt(327, 5), 18.112783842751))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='newton_sqrt(327, 5)';
		}

	echo ' -> Testing fibonacci_sequence';
		if(var_export_contains(
			fibonacci_sequence(10),
			'array(0=>1,1=>1,2=>2,3=>3,4=>5,5=>8,6=>13,7=>21,8=>34,9=>55,)'
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='fibonacci_sequence(10)';
		}

	echo ' -> Testing polynomial';
		if(is_float_equal(polynomial([-1,7,-1,0], 2), 18))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='polynomial([-1,7,-1,0], 2)';
		}

	echo ' -> Testing numeric_array_min';
		if(numeric_array_min([5,2,6,5,76,8,3]) === 2)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='numeric_array_min([5,2,6,5,76,8,3])';
		}

	echo ' -> Testing numeric_array_max';
		if(numeric_array_max([5,2,6,5,76,8,3]) === 76)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='numeric_array_min([5,2,6,5,76,8,3])';
		}

	echo ' -> Testing numeric_array_average';
		if(numeric_array_average([5,2,6,5,76,8,3]) === 15)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='numeric_array_min([5,2,6,5,76,8,3])';
		}

	echo ' -> Testing tower_of_hanoi';
		if(var_export_contains(
			tower_of_hanoi(3),
			"array(0=>array(0=>1,1=>'a',2=>'c',),1=>array(0=>2,1=>'a',2=>'b',),2=>array(0=>1,1=>'c',2=>'b',),3=>array(0=>3,1=>'a',2=>'c',),4=>array(0=>1,1=>'b',2=>'a',),5=>array(0=>2,1=>'b',2=>'c',),6=>array(0=>1,1=>'a',2=>'c',),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='tower_of_hanoi(3)';
		}

	echo ' -> Testing amMod';
		if(var_export_contains(
			amMod(5000000, 500, 2),
			"array(0=>array(0=>0,1=>'0.00000000000000000000',2=>'0.00000000000000000000',3=>'0.00000000000000000000',),1=>array(0=>1,1=>'-0.64278760968261838826',2=>'-0.00000000000016070832',3=>'0.00000000000010330132',),2=>array(0=>2,1=>'-0.98480775301043044223',2=>'-0.00000000000032141665',3=>'0.00000000000031653360',),)"
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='amMod(5000000, 500, 2)';
		}

	echo ' -> Testing sa_generate_array [SKIP]'.PHP_EOL;

	$descending="array(0=>9,1=>6,2=>5,3=>2,4=>0,)";
	$ascending="array(0=>0,1=>2,2=>5,3=>6,4=>9,)";
	foreach([
		'bogo_sort'=>$descending,
		'bogo_sort_ascending'=>$ascending,
		'naive_sort'=>$ascending,
		'bubble_sort'=>$ascending,
		'insert_sort'=>$ascending,
		'selection_sort'=>$ascending,
		'merge_sort'=>$ascending,
		'quick_sort'=>$ascending,
		'bucket_sort'=>$ascending,
		'bucket_sort_descending'=>$descending
	] as $function=>$result){
		echo ' -> Testing '.$function;
			if(var_export_contains($function([6, 9, 2, 5, 0]), $result))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors[]=$function;
			}
	}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.' failed'.PHP_EOL;

		exit(1);
	}
?>