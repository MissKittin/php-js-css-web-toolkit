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
		if(str_replace(["\n", ' '], '', var_export(factorization(2000), true)) === "array(0=>0,1=>0,2=>0,3=>2,)")
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
		if(str_replace(["\n", ' '], '', var_export(prime_factorization(6), true)) === "array(0=>2,1=>3,)")
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
		if(str_replace(["\n", ' '], '', var_export(is_triangle(3, 4, 5), true)) === "array('result'=>true,'area'=>6.0,'perimeter'=>12.0,)")
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='is_triangle(3, 4, 5)';
		}
		if(str_replace(["\n", ' '], '', var_export(is_triangle(1, 30, 100), true)) === "array('result'=>false,)")
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
		if(abs(line_segment_length(0, 0, 3, 4)-5) < 0.00001)
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='line_segment_length(0, 0, 3, 4)';
		}
		if(abs(line_segment_length(0, 0, 6, 8)-10) < 0.00001)
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
		if(str_replace(["\n", ' '], '', var_export(analysis_of_quadratic_function(1, -2, -8), true)) === "array(0=>true,1=>-2.0,2=>4.0,'p'=>1,'q'=>-9,)")
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='analysis_of_quadratic_function(1, -2, -8)';
		}

	echo ' -> Testing find_divisors [SKIP]'.PHP_EOL;
	echo ' -> Testing least_common_multiple [SKIP]'.PHP_EOL;
	echo ' -> Testing factorial [SKIP]'.PHP_EOL;
	echo ' -> Testing power [SKIP]'.PHP_EOL;
	echo ' -> Testing newton_sqrt [SKIP]'.PHP_EOL;
	echo ' -> Testing fibonacci_sequence [SKIP]'.PHP_EOL;
	echo ' -> Testing polynomial [SKIP]'.PHP_EOL;
	echo ' -> Testing numeric_array_min [SKIP]'.PHP_EOL;
	echo ' -> Testing numeric_array_max [SKIP]'.PHP_EOL;
	echo ' -> Testing numeric_array_average [SKIP]'.PHP_EOL;
	echo ' -> Testing tower_of_hanoi [SKIP]'.PHP_EOL;
	echo ' -> Testing amMod [SKIP]'.PHP_EOL;

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
			if(str_replace(["\n", ' '], '', var_export($function([6, 9, 2, 5, 0]), true)) === $result)
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