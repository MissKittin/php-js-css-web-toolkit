<?php
	class dotenv
	{
		/*
		 * DotEnv proxy
		 *
		 * How it works:
		 *  If the .env file exists, the constructor reads it and loads into the internal registry.
		 *   The variable name may contain spaces (not recommended), the values may be:
		 *    example value
		 *    "example value"
		 *    'example value'
		 *   The assign character can be value=variable, value = variable or mixed.
		 *   If the value is eg. "example"value", it will be registered as example"value
		 *    but it is not recommended.
		 *  getenv() will search a variable in arrays in the following order:
		 *   internal registry, $_ENV, $_SERVER
		 *  If the use of PHP built-in getenv() is allowed, this function will be called last.
		 *  getenv() method returns string if the variable is defined, or default_value=false if not.
		 *  Always use getenv() method.
		 *
		 * Limitations:
		 *  variable=${another_variable} is not supported - ${another_variable} won't be evaluated
		 *
		 * Usage:
			//$env=new dotenv('./.env', false); // false means that PHP getenv() is not allowed, true is default
			$env=new dotenv('./.env'); // if is "new dotenv()" or ./.env not exists, internal registry will be empty"
			echo $env->getenv('my_env', 'default_value'); // default_value is optional
		 *
		 * Example .env file:
			myenv=myval
			myenv_b="myval_b"
		 */

		protected $call_getenv;
		protected $env=[];

		public function __construct(
			?string $file=null,
			bool $call_getenv=true
		){
			$this->call_getenv=$call_getenv;

			if(($file !== null) && file_exists($file))
			{
				$file=fopen($file, 'r');

				while(!feof($file))
				{
					$line=fgets($file);
					$line=explode('=', $line);
					$line[0]=trim($line[0]);

					if(isset($line[1]))
					{
						$line[1]=trim($line[1]);

						if(
							($line[1] !== '') &&
							($line[0][0] !== '#')
						){
							$line_last_char=substr($line[1], -1);

							if(
								(
									($line[1][0] === '"') &&
									($line_last_char === '"')
								) ||
								(
									($line[1][0] === '\'') &&
									($line_last_char === '\'')
								)
							){
								$this->env[$line[0]]=substr($line[1], 1, -1);
								continue;
							}

							$this->env[$line[0]]=$line[1];
						}
					}
				}

				fclose($file);
			}
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