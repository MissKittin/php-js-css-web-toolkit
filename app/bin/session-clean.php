<?php
	/*
	 * Remove stale sessions
	 *
	 * Warning:
	 *  $_gc_maxlifetime is reserved
	 *  $_SESSION_CLEAN_LOG is reserved
	 *  $_sessions_dir is reserved
	 *  $_SESSION_CLEAN_TOOL is reserved
	 *  $_sessions_dir is reserved
	 *  $_session_file is reserved
	 *
	 * Note:
	 *  you can execute this tool from application
	 *   but it is better to run it from cron
	 *  if $_sessions_dir is not defined,
	 *   $_SESSION_CLEAN_TOOL will be set
	 *   and ./app/lib/samples/session_start.php will be included
	 *
	 * Log callback (not for cli):
	 *  after the session file has been deleted,
	 *  the $_SESSION_CLEAN_LOG function will be called,
	 *  where you can add a log entry about the session file deletion
	 */

	$_gc_maxlifetime=(int)ini_get('session.gc_maxlifetime');

	if(php_sapi_name() === 'cli')
	{
		chdir(__DIR__.'/../..');

		$_SESSION_CLEAN_LOG=function($file)
		{
			echo 'Removed '.$file.PHP_EOL;
		};
	}

	if(!isset($_sessions_dir))
	{
		$_SESSION_CLEAN_TOOL=true;
		include './app/shared/samples/session_start.php';
		unset($_SESSION_CLEAN_TOOL);
	}

	if(is_dir($_sessions_dir))
		foreach(new directoryIterator($_sessions_dir) as $_session_file)
		{
			if(!$_session_file->isFile())
				continue;

			if(substr($_session_file->getFilename(), 0, 5) !== 'sess_')
				continue;

			if(time()-$_session_file->getMTime() >= $_gc_maxlifetime)
			{
				unlink($_session_file->getPathname());

				if(isset($_SESSION_CLEAN_LOG))
					$_SESSION_CLEAN_LOG($_session_file->getFilename());
			}
		}
	else
		if(php_sapi_name() === 'cli')
		{
			echo $_sessions_dir.' is not a directory'.PHP_EOL;
			exit(1);
		}

	unset($_gc_maxlifetime);
	unset($_sessions_dir);
	unset($_session_file);
?>