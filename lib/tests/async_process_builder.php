<?php
	/*
	 * async_process_builder.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  proc_* and stream_* functions are required
	 */

	if((isset($argv[1])) && ($argv[1] === 'backend-process'))
	{
		$stdin=fopen('php://stdin', 'r');
		$stderr=fopen('php://stderr', 'w');

		do
		{
			$input=trim(fgets($stdin));

			if($input === 'do_getenv')
				echo 'GETENV: '.getenv('GETENV_VARIABLE').PHP_EOL;
			else if($input === 'get_arg')
				echo 'GETARG: '.$argv[2].PHP_EOL;
			else
			{
				echo 'OUT: '.$input.PHP_EOL;
				fwrite($stderr, 'ERR: '.$input.PHP_EOL);
			}
		}
		while($input !== 'end');

		exit();
	}

	echo ' -> Testing proc_open function';
		if(!function_exists('proc_open'))
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
	$caught=[];
	$process_command='"'.PHP_BINARY.'" "'.$argv[0].'" backend-process';
	$read_delay=1; // seconds

	echo ' -> Testing read_char'.PHP_EOL;
		$process=new async_process_builder($process_command);
		$process->start();
		echo '  -> process_started';
			if($process->process_started())
			{
				echo ' [ OK ]'.PHP_EOL;
				echo '  -> get_pid: '.$process->get_pid().PHP_EOL;
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		try {
			$process->write('test read_char');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_char write: '.$error->getMessage();
		}
		sleep($read_delay);
		echo '  -> out';
			$read_char_error=false;
			try {
				foreach(str_split('OUT: test read_char') as $i_char)
					if($process->read_char_out() !== $i_char)
					{
						$read_char_error=true;
						break;
					}
				if($read_char_error)
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_char out: '.$error->getMessage();
			}
		echo '  -> err';
			$read_char_error=false;
			try {
				foreach(str_split('ERR: test read_char') as $i_char)
					if($process->read_char_err() !== $i_char)
					{
						$read_char_error=true;
						break;
					}
				if($read_char_error)
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
				else
					echo ' [ OK ]'.PHP_EOL;
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_char err: '.$error->getMessage();
			}
		echo '  -> stdin_closed';
			try {
				if(!$process->stdin_closed())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_char stdin_closed: '.$error->getMessage();
			}
		try {
			$process->stop();
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_char stop: '.$error->getMessage();
		}
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing read_until_char'.PHP_EOL;
		$process=new async_process_builder($process_command);
		$process->start();
		echo '  -> process_started';
			if($process->process_started())
			{
				echo ' [ OK ]'.PHP_EOL;
				echo '  -> get_pid: '.$process->get_pid().PHP_EOL;
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		try {
			$process->write('test read_until_char');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_until_char write: '.$error->getMessage();
		}
		sleep($read_delay);
		echo '  -> out';
			try {
				if(trim($process->read_until_char_out(substr(PHP_EOL, -1))) === 'OUT: test read_until_char')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_until_char out: '.$error->getMessage();
			}
		echo '  -> err';
			try {
				if(trim($process->read_until_char_err(substr(PHP_EOL, -1))) === 'ERR: test read_until_char')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_until_char err: '.$error->getMessage();
			}
		echo '  -> stdin_closed';
			try {
				if(!$process->stdin_closed())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_until_char stdin_closed: '.$error->getMessage();
			}
		try {
			$process->stop();
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_until_char stop: '.$error->getMessage();
		}
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing read_line'.PHP_EOL;
		$process=new async_process_builder($process_command);
		$process->start();
		echo '  -> process_started';
			if($process->process_started())
			{
				echo ' [ OK ]'.PHP_EOL;
				echo '  -> get_pid: '.$process->get_pid().PHP_EOL;
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		try {
			$process->write('test read_line');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_line write: '.$error->getMessage();
		}
		sleep($read_delay);
		echo '  -> out';
			try {
				if($process->read_line_out() === 'OUT: test read_line')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_line out: '.$error->getMessage();
			}
		echo '  -> err';
			try {
				if($process->read_line_err() === 'ERR: test read_line')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_line err: '.$error->getMessage();
			}
		echo '  -> stdin_closed';
			try {
				if(!$process->stdin_closed())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_line stdin_closed: '.$error->getMessage();
			}
		try {
			$process->stop();
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_line stop: '.$error->getMessage();
		}
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing read_bytes'.PHP_EOL;
		$process=new async_process_builder($process_command);
		$process->start();
		echo '  -> process_started';
			if($process->process_started())
			{
				echo ' [ OK ]'.PHP_EOL;
				echo '  -> get_pid: '.$process->get_pid().PHP_EOL;
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		try {
			$process->write('test read_bytes');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_bytes write: '.$error->getMessage();
		}
		sleep($read_delay);
		echo '  -> out';
			try {
				if($process->read_bytes_out(19) === substr('OUT: test read_bytes', 0, 19))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_bytes out: '.$error->getMessage();
			}
		echo '  -> err';
			try {
				if($process->read_bytes_err(19) === substr('ERR: test read_bytes', 0, 19))
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_bytes err: '.$error->getMessage();
			}
		echo '  -> stdin_closed';
			try {
				if(!$process->stdin_closed())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_bytes stdin_closed: '.$error->getMessage();
			}
		try {
			$process->stop();
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_bytes stop: '.$error->getMessage();
		}
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing read_all'.PHP_EOL;
		$process=new async_process_builder($process_command);
		$process->start();
		echo '  -> process_started';
			if($process->process_started())
			{
				echo ' [ OK ]'.PHP_EOL;
				echo '  -> get_pid: '.$process->get_pid().PHP_EOL;
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		try {
			$process->write('test read_all');
			$process->write('more content');
			$process->write('end');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='read_all write: '.$error->getMessage();
		}
		sleep($read_delay);
		echo '  -> stdin_closed';
			try {
				if(!$process->stdin_closed())
					echo ' [ OK ]';
				else
				{
					echo ' [FAIL]';
					$failed=true;
				}
				$process->close_stdin();
				if($process->stdin_closed())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_all stdin_closed: '.$error->getMessage();
			}
		echo '  -> err';
			try {
				if(
					trim($process->read_all_err())
					===
					'ERR: test read_all'.PHP_EOL
					.'ERR: more content'.PHP_EOL
					.'ERR: end'
				)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_all err: '.$error->getMessage();
			}
		// now the process is stopped
		echo '  -> out';
			try {
				if(
					trim($process->read_all_out())
					===
					'OUT: test read_all'.PHP_EOL
					.'OUT: more content'.PHP_EOL
					.'OUT: end'
				)
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='read_all out: '.$error->getMessage();
			}
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing setenv/read_line'.PHP_EOL;
		$process=new async_process_builder($process_command, false);
		$process->setenv('GETENV_VARIABLE', 'GETENV_VALUE');
		echo '  -> getenv';
			if($process->getenv('GETENV_VARIABLE') === 'GETENV_VALUE')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		$process->start();
		echo '  -> process_started';
			if($process->process_started())
			{
				echo ' [ OK ]'.PHP_EOL;
				echo '  -> get_pid: '.$process->get_pid().PHP_EOL;
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		try {
			$process->write('do_getenv');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='setenv/read_line write: '.$error->getMessage();
		}
		sleep($read_delay);
		echo '  -> out';
			try {
				if($process->read_line_out() === 'GETENV: GETENV_VALUE')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='setenv/read_line out: '.$error->getMessage();
			}
		echo '  -> stdin_closed';
			try {
				if(!$process->stdin_closed())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='setenv/read_line stdin_closed: '.$error->getMessage();
			}
		try {
			$process->stop();
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='setenv/read_line stop: '.$error->getMessage();
		}
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	echo ' -> Testing arg/read_line'.PHP_EOL;
		$process=new async_process_builder($process_command, false);
		$process->arg('--testarg=value');
		echo '  -> get_args';
			if(
				($process->get_args()[0] === '\'--testarg=value\'') || // *nix
				($process->get_args()[0] === '"--testarg=value"') // windows
			)
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		$process->start();
		echo '  -> process_started';
			if($process->process_started())
			{
				echo ' [ OK ]'.PHP_EOL;
				echo '  -> get_pid: '.$process->get_pid().PHP_EOL;
			}
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		try {
			$process->write('get_arg');
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='arg/read_line write: '.$error->getMessage();
		}
		sleep($read_delay);
		echo '  -> out';
			try {
				if($process->read_line_out() === 'GETARG: --testarg=value')
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='arg/read_line out: '.$error->getMessage();
			}
		echo '  -> stdin_closed';
			try {
				if(!$process->stdin_closed())
					echo ' [ OK ]'.PHP_EOL;
				else
				{
					echo ' [FAIL]'.PHP_EOL;
					$failed=true;
				}
			} catch(Throwable $error) {
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
				$caught[]='arg/read_line stdin_closed: '.$error->getMessage();
			}
		try {
			$process->stop();
		} catch(Throwable $error) {
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
			$caught[]='arg/read_line stop: '.$error->getMessage();
		}
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
	{
		if(!empty($caught))
		{
			echo PHP_EOL;

			foreach($caught as $caught_note)
				echo $caught_note.PHP_EOL;
		}

		exit(1);
	}
?>