<?php
	class dotenv_exception extends Exception {}
	class dotenv
	{
		/*
		 * DotEnv proxy
		 *
		 * How it works:
		 *  if the .env file exists, the constructor reads it and loads into the internal registry
		 *   the variable name may contain spaces (not recommended), the values may be:
		 *    example value
		 *    "example value"
		 *    'example value'
		 *   the assign character can be value=variable, value = variable or mixed
		 *   if the value is eg. "example"value", it will be registered as example"value
		 *    but it is not recommended
		 *  getenv() method will search a variable in arrays in the following order:
		 *   internal registry, $_ENV, $_SERVER
		 *   if the use of PHP built-in getenv() is allowed (call_getenv option), this function will be called last
		 *  getenv() method returns string if the variable is defined, or $default_value if not (default: false)
		 *
		 * Limitations:
		 *  variable=$another_variable is not supported - must be ${another_variable}
		 *   and substitute_variables option must be enabled
		 *   also if the another_variable is not defined it will be interpreted as an empty string
		 *  the .env file is parsed from top to bottom - you can't refer to a variable that is defined lower
		 *  you cannot refer to system environment variables from the .env file
		 *
		 * Note:
		 *  variables with HTTP_ prefix and HTTPS variable are not added to the $_SERVER
		 *   regardless of the override_server option
		 *  throws an dotenv_exception on error
		 *
		 * Warning:
		 *  if .env file does not exist, it won't throw an error
		 *
		 * Usage:
			$params=[ // these are the default values
				'call_getenv'=>true, // allow or disallow access to PHP getenv() for getenv method
				'substitute_variables'=>true, // convert ${anothervariable} to "another_value"
				'seed_putenv'=>false, // putenv() variables
				'seed_env'=>false, // add variables to $_ENV
				'seed_server'=>false, // add variables to $_SERVER (see note)
				'override_server'=>false // overwrite values in $_SERVER (see note)
			];

			$env=new dotenv('./.env', $params); // or
			$env=new dotenv('./.env'); // if ./.env does not exist, internal registry will be empty
			$env=new dotenv(); // internal registry will be empty

			echo $env->getenv('my_env', 'default_value'); // default_value is optional (can be anything)
		 *
		 * Example .env file:
			myenv=myval

			# comment
			myenv_b="myval_b"

			myenv_c=${myenv_b}
			#myenv_disabled=something
			myenv_d="myenv_b value is ${myenv_b}."
		 */

		protected $call_getenv;
		protected $env=[];

		public function __construct(
			?string $file=null,
			array $params=[]
		){
			foreach([
				'call_getenv'=>true,
				'substitute_variables'=>true,
				'seed_putenv'=>false,
				'seed_env'=>false,
				'seed_server'=>false,
				'override_server'=>false
			] as $param=>$param_value){
				if(isset($params[$param]))
				{
					if(!is_bool($params[$param]))
						throw new dotenv_exception(
							'The input array parameter '.$param.' is not a boolean'
						);

					continue;
				}

				$params[$param]=$param_value;
			}

			$this->call_getenv=$params['call_getenv'];

			if(
				($file === null) ||
				(!is_file($file))
			)
				return;

			$file_handle=fopen($file, 'r');

			if($file_handle === false)
				return;

			while(!feof($file_handle))
			{
				$line=explode(
					'=',
					fgets($file_handle),
					2
				);
				$line[0]=trim($line[0]);

				if(!isset($line[1]))
					continue;

				$line[1]=trim($line[1]);

				if(
					($line[1] === '') ||
					($line[0][0] === '#')
				)
					continue;

				$line_last_char=substr($line[1], -1);

				if(
					($line[1][0] === '"') &&
					($line_last_char === '"')
				){
					$this->process_variable(
						$line[0], substr($line[1], 1, -1),
						$params, false
					);

					continue;
				}

				if(
					($line[1][0] === '\'') &&
					($line_last_char === '\'')
				){
					$this->process_variable(
						$line[0], substr($line[1], 1, -1),
						$params, true
					);

					continue;
				}

				$this->process_variable(
					$line[0], $line[1],
					$params, false
				);
			}

			fclose($file_handle);
		}

		protected function substitute_variable(
			$name, $value,
			$substitute_variables
		){
			if(!$substitute_variables)
				return $value;

			return preg_replace_callback(
				'/\\${[a-zA-Z_][a-zA-Z0-9_]*}/',
				function($matches)
				{
					$match=substr($matches[0], 2, -1);

					if(isset($this->env[$match]))
						return $this->env[$match];

					return '';
				},
				$value
			);
		}
		protected function seed_php_env(
			$name, $value,
			$putenv, $env, $server,
			$override_server
		){
			if($putenv)
				putenv($name.'='.$value);

			if($env)
				$_ENV[$name]=$value;

			if($server)
			{
				if(
					isset($_SERVER[$name]) &&
					(!$override_server)
				)
					return;

				if(substr($name, 0, 5) === 'HTTP_')
					return;

				if($name === 'HTTPS')
					return;

				$_SERVER[$name]=$value;
			}
		}
		protected function process_variable(
			$name, $value,
			$params, $disable_substitution
		){
			$substitute_variables=$params['substitute_variables'];

			if($disable_substitution)
				$substitute_variables=false;

			$this->env[$name]=$this->substitute_variable(
				$name, $value,
				$substitute_variables
			);

			$this->seed_php_env(
				$name, $this->env[$name],
				$params['seed_putenv'], $params['seed_env'], $params['seed_server'],
				$params['override_server']
			);
		}

		public function getenv(string $variable, $default_value=false)
		{
			if(isset($this->env[$variable]))
				return $this->env[$variable];

			if(isset($_ENV[$variable]))
				return $_ENV[$variable];

			if(isset($_SERVER[$variable]))
				return $_SERVER[$variable];

			if($this->call_getenv)
			{
				$result=getenv($variable);

				if($result !== false)
					return $result;
			}

			return $default_value;
		}
	}
?>