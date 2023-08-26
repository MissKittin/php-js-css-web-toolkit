<?php
	/*
	 * async_process_builder.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
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
		if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;
	$process_command=PHP_BINARY.' '.$argv[0].' backend-process';
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
		$process->write('test read_char');
		sleep($read_delay);
		echo '  -> out';
			$read_char_error=false;
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
		echo '  -> err';
			$read_char_error=false;
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
		echo '  -> stdin_closed';
			if(!$process->stdin_closed())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		$process->stop();
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
		$process->write('test read_until_char');
		sleep($read_delay);
		echo '  -> out';
			if(trim($process->read_until_char_out(substr(PHP_EOL, -1))) === 'OUT: test read_until_char')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> err';
			if(trim($process->read_until_char_err(substr(PHP_EOL, -1))) === 'ERR: test read_until_char')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> stdin_closed';
			if(!$process->stdin_closed())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		$process->stop();
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
		$process->write('test read_line');
		sleep($read_delay);
		echo '  -> out';
			if($process->read_line_out() === 'OUT: test read_line')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> err';
			if($process->read_line_err() === 'ERR: test read_line')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> stdin_closed';
			if(!$process->stdin_closed())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		$process->stop();
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
		$process->write('test read_bytes');
		sleep($read_delay);
		echo '  -> out';
			if($process->read_bytes_out(19) === substr('OUT: test read_bytes', 0, 19))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> err';
			if($process->read_bytes_err(19) === substr('ERR: test read_bytes', 0, 19))
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> stdin_closed';
			if(!$process->stdin_closed())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		$process->stop();
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
		$process->write('test read_all');
		$process->write('more content');
		$process->write('end');
		sleep($read_delay);
		echo '  -> stdin_closed';
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
		echo '  -> err';
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
		// now the process is stopped
		echo '  -> out';
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
		$process->write('do_getenv');
		sleep($read_delay);
		echo '  -> out';
			if($process->read_line_out() === 'GETENV: GETENV_VALUE')
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		echo '  -> stdin_closed';
			if(!$process->stdin_closed())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}
		$process->stop();
		echo '  -> process_started';
			if(!$process->process_started())
				echo ' [ OK ]'.PHP_EOL;
			else
			{
				echo ' [FAIL]'.PHP_EOL;
				$failed=true;
			}

	if($failed)
		exit(1);
?>