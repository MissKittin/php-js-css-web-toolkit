<?php
	/*
	 * check_date.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  has_php_close_tag.php library is required
	 *  include_into_namespace.php library is required
	 */

	namespace Test
	{
		function _include_tested_library($namespace, $file)
		{
			if(!is_file($file))
				return false;

			$code=file_get_contents($file);

			if($code === false)
				return false;

			include_into_namespace($namespace, $code, has_php_close_tag($code));

			return true;
		}

		echo ' -> Mocking functions';
			function date($param)
			{
				switch($param)
				{
					case 'Y':
						return $GLOBALS['mocked_date']['Y'];
					case 'm':
						return $GLOBALS['mocked_date']['m'];
					case 'd':
						return $GLOBALS['mocked_date']['d'];
					case 'Y-m-d':
						return
							$GLOBALS['mocked_date']['Y']
							.'-'.
							$GLOBALS['mocked_date']['m']
							.'-'.
							$GLOBALS['mocked_date']['d']
						;
				}
			}
		echo ' [ OK ]'.PHP_EOL;

		foreach(['has_php_close_tag.php', 'include_into_namespace.php'] as $library)
		{
			echo ' -> Including '.$library;
				if(@(include __DIR__.'/../lib/'.$library) === false)
				{
					echo ' [FAIL]'.PHP_EOL;
					exit(1);
				}
			echo ' [ OK ]'.PHP_EOL;
		}

		echo ' -> Including '.basename(__FILE__);
			if(_include_tested_library(
				__NAMESPACE__,
				__DIR__.'/../lib/'.basename(__FILE__)
			))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}

		$errors=[];

		echo ' -> Generating check_easter cache'.PHP_EOL;
			$GLOBALS['mocked_date']['Y']='2021';
			$check_easter_cache=check_easter__make_cache();

		echo ' -> Testing check_date'.PHP_EOL;
		echo '  -> one year'.PHP_EOL;
		echo '   -> returns true';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='05';
			$GLOBALS['mocked_date']['d']='10';
			if(check_date(20,4, 27,8))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => one year'][]='returns true failed';
			}
		echo '   -> returns false';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='03';
			$GLOBALS['mocked_date']['d']='10';
			if(!check_date(20,4, 27,8))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => one year'][]='returns false failed';
			}
		echo '   -> one day before';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='19';
			if(!check_date(20,4, 27,8))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => one year'][]='one day before failed';
			}
		echo '   -> first day';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='20';
			if(check_date(20,4, 27,8))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => one year'][]='first day failed';
			}
		echo '   -> last day';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='08';
			$GLOBALS['mocked_date']['d']='27';
			if(check_date(20,4, 27,8))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => one year'][]='last day failed';
			}
		echo '   -> one day after';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='08';
			$GLOBALS['mocked_date']['d']='28';
			if(!check_date(20,4, 27,8))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => one year'][]='one day after failed';
			}
		echo '  -> between years'.PHP_EOL;
		echo '   -> returns true';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='08';
			$GLOBALS['mocked_date']['d']='10';
			if(check_date(24,6, 14,2))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => between years'][]='returns true failed';
			}
		echo '   -> returns false';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='05';
			$GLOBALS['mocked_date']['d']='10';
			if(!check_date(24,6, 14,2))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => between years'][]='returns false failed';
			}
		echo '   -> one day before';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='06';
			$GLOBALS['mocked_date']['d']='23';
			if(!check_date(24,6, 14,2))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => between years'][]='one day before failed';
			}
		echo '   -> first day';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='06';
			$GLOBALS['mocked_date']['d']='24';
			if(check_date(24,6, 14,2))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => between years'][]='first day failed';
			}
		echo '   -> last day';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='02';
			$GLOBALS['mocked_date']['d']='14';
			if(check_date(24,6, 14,2))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => between years'][]='last day failed';
			}
		echo '   -> one day after';
			$GLOBALS['mocked_date']['Y']=\date('Y');
			$GLOBALS['mocked_date']['m']='02';
			$GLOBALS['mocked_date']['d']='15';
			if(!check_date(24,6, 14,2))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_date => between years'][]='one day after failed';
			}

		echo ' -> Testing check_easter'.PHP_EOL;
		echo '  -> returns true';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='22';
			if(check_easter(49))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter'][]='returns true failed';
			}
		echo '  -> returns false';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='16';
			if(!check_easter(49))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter'][]='returns false failed';
			}
		echo '  -> one day before';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='16';
			if(!check_easter(49))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter'][]='one day before failed';
			}
		echo '  -> first day';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='17';
			if(check_easter(49))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter'][]='first day failed';
			}
		echo '  -> last day';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='06';
			$GLOBALS['mocked_date']['d']='05';
			if(check_easter(49))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter'][]='last day failed';
			}
		echo '  -> one day after';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='06';
			$GLOBALS['mocked_date']['d']='06';
			if(!check_easter(49))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter'][]='one day after failed';
			}

		echo ' -> Testing check_easter_cache'.PHP_EOL;
		echo '  -> returns true';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='22';
			if(check_easter_cache(49, $check_easter_cache))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter_cache'][]='returns true failed';
			}
		echo '  -> returns false';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='16';
			if(!check_easter_cache(49, $check_easter_cache))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter_cache'][]='returns false failed';
			}
		echo '  -> one day before';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='16';
			if(!check_easter_cache(49, $check_easter_cache))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter_cache'][]='one day before failed';
			}
		echo '  -> first day';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='04';
			$GLOBALS['mocked_date']['d']='17';
			if(check_easter_cache(49, $check_easter_cache))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter_cache'][]='first day failed';
			}
		echo '  -> last day';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='06';
			$GLOBALS['mocked_date']['d']='05';
			if(check_easter_cache(49, $check_easter_cache))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter_cache'][]='last day failed';
			}
		echo '  -> one day after';
			$GLOBALS['mocked_date']['Y']='2022';
			$GLOBALS['mocked_date']['m']='06';
			$GLOBALS['mocked_date']['d']='06';
			if(!check_easter_cache(49, $check_easter_cache))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$errors['check_easter_cache'][]='one day after failed';
			}

		if(!empty($errors))
		{
			echo PHP_EOL;

			foreach($errors as $error_segment=>$error_content_array)
				foreach($error_content_array as $error_content)
					echo $error_segment.': '.$error_content.PHP_EOL;

			exit(1);
		}
	}
?>