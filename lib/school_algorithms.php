<?php
	/*
	 * Miscellaneous algorithms from lessons
	 * for historical purposes only
	 *
	 * originally written in C++ in school (~ 2014-2016)
	 * a bit redesigned, cleaned, patched and translated to English
	 * added cache for calculations and PHP micro optimizations
	 *
	 * Numeric functions:
	 *  is_prime_number(int) [returns bool]
	 *  is_perfect_number(int) [returns bool]
	 *  factorization(int) [returns array]
	 *  is_narcissistic(int) [returns bool]
	 *   factorization() required
	 *  find_divisors(int) [returns int_array]
	 *   new function, original source lost
	 *  greatest_common_divisor_iteratively(int, int) [returns int]
	 *  greatest_common_divisor_recursively(int, int) [returns int]
	 *  least_common_multiple(int, int_positive) [returns int]
	 *   new function, original source lost
	 *  prime_factorization(int) [returns array]
	 *  dec2bin(int) [returns int]
	 *  bin2dec(int) [returns int]
	 *  find_associated_number(int) [returns int || bool]
	 *   matura exam - see function body
	 *  factorial(float_number) [returns float]
	 *   originally written in PHP
	 *  power(float_number, int_positive_or_zero_exponent) [returns float]
	 *   new function, I don't remember if it was in class
	 *  newton_sqrt(float_number, int_calculation_precission) [returns float]
	 *   new function, I don't remember if it was in class
	 *  fibonacci_sequence(int_length) [returns int_array]
	 *   new function, original source lost
	 *  polynomial(array_float_polynomial_coefficients, float_polynomial_argument) [returns float]
	 *   new function, Horner's method
	 *   where coefficients array is eg. [-1, 7, -1, 0] for -(x^3)+7(x^2)-x
	 *  numeric_array_min(numeric_array) [returns float]
	 *   new function, original source lost
	 *  numeric_array_max(numeric_array) [returns float]
	 *   new function, original source lost
	 *  numeric_array_average(numeric_array) [returns float]
	 *   new function, original source lost
	 *  tower_of_hanoi(int_number_of_pucks) [returns array(array(int_depth, string_move_puck_from, string_move_puck_to), ...)]
	 *   new function, original source lost
	 *   where int_depth is in reverse order - 1 is in the deepest
	 *  amMod(int_carrier_frequency=500, int_signal_frequency=10, int_number_of_runs=360000) [returns array(array(int_degree, float_carrier, float_signal, float_modulated_signal))]
	 *   my program written in C++ that does amplitude modulation of sinus
	 *   warning: may require a large amount of memory
	 *
	 * String functions:
	 *  is_palindrome(string) [returns bool]
	 *  are_anagrams(string, string) [returns bool]
	 *  count_pattern_matches(pattern_string, string) [returns int]
	 *  caesar_cipher_encrypt(string, int_offset) [returns string]
	 *  caesar_cipher_decrypt(string, int_offset) [returns string]
	 *  morse_code_encrypt(string) [returns string]
	 *  morse_code_decrypt(string) [returns string]
	 *   new function
	 *
	 * Geometric functions:
	 *  is_triangle(float, float, float) [returns array(bool_result, float_area, float_perimeter)]
	 *   returns array(bool_result) if is not a triangle
	 *  line_segment_length(float_x_a, float_y_a, float_x_b, float_y_b) [returns float]
	 *  point_is_on_line_segment(float_point_x, float_point_y, float_line_xa, float_line_ya, float_line_xb, float_line_yb) [returns bool]
	 *   line_segment_length() required
	 *  line_segments_intersect(float_line_a_xa, float_line_a_ya, float_line_a_xb, float_line_a_yb, float_line_b_xa, float_line_b_ya, float_line_b_xb, float_line_b_yb) [returns bool]
	 *   point_is_on_line_segment() required
	 *    line_segment_length() required
	 *  point_is_in_polygon(float_point_x,float_point_y, [[float_point_a_x,float_point_a_y], [float_point_b_x,float_point_b_y], [float_point_c_x,float_point_c_y], [...], [float_point_n_x,float_point_n_y]]) [returns bool]
	 *   where array (3rd parameter) has vertex coordinates and vertices of the polygon can be infinity
	 *   note: if the point lies on the perimeter of the polygon, the function returns false
	 *   line_segments_intersect() required
	 *    point_is_on_line_segment() required
	 *     line_segment_length() required
	 *  analysis_of_quadratic_function(int_a, int_b, int_c) [returns array(bool||x_float, x1_float, x2_float, p_float, q_float)]
	 *   where x_float is true if 2 results exists, false if no result exists in real numbers
	 *    and p and q are vertex coordinates of the parabola's apex (p;q)
	 *   this function originally was written in js
	 *  straight_line_passes_through_point(float_const_a, float_const_b, float_point_x, float_point_y) [returns bool]
	 *   new function
	 *   calculates linear function y=ax+b
	 */

	/*
	 * Sorting algorithms library
	 * for historical purposes only
	 *
	 * originally written in C++ in school (04.11.2015)
	 * a bit redesigned, cleaned, patched and translated to English
	 * added cache for calculations and PHP micro optimizations
	 * also some functions has originally array_size parameter -
	 *  that was automatized by the $array_size=count($array)
	 *
	 * Warning: all sorting functions expects flat array with numbers only!
	 *  eg. from sa_generate_array
	 *
	 * Sorting functions:
	 *  bogo_sort
	 *  bogo_sort_ascending
	 *   new function
	 *  naive_sort
	 *  bubble_sort
	 *  insert_sort
	 *  selection_sort
	 *  merge_sort
	 *  quick_sort
	 *  bucket_sort
	 *  bucket_sort_descending
	 *  bucket_sort_optimized
	 *   new function with smaller memory footprint but slower
	 *
	 * Debugging helper:
	 *  sa_generate_array
	 *   params (ints): array size, int lowest random value, int highest random value
	 *
	 * Fun time:
		// bogo and naive sort are excluded due to very long time
		// you need to include measure_exec_time library
		$array=sa_generate_array(999, 0, 999999999); // or you can $argv[1] instead of 999
		error_log('sa_generate_array completed');
		foreach(['bubble_sort', 'insert_sort', 'selection_sort', 'merge_sort', 'quick_sort', 'bucket_sort', 'bucket_sort_descending', 'bucket_sort_optimized'] as $alg)
		{
			$exec_time=new measure_exec_time_from_here();
			$alg($array);
			error_log($alg.' exec time: '.$exec_time->get_exec_time().' seconds');
			unset($exec_time);
		}
	 */

	/* ** Miscellaneous algorithms ** */

	function is_prime_number(int $number)
	{
		for($i=2; $i<$number; ++$i)
			if($number%$i === 0)
				return false;

		return true;
	}
	function is_perfect_number(int $number)
	{
		$sum=0;

		for($i=1; $i<$number; ++$i)
			if($number%$i === 0)
				$sum+=$i;

		if($sum === $number)
			return true;

		return false;
	}
	function factorization(int $number)
	{
		while($number > 0)
		{
			$output_array[]=$number%10;
			$number/=10;
			$number=(int)$number; // patch
		}

		return $output_array;
	}
	function is_narcissistic(int $number)
	{
		// factorization() required

		$array=factorization($number);
		$array_size=count($array);
		$sum=0;

		for($i=0; $i<$array_size; ++$i)
			$sum+=pow($array[$i], $array_size);

		if($sum === $number)
			return true;

		return false;
	}
	function find_divisors(int $number)
	{
		$result=[];

		$sqrt_number=sqrt($number);
		for($i=1; $i<=$sqrt_number; ++$i)
			if($number%$i === 0)
				$result[]=$i;

		for($i=count($result)-1; $i>=0; --$i)
			if($number/$result[$i] !== $result[$i])
				$result[]=$number/$result[$i];

		return $result;
	}
	function greatest_common_divisor_iteratively(int $number_a, int $number_b)
	{
		while($number_a !== $number_b)
			if($number_a > $number_b)
				$number_a-=$number_b;
			else
				$number_b-=$number_a;

		return $number_a;
	}
	function greatest_common_divisor_recursively(int $number_a, int $number_b)
	{
		if($number_b === 0)
			return $number_a;

		return (__METHOD__)($number_b, $number_a%$number_b);
	}
	function least_common_multiple(int $number_a, int $number_b)
	{
		// patch
		if($number_b < 1)
			throw new Exception('number_b must be positive');

		while($number_b !== 0)
		{
			$helper=$number_b;
			$number_b=$number_a%$number_b;
			$number_a=$helper;
		}

		return $number_a;
	}
	function prime_factorization(int $number)
	{
		for($i=2; $i<=sqrt($number); ++$i)
		{
			while($number%$i === 0)
			{
				$output_array[]=$i;
				$number/=$i;
			}
			if($number === 1)
				break;
		}
		$output_array[]=$number;

		return $output_array;
	}
	function dec2bin(int $number)
	{
		$i=0;
		while($number > 0)
		{
			$bin[]=$number%2;
			$number/=2;
			$number=(int)$number; // patch
			++$i;
		}
		--$i;

		$output_int=''; // patch

		while($i >= 0)
		{
			$output_int.=$bin[$i];
			--$i;
		}

		return (int)$output_int;
	}
	function bin2dec(int $number)
	{
		$number=(string)$number; // patch

		$result=0;
		$scale=1; // ?? "waga"

		for($i=strlen($number)-1; $i>=0; --$i)
		{
			if($number[$i] == 1) // $number[$i] is one-character string
				$result+=$scale;

			$scale*=2;
		}

		return $result;
	}
	function find_associated_number(int $number_a)
	{
		/*
		 * Modifications:
		 *  added cache for divisions
		 *  changed echo result to return result
		 *
		 * (Translated by Google translate)
		 * Two different integers a and b greater than 1 will be called associated, if a sum
		 * all different positive divisors of a less than a is equal to b+1, a sum
		 * all different positive divisors of b less than b equal a+1.
		 * For example, the numbers 140 and 195 are associated because:
		 * a) the divisors of 140 are 1, 2, 4, 5, 7, 10, 14, 20, 28, 35, 70, and their sum is 196=195+1.
		 * b) the factors of 195 are 1, 3, 5, 13, 15, 39, 65, and the sum of these numbers is 141=140+1.
		 * --------------
		 * There is an integer and greater than 1. Arrange and write in the notation of your choice
		 * an algorithm that will find and print the number b associated with a, or the message "NO" if any
		 * the number does not exist.
		 * You can only use the following arithmetic operations in the algorithm notation:
		 * add, subtract, multiply, divide, and calculate the remainder of the division.
		 * Attention:
		 * The number of arithmetic operations will be taken into account when evaluating the algorithm
		 * performed by your algorithm.
		 *
		 * Dwie rozne liczby calkowite a i b wieksze od 1 nazwiemy skojarzonymi, jesli suma
		 * wszystkich roznych dodatnich dzielnikow a mniejszych od a jest rowna b+1, a suma
		 * wszystkich roznych dodatnich dzielnikow b mniejszych od b jest rowna a+1.
		 * Skojarzone sa np. liczby 140 i 195, poniewaz:
		 * a) dzielnikami 140 sa 1, 2, 4, 5, 7, 10, 14, 20, 28, 35, 70, a ich suma wynosi 196=195+1.
		 * b) dzielnikami 195 sa 1, 3, 5, 13, 15, 39, 65, a suma tych liczb rowna jest 141=140+1.
		 * --------------
		 * Dana jest liczba calkowita a wieksza od 1. Uloz i zapisz w wybranej przez siebie notacji
		 * algorytm, ktory znajdzie i wypisze liczbe b skojarzona z a lub komunikat "NIE", jesli taka
		 * liczba nie istnieje.
		 * W zapisie algorytmu mozesz korzystac tylko z nastepujacych operacji arytmetycznych:
		 * dodawania, odejmowania, mnozenia, dzielenia calkowitego i obliczania reszty z dzielenia.
		 * Uwaga:
		 * Przy ocenie algorytmu bedzie brana pod uwage liczba operacji arytmetycznych
		 * wykonywanych przez Twoj algorytm.
		 */

		$number_a_divided=$number_a/2;
		$sum_a=0;
		for($i=1; $i<=$number_a_divided; ++$i)
			if($number_a%$i === 0)
				$sum_a+=$i;

		$number_b=$sum_a-1;
		$number_b_divided=$number_b/2;
		$sum_b=0;
		for($i=1; $i<=$number_b_divided; ++$i)
			if($number_b%$i === 0)
				$sum_b+=$i;

		if($sum_b === $number_a+1)
			return $number_b;

		return false;
	}
	function factorial(float $number)
	{
		$result=1;

		for($i=1; $i<=$number; ++$i)
		{
			$result*=$i;
			//echo $i.'*'; // prints "$i*$i*...*$i"
		}

		return $result;
	}
	function power(float $number, int $exponent)
	{
		// patch
		if($exponent < 0)
			throw new Exception('Exponent must be positive or 0');

		$result=1;

		for($i=0; $i<$exponent; ++$i)
			$result*=$number;

		return $result;
	}
	function newton_sqrt(float $number, int $precission)
	{
		$approx_number=$number*0.5;
		for($i=0; $i<$precission; ++$i)
			$approx_number=0.5*($approx_number+($number/$approx_number));

		return $approx_number;
	}
	function fibonacci_sequence(int $length)
	{
		--$length;
		$a=0;
		$b=1;
		$result[]=$b;

		for($i=0; $i<$length; ++$i)
		{
			$b+=$a;
			$a=$b-$a;
			$result[]=$b;
		}

		return $result;
	}
	function polynomial(array $coefficients, float $argument)
	{
		$degree=count($coefficients);

		$result=$coefficients[0];
		for($i=1; $i<$degree; ++$i)
			$result=$result*$argument+$coefficients[$i];

		return $result;
	}
	function numeric_array_min(array $array)
	{
		$array_size=count($array);
		$min=$array[0];

		for($i=1; $i<$array_size; ++$i)
			if($array[$i] < $min)
				$min=$array[$i];

		return $min;
	}
	function numeric_array_max(array $array)
	{
		$array_size=count($array);
		$max=$array[0];

		for($i=1; $i<$array_size; ++$i)
			if($array[$i] > $max)
				$max=$array[$i];

		return $max;
	}
	function numeric_array_average(array $array)
	{
		$array_size=count($array);
		$sum=0;

		for($i=0; $i<$array_size; ++$i)
			$sum+=$array[$i];

		return $sum/$array_size;
	}
	function tower_of_hanoi(int $pucks, $a='a', $b='b', $c='c')
	{
		$array=[];

		if($pucks > 0)
		{
			$array=array_merge($array, (__METHOD__)($pucks-1, $a, $c, $b));
			$array[]=[$pucks, $a, $c]; // you can echo here
			$array=array_merge($array, (__METHOD__)($pucks-1, $b, $a, $c));
		}

		return $array;
	}
	function amMod(
		int $carrier_frequency=500,
		int $signal_frequency=10,
		int $number_of_runs=360000
	){
		for($phase=0; $phase<=$number_of_runs; ++$phase)
		{
			$phase_in_rad=deg2rad($phase);
			$carrier=sin($phase_in_rad*$carrier_frequency);
			$signal=sin(($phase_in_rad/($number_of_runs/360))*$signal_frequency);

			$array[]=[
				$phase,
				number_format($carrier, 20, '.', ''),
				number_format($signal, 20, '.', ''),
				number_format($carrier*$signal, 20, '.', '')
			];
		}

		return $array;
	}

	function is_palindrome(string $string)
	{
		$size=strlen($string)-1;
		$i=0;

		while($i < $size)
		{
			if($string[$i] !== $string[$size])
				return false;

			++$i;
			--$size;
		}

		return true;
	}
	function are_anagrams(string $string_a, string $string_b)
	{
		$sort_string=function($string)
		{
			$size=strlen($string)-1;

			$change=true;
			while($change)
			{
				$change=false;

				for($i=0; $i<$size; ++$i)
					if($string[$i] > $string[$i+1])
					{
						$change=true;

						$pivot=$string[$i];
						$string[$i]=$string[$i+1];
						$string[$i+1]=$pivot;
					}
			}

			return $string;
		};

		if($string_a === $string_b)
			return true;

		$string_a=$sort_string($string_a);
		$string_b=$sort_string($string_b);

		if($string_a !== $string_b)
			return false;

		return true;
	}
	function count_pattern_matches(string $pattern, string $string)
	{
		$pattern_size=strlen($pattern);
		$string_size=strlen($string);
		$indicator=0;
		$result=0;

		$i=0;
		while($i < $string_size)
		{
			$indicator=0;

			if($string[$i] === $pattern[0])
				++$indicator;

			if($indicator !== 0)
				for($j=1; $j<$pattern_size; ++$j)
				{
					++$i;

					if($string[$i] === $pattern[$j])
						++$indicator;
					else
						--$i;
				}

				if($indicator === $pattern_size)
					++$result;

				++$i;
		}

		return $result;
	}
	function caesar_cipher_encrypt(string $string, int $offset)
	{
		$string=strtolower($string); // patch
		$size=strlen($string);

		$result=''; // patch

		for($i=0; $i<$size; ++$i)
		{
			if($string[$i] === ' ')
				$result.=' ';
			else
			{
				$letter=ord($string[$i]); // patch: added
				$letter+=$offset;
				if($letter > 122)
					$letter-=26;
				$result.=chr($letter); // patch: chr()
			}
		}

		return $result;
	}
	function caesar_cipher_decrypt(string $string, int $offset)
	{
		$string=strtolower($string); // patch
		$size=strlen($string);

		$result=''; // patch

		for($i=0; $i<$size; ++$i)
			if($string[$i] === ' ')
				$result.=' ';
			else
			{
				$letter=ord($string[$i]); // patch: added
				$letter-=$offset;
				if($letter < 97)
					$letter+=26;
				$result.=chr($letter); // patch: chr()
			}

		return $result;
	}
	function morse_code_encrypt(string $string)
	{
		$string=strtolower($string); // patch
		$size=strlen($string);

		$output=''; // patch

		for($i=0; $i<$size; ++$i)
			switch($string[$i])
			{
				case 'a': $output.='.- '; break;
				case 'b': $output.='-... '; break;
				case 'c': $output.='-.-. '; break;
				case 'd': $output.='-.. '; break;
				case 'e': $output.='. '; break;
				case 'f': $output.='..-. '; break;
				case 'g': $output.='--. '; break;
				case 'h': $output.='.... '; break;
				case 'i': $output.='.. '; break;
				case 'j': $output.='.--- '; break;
				case 'k': $output.='-.- '; break;
				case 'l': $output.='.-.. '; break;
				case 'm': $output.='-- '; break;
				case 'n': $output.='.- '; break;
				case 'o': $output.='--- '; break;
				case 'p': $output.='.--. '; break;
				case 'q': $output.='--.- '; break;
				case 'r': $output.='.-. '; break;
				case 's': $output.='... '; break;
				case 't': $output.='- '; break;
				case 'u': $output.='..- '; break;
				case 'v': $output.='...- '; break;
				case 'w': $output.='.-- '; break;
				case 'x': $output.='-..- '; break;
				case 'y': $output.='-.-- '; break;
				case 'z': $output.='..-- '; break;
				case '0': $output.='----- '; break;
				case '1': $output.='.---- '; break;
				case '2': $output.='..---- '; break;
				case '3': $output.='...-- '; break;
				case '4': $output.='....- '; break;
				case '5': $output.='..... '; break;
				case '6': $output.='-.... '; break;
				case '7': $output.='--.... '; break;
				case '8': $output.='---.. '; break;
				case '9': $output.='----. '; break;
				case ' ': $output.='    '; break;
				default: $output.='??? '; // patch
			}

		return substr($output, 0, -1); // patch
	}
	function morse_code_decrypt(string $string)
	{
		$output='';

		foreach(explode('    ', $string) as $word)
		{
			foreach(explode(' ', $word) as $letter)
				switch($letter)
				{
					case '.-': $output.='a'; break;
					case '-...': $output.='b'; break;
					case '-.-.': $output.='c'; break;
					case '-..': $output.='d'; break;
					case '.': $output.='e'; break;
					case '..-.': $output.='f'; break;
					case '--.': $output.='g'; break;
					case '....': $output.='h'; break;
					case '..': $output.='i'; break;
					case '.---': $output.='j'; break;
					case '-.-': $output.='k'; break;
					case '.-..': $output.='l'; break;
					case '--': $output.='m'; break;
					case '.-': $output.='n'; break;
					case '---': $output.='o'; break;
					case '.--.': $output.='p'; break;
					case '--.-': $output.='q'; break;
					case '.-.': $output.='r'; break;
					case '...': $output.='s'; break;
					case '-': $output.='t'; break;
					case '..-': $output.='u'; break;
					case '...-': $output.='v'; break;
					case '.--': $output.='w'; break;
					case '-..-': $output.='x'; break;
					case '-.--': $output.='y'; break;
					case '..--': $output.='z'; break;
					case '-----': $output.='0'; break;
					case '.----': $output.='1'; break;
					case '..---': $output.='2'; break;
					case '...--': $output.='3'; break;
					case '....-': $output.='4'; break;
					case '.....': $output.='5'; break;
					case '-....': $output.='6'; break;
					case '--...': $output.='7'; break;
					case '---..': $output.='8'; break;
					case '----.': $output.='9';
				}

			$output.=' ';
		}

		return substr($output, 0, -1);
	}

	function is_triangle(float $a, float $b, float $c)
	{
		if(
			(($a+$b) > $c) &&
			(($b+$c) > $a) &&
			(($c+$a) > $b)
		){
			$perimeter=$a+$b+$c;
			$height=$perimeter/2;

			return [
				'result'=>true,
				'area'=>sqrt($height*($height-$a)*($height-$b)*($height-$c)),
				'perimeter'=>$perimeter
			];
		}

		return ['result'=>false];
	}
	function line_segment_length(float $xa, float $ya, float $xb, float $yb)
	{
		$x=$xb-$xa;
		$y=$yb-$ya;

		return sqrt(($x*$x) + ($y*$y));
	}
	function point_is_on_line_segment(
		float $point_x,
		float $point_y,
		float $xa,
		float $ya,
		float $xb,
		float $yb
	){
		// line_segment_length() required

		// not moved into if() due to better code readability
		$len_line_segment=line_segment_length($xa, $ya, $xb, $yb); // |AB|
		$len_ls_A_2_point=line_segment_length($xa, $ya, $point_x, $point_y); // A(xa;ya), point(point_x;point_y)
		$len_point_2_ls_B=line_segment_length($point_x, $point_y, $xb, $yb); // B(xb;yb)

		if(abs($len_line_segment-($len_ls_A_2_point+$len_point_2_ls_B)) < 0.01)
			return true;

		return false;
	}
	function line_segments_intersect(
		float $xa,
		float $ya,
		float $xb,
		float $yb,
		float $xc,
		float $yc,
		float $xd,
		float $yd
	){
		// point_is_on_line_segment() required

		$det=function($x, $y, $xa, $ya, $xb, $yb)
		{
			// if point is on straight line
			// not moved into if() due to better code readability
			return ($xb*$y + $xa*$yb + $ya*$x)-($ya*$xb + $yb*$x + $y*$xa);
		};

		// not moved into if() due to better code readability
		$det_1=$det($xa, $ya, $xc, $yc, $xd, $yd);
		$det_2=$det($xb, $yb, $xc, $yc, $xd, $yd);
		$det_3=$det($xc, $yc, $xa, $ya, $xb, $yb);
		$det_4=$det($xd, $yd, $xa, $ya, $xb, $yb);

		if(($det_1*$det_2<0) && ($det_3*$det_4<0))
			return true;

		if(point_is_on_line_segment($xa, $ya, $xc, $yc, $xd, $yd))
			return true;
		if(point_is_on_line_segment($xb, $yb, $xc, $yc, $xd, $yd))
			return true;
		if(point_is_on_line_segment($xc, $yc, $xa, $ya, $xb, $yb))
			return true;
		if(point_is_on_line_segment($xd, $yc, $xa, $ya, $xb, $yb))
			return true;

		return false;
	}
	function point_is_in_polygon(
		float $point_x,
		float $point_y,
		array $vertex_coordinates
	){
		// line_segments_intersect() required

		$vertices=count($vertex_coordinates); // patch

		// coordinates of the other end of the segment
		$pk=0; // x is from coordinates array, y is $point_y
		for($i=0; $i<$vertices; ++$i)
			if($vertex_coordinates[$i][0] > $pk)
				$pk=$vertex_coordinates[$i][0];
		++$pk;

		$vertices-=1; // optimization
		$common_vertices=0;
		for($i=0; $i<$vertices; ++$i)
			if(line_segments_intersect(
				$point_x,
				$point_y,
				$pk,
				$point_y,
				$vertex_coordinates[$i][0],
				$vertex_coordinates[$i][1],
				$vertex_coordinates[$i+1][0],
				$vertex_coordinates[$i+1][1]
			))
				++$common_vertices;

		if($common_vertices%2 === 0)
			return false;

		return true;
	}
	function analysis_of_quadratic_function(int $a, int $b, int $c)
	{
		$delta=($b*$b)-4*$a*$c;
		$delta_sqrt=sqrt($delta); // optimization
		$divider=2*$a; // optimization

		if($delta > 0)
		{
			$x[0]=true; // patch
			$x[1]=(-$b-$delta_sqrt)/$divider;
			$x[2]=(-$b+$delta_sqrt)/$divider;
		}
		else if($delta === 0)
			$x[0]=-$b/$divider;
		else
			$x[0]=false;

		// added by me
		$x['p']=-$b/$divider;
		$x['q']=-$delta/(4*$a);

		return $x;
	}
	function straight_line_passes_through_point(float $a, float $b, float $x, float $y)
	{
		if($y === $a*$x+$b)
			return true;

		return false;
	}

	/* ** Sorting algorithms library ** */

	function sa_generate_array(int $array_size, int $min, int $max)
	{
		for($i=0; $i<$array_size; ++$i)
			$array[]=rand($min, $max);

		return $array;
	}

	function bogo_sort(array $array)
	{
		$array_size_original=count($array);
		$array_size=$array_size_original-1;
		$array_size_tripled=$array_size_original*3;

		$i=0;
		$sorted=false;

		while(!$sorted)
			if($array[$i] < $array[$i+1])
			{
				for($j=0; $j<$array_size_tripled; ++$j)
				{
					$rand_a=rand()%$array_size_original;
					$rand_b=rand()%$array_size_original;

					$pivot=$array[$rand_a];

					$array[$rand_a]=$array[$rand_b];
					$array[$rand_b]=$pivot;
				}

				$i=0;
			}
			else
			{
				++$i;

				if($i === $array_size)
					$sorted=true;
		}

		return $array;
	}
	function bogo_sort_ascending(array $array)
	{
		return array_reverse(bogo_sort($array));
	}
	function naive_sort(array $array)
	{
		$array_size=count($array)-1;

		$i=0;
		do
			if($array[$i] > $array[$i+1])
			{
				$pivot=$array[$i];

				$array[$i]=$array[$i+1];
				$array[$i+1]=$pivot;

				$i=0;
			}
			else
				++$i;
		while($i < $array_size);

		return $array;
	}
	function bubble_sort(array $array)
	{
		$array_size=count($array)-1;

		$change=true;
		while($change)
		{
			$change=false;

			for($i=0; $i<$array_size; ++$i)
				if($array[$i] > $array[$i+1])
				{
					$change=true;

					$pivot=$array[$i];

					$array[$i]=$array[$i+1];
					$array[$i+1]=$pivot;
				}

			--$array_size;
		}

		return $array;
	}
	function insert_sort(array $array)
	{
		$array_size=count($array);

		for($i=1; $i<$array_size; ++$i)
		{
			$j=$i;
			$pivot=$array[$i];

			while(
				($j > 0) && // in PHP $j>0 must be first
				($array[$j-1] > $pivot)
			){
				$array[$j]=$array[$j-1];
				--$j;
			}

			$array[$j]=$pivot;
		}

		return $array;
	}
	function selection_sort(array $array)
	{
		$array_size=count($array);

		for($i=0; $i<$array_size; ++$i)
		{
			$min=$array[$i];
			$number=$i;

			for($j=$i; $j<$array_size; ++$j)
				if($min > $array[$j])
				{
					$min=$array[$j];
					$number=$j;
				}

			$pivot=$array[$i];
			$array[$i]=$array[$number];
			$array[$number]=$pivot;
		}

		return $array;
	}
	function merge_sort(array $array, $array_size=null, $begin=0, $end=null)
	{
		if($array_size === null)
			$array_size=count($array);

		// patch
		if($end === null)
			$end=$array_size-1;

		if($begin < $end)
		{
			$middle=($begin+$end)/2;
			$middle=(int)$middle; // patch

			$array=(__METHOD__)($array, $array_size, $begin, $middle); // left
			$array=(__METHOD__)($array, $array_size, $middle+1, $end); // right

			// this block was independent function:
			//  $array=merge_sort__merge($array, $array_size, $begin, $middle, $end);
			/**/	for($i=$begin; $i<=$end; ++$i)
			/**/		$pivot[$i]=$array[$i];
			/**/
			/**/	$i=$begin;
			/**/	$j=$middle+1;
			/**/	$q=$begin;
			/**/
			/**/	while(($i <= $middle) && ($j <= $end))
			/**/	{
			/**/		if($pivot[$i] < $pivot[$j])
			/**/		{
			/**/			$array[$q]=$pivot[$i];
			/**/			++$i;
			/**/		}
			/**/		else
			/**/		{
			/**/			$array[$q]=$pivot[$j];
			/**/			++$j;
			/**/		}
			/**/
			/**/		++$q;
			/**/	}
			/**/
			/**/	while($i <= $middle)
			/**/	{
			/**/		$array[$q]=$pivot[$i];
			/**/		++$q; ++$i;
			/**/	}
		}

		return $array;
	}
	function quick_sort(array $array, $array_size=null, $begin=0, $end=null)
	{
		if($array_size === null)
			$array_size=count($array);

		// patch
		if($end === null)
			$end=$array_size-1;

		$i=($begin+$end)/2;
		$divider=$array[$i];
		$array[$i]=$array[$end];

		$j=$begin; // patch (was $i=$j=$begin in for loop)
		for($i=$begin; $i<$end; ++$i)
			if($array[$i] < $divider)
			{
				$pivot=$array[$i];
				$array[$i]=$array[$j];
				$array[$j]=$pivot;

				++$j;
			}

		$array[$end]=$array[$j];
		$array[$j]=$divider;

		if($begin < $j-1)
			$array=(__METHOD__)($array, $array_size, $begin, $j-1);
		if($j+1 < $end)
			$array=(__METHOD__)($array, $array_size, $j+1, $end);

		return $array;
	}
	function bucket_sort(array $array)
	{
		$array_size=count($array);

		// patch
		$buckets_range=0;
		for($i=0; $i<$array_size; ++$i)
			if($array[$i] > $buckets_range)
				$buckets_range=$array[$i];

		for($i=0; $i<=$buckets_range; ++$i) // was originally $i<$buckets_range-1 ($buckets_range starts from 1)
			$buckets[$i]=0;

		for($i=0; $i<$array_size; ++$i)
			++$buckets[$array[$i]];

		$index=0;
		for($i=0; $i<=$buckets_range; ++$i)
			for($j=0; $j<$buckets[$i]; ++$j)
			{
				$array[$index]=$i;
				++$index;
			}

		return $array;
	}
	function bucket_sort_descending(array $array)
	{
		/*
		 * Yes, I know that you can array_reverse(bucket_sort())
		 * but I wanted to translate as much code as possible
		 */

		$array_size=count($array);

		// patch
		$buckets_range=0;
		for($i=0; $i<$array_size; ++$i)
			if($array[$i] > $buckets_range)
				$buckets_range=$array[$i];

		for($i=0; $i<=$buckets_range; ++$i) // was originally $i<$buckets_range-1 ($buckets_range starts from 1)
			$buckets[$i]=0;

		for($i=0; $i<$array_size; ++$i)
			++$buckets[$array[$i]];

		$index=0;
		for($i=$buckets_range; $i>=0; --$i)
			for($j=0; $j<$buckets[$i]; ++$j)
			{
				$array[$index]=$i;
				++$index;
			}

		return $array;
	}
	function bucket_sort_optimized(array $array)
	{
		$array_size=count($array);

		$buckets_range=0;
		for($i=0; $i<$array_size; ++$i)
		{
			if($array[$i] > $buckets_range)
				$buckets_range=$array[$i];

			if(isset($buckets[$array[$i]]))
				++$buckets[$array[$i]];
			else
				$buckets[$array[$i]]=1;
		}

		$index=0;
		for($i=0; $i<=$buckets_range; ++$i)
			if(isset($buckets[$i]))
				for($j=0; $j<$buckets[$i]; ++$j)
				{
					$array[$index]=$i;
					++$index;
				}

		return $array;
	}
?>