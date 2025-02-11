<?php
	/*
	 * Superclosure
	 *
	 * Classes:
	 *  superclosure
	 *  superclosure_meta
	 *   extension with getters
	 */

	class superclosure_exception extends Exception {}
	class superclosure
	{
		/*
		 * Serializable anonymous functions
		 * Inspired by Jeremy Lindblom's SuperClosure
		 *
		 * Warning:
		 *  for security reasons, it is better to sign or encrypt
		 *   the serialized closure before sending or saving
		 *  restoring static and global variables are not supported
		 *  PHP 7.4.0 or newer is required
		 *  eval() must be allowed
		 *
		 * Note:
		 *  function($arg) use ($var) is supported
		 *  throws an superclosure_exception on error
		 *
		 * Usage:
			$closure=new superclosure(function($arg){
				echo 'My anonymous function: '.$arg;
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
			foreach($data as $data_field)
			{
				if(is_array($data_field))
				{
					extract($data_field);
					continue;
				}

				eval('$this->reflection='.$data_field.';');

				if(!$this->reflection instanceof Closure)
					throw new superclosure_exception('Closure expected in unserialized data');
			}

			$this->reflection=new ReflectionFunction($this->reflection);
		}
	}
	class superclosure_meta extends superclosure
	{
		/*
		 * An extension for superclosure
		 * Read the parameters of an anonymous function
		 *
		 * Usage:
			$closure=new superclosure(function($arg){
				echo 'My anonymous function: '.$arg;
			});
			$closure_body=$closure->get_closure_body();
			$closure_vars=$closure->get_closure_vars();
			$closure->flush(); // free memory
		 */

		protected $sleep_called=false;

		public function __sleep()
		{
			if($this->sleep_called)
				return ['closure_vars', 'closure_body'];

			return parent::{__FUNCTION__}();
		}

		public function flush()
		{
			$this->closure_vars=null;
			$this->closure_body=null;
			$this->sleep_called=false;
		}
		public function get_closure_vars()
		{
			if(!$this->sleep_called)
			{
				$this->__sleep();
				$this->sleep_called=true;
			}

			return $this->closure_vars;
		}
		public function get_closure_body()
		{
			if(!$this->sleep_called)
			{
				$this->__sleep();
				$this->sleep_called=true;
			}

			return $this->closure_body;
		}
	}
?>