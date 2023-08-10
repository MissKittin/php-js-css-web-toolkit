<?php
	/*
	 * check_var.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *
	 * Warning:
	 *  var_export_contains.php library is required
	 */

	foreach([
		'var_export_contains.php'
	] as $library){
		echo ' -> Including '.$library;
			if(@(include __DIR__.'/../lib/'.$library) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		echo ' [ OK ]'.PHP_EOL;
	}

	echo ' -> Including '.basename(__FILE__);
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Mocking superglobals';
		$_SERVER['argv']=[];
		$_SERVER['argv'][]=basename(__FILE__);
		$_COOKIE=[];
		$_ENV=[];
		$_FILES=[];
		$_GET=[];
		$_POST=[];
		$_REQUEST=[];
		$_SESSION=[];
	echo ' [ OK ]'.PHP_EOL;

	$errors=[];

	echo ' -> Testing check_argv'.PHP_EOL;
	echo '  -> returns true';
		$_SERVER['argv'][]='testarg';
		if(check_argv('testarg'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv returns true failed';
		}
	echo '  -> returns false';
		if(!check_argv('testwrongarg'))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv returns false failed';
		}

	echo ' -> Testing check_argv_param'.PHP_EOL;
	echo '  -> returns string';
		$_SERVER['argv'][]='testargparam=testvalue';
		if(check_argv_param('testargparam', '=') === 'testvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_param returns string failed';
		}
	echo '  -> returns null';
		if(check_argv_param('testwrongargparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_param returns null failed';
		}

	echo ' -> Testing check_argv_param_many'.PHP_EOL;
	echo '  -> returns array(2)';
		$_SERVER['argv'][]='testargparam=testvalue2';
		if(count(check_argv_param_many('testargparam', '=')) === 2)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_param_many returns array(2) failed';
		}
	echo '  -> returns empty';
		if(empty(check_argv_param_many('testwrongarg')))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_param_many returns empty failed';
		}

	echo ' -> Testing check_argv_next_param'.PHP_EOL;
	echo '  -> returns string';
		$_SERVER['argv'][]='testargparamB';
		$_SERVER['argv'][]='testvalueB';
		if(check_argv_next_param('testargparamB') === 'testvalueB')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_next_param returns string failed';
		}
	echo '  -> returns null';
		if(check_argv_next_param('testwrongargparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_param returns null failed';
		}

	echo ' -> Testing check_argv_next_param_many'.PHP_EOL;
	echo '  -> returns array(2)';
		$_SERVER['argv'][]='testargparamB';
		$_SERVER['argv'][]='testvalue2';
		if(count(check_argv_next_param_many('testargparamB', '=')) === 2)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_next_param_many returns array(2) failed';
		}
	echo '  -> returns null';
		if(check_argv_next_param_many('testwrongarg') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argv_next_param_many returns null failed';
		}

	echo ' -> Testing argv2array';
		$_SERVER['argv']=[
			basename(__FILE__),
			'-arg1', 'val1',
			'-arg1', 'val2',
			'-arg2', 'valA',
			'-arg2', 'valB',
			'-arg3'
		];
		if(var_export_contains(argv2array(), "array('-arg1'=>array(0=>'val1',1=>'val2',),'-arg2'=>array(0=>'valA',1=>'valB',),'-arg3'=>array(),)"))
			echo ' [ OK ]';
		else
		{
			echo ' [FAIL]';
			$errors[]='argv2array test1 failed';
		}
		$_SERVER['argv']=[
			basename(__FILE__),
			'-arg1=val1',
			'-arg1=val2',
			'-arg2=valA',
			'-arg2=valB',
			'-arg3'
		];
		if(var_export_contains(argv2array('='), "array('-arg1'=>array(0=>'val1',1=>'val2',),'-arg2'=>array(0=>'valA',1=>'valB',),'-arg3'=>array(),)"))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='argv2array test failed';
		}

	echo ' -> Testing check_argc';
		$_SERVER['argc']=2;
		if(check_argc() === 2)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_argc failed';
		}

	echo ' -> Testing check_cookie'.PHP_EOL;
	echo '  -> returns string';
		$_COOKIE['testcookie']='goodvalue';
		if(check_cookie('testcookie') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_cookie returns string failed';
		}
	echo '  -> returns null';
		if(check_cookie('badcookie') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_cookie returns null failed';
		}

	echo ' -> Testing check_cookie_escaped'.PHP_EOL;
	echo '  -> returns string';
		$_COOKIE['testcookie']='<tag>goodvalue';
		if(check_cookie_escaped('testcookie') === '&lt;tag&gt;goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_cookie_escaped returns string failed';
		}
	echo '  -> returns null';
		if(check_cookie_escaped('badcookie') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_cookie_escaped returns null failed';
		}

	echo ' -> Testing check_env'.PHP_EOL;
	echo '  -> returns string';
		$_ENV['testvar']='goodvalue';
		if(check_env('testvar') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_env returns string failed';
		}
	echo '  -> returns null';
		if(check_env('badval') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_env returns null failed';
		}

	echo ' -> Testing check_files'.PHP_EOL;
	echo '  -> simple'.PHP_EOL;
	echo '   -> returns string';
		$_FILES['testparam']='goodvalue';
		if(check_files('testparam') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_files simple returns string failed';
		}
	echo '   -> returns null';
		if(check_files('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_files simple returns null failed';
		}
	echo '  -> nested'.PHP_EOL;
	echo '   -> returns string';
		$_FILES['testparamB']['nested']='goodvalue';
		if(check_files('testparamB', 'nested') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_files nested returns string failed';
		}
	echo '   -> returns null';
		if(check_files('badparam', 'nested') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_files nested returns null failed';
		}

	echo ' -> Testing check_get'.PHP_EOL;
	echo '  -> returns string';
		$_GET['testparam']='goodvalue';
		if(check_get('testparam') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_get returns string failed';
		}
	echo '  -> returns null';
		if(check_get('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_get returns null failed';
		}

	echo ' -> Testing check_get_escaped'.PHP_EOL;
	echo '  -> returns string';
		$_GET['testparam']='<tag>goodvalue';
		if(check_get_escaped('testparam') === '&lt;tag&gt;goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_get_escaped returns string failed';
		}
	echo '  -> returns null';
		if(check_get_escaped('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_get_escaped returns null failed';
		}

	echo ' -> Testing check_post'.PHP_EOL;
	echo '  -> returns string';
		$_POST['testparam']='goodvalue';
		if(check_post('testparam') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_post returns string failed';
		}
	echo '  -> returns null';
		if(check_post('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_post returns null failed';
		}

	echo ' -> Testing check_post_escaped'.PHP_EOL;
	echo '  -> returns string';
		$_POST['testparam']='<tag>goodvalue';
		if(check_post_escaped('testparam') === '&lt;tag&gt;goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_post_escaped returns string failed';
		}
	echo '  -> returns null';
		if(check_post_escaped('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_post_escaped returns null failed';
		}

	echo ' -> Testing check_request'.PHP_EOL;
	echo '  -> returns string';
		$_REQUEST['testparam']='goodvalue';
		if(check_request('testparam') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_request returns string failed';
		}
	echo '  -> returns null';
		if(check_request('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_request returns null failed';
		}

	echo ' -> Testing check_server'.PHP_EOL;
	echo '  -> simple'.PHP_EOL;
	echo '   -> returns string';
		$_SERVER['testparam']='goodvalue';
		if(check_server('testparam') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_server simple returns string failed';
		}
	echo '   -> returns null';
		if(check_server('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_server simple returns null failed';
		}
	echo '  -> nested'.PHP_EOL;
	echo '   -> returns string';
		$_SERVER['testparamB']['nested']='goodvalue';
		if(check_server('testparamB', 'nested') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_server nested returns string failed';
		}
	echo '   -> returns null';
		if(check_server('badparam', 'nested') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_server nested returns null failed';
		}

	echo ' -> Testing check_session'.PHP_EOL;
	echo '  -> returns string';
		$_SESSION['testparam']='goodvalue';
		if(check_session('testparam') === 'goodvalue')
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_session returns string failed';
		}
	echo '  -> returns null';
		if(check_session('badparam') === null)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$errors[]='check_session returns null failed';
		}

	if(!empty($errors))
	{
		echo PHP_EOL;

		foreach($errors as $error)
			echo $error.PHP_EOL;

		exit(1);
	}
?>