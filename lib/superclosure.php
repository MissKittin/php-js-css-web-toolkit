<?php
	class superclosure
	{
		/*
		 * Serializable anonymous functions
		 * Inspired by Jeremy Lindblom's SuperClosure
		 *
		 * PHP 7.4.0 and newer required
		 *  and eval() must be allowed
		 *
		 * Warning: restoring static and global variables is not supported
		 *
		 * Usage (function($arg) use ($var) is supported):
			$closure=new superclosure(function($arg){
				echo 'My anonymous function: ' . $arg;
			});
			$serialized_closure=serialize($closure);
			$unserialized_closure=unserialize($serialized_closure);
		 *
		 * Sources:
		 *  http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
		 *  http://web.archive.org/web/20190220203506/http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
		 */

		protected $reflection;
		protected $closure_body;
		protected $closure_vars;

		public function __construct(closure $function)
		{
			$this->reflection=new ReflectionFunction($function);
		}
		public function __invoke()
		{
			return $this->reflection->invokeArgs(func_get_args());
		}
		public function __sleep()
		{
			$function_body='';

			$current_file=new SplFileObject($this->reflection->getFilename());
			$current_file->seek($this->reflection->getStartLine()-1);
			$end_line=$this->reflection->getEndLine();
			while($current_file->key() < $end_line)
			{
				$function_body.=$current_file->current();
				$current_file->next();
			}

			$begin=strpos($function_body, 'function');
			$end=strrpos($function_body, '}');
			$this->closure_body=substr($function_body, $begin, $end-$begin+1);

			$this->closure_vars=$this->reflection->getStaticVariables();

			return ['closure_vars', 'closure_body'];
		}
		public function __unserialize($data)
		{
			// I had no better idea
			foreach($data as $data_field)
				if(is_array($data_field))
					extract($data_field);
				else
				{
					eval('$this->reflection='.$data_field.';');
					if(!$this->reflection instanceOf Closure)
						throw new Exception('closure expected in unserialized data');
				}

			$this->reflection=new ReflectionFunction($this->reflection);
		}
	}
?>