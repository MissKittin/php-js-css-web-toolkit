<?php
	/*
	 * Observer design pattern
	 *
	 * Note:
	 *  observer class can be inherited or use t_observer trait
	 *  throws an observer_exception on error
	 *
	 * Example usage:
		class logger implements i_observer
		{
			public function update($observer)
			{
				echo __CLASS__.' update(): '.$observer->get_content().PHP_EOL;
			}
		}
		class rss implements i_observer
		{
			public function update($observer)
			{
				echo __CLASS__.' update(): '.$observer->get_content().PHP_EOL;
			}
		}
		class article implements i_observer
		{
			public function update($observer)
			{
				echo __CLASS__.' update(): '.$observer->get_content().PHP_EOL;
			}
		}

		class my_observer extends observer
		{
			// note: you can use t_observer trait instead of observer class

			private $content;

			public function add($content)
			{
				echo __CLASS__.' add(): '.$content.PHP_EOL;
				$this->content=$content;
				$this->notify();
			}
			public function get_content()
			{
				return $this->content;
			}
		}

		$logger_object=new logger();
		$my_observer_object=new my_observer();

		$my_observer_object
		->	attach(new article())
		->	attach(new rss())
		->	attach($logger_object);

		$my_observer_object->add('New article content');
		$my_observer_object->detach($logger_object);
		$my_observer_object->add('Second article content');
	 */

	class observer_exception extends Exception {}

	interface i_observer
	{
		public function update($observer);
	}

	trait t_observer
	{
		protected $observers=[];

		public function attach(i_observer $observer)
		{
			$object_hash=spl_object_hash($observer);

			if(isset($this->observers[$object_hash]))
				throw new observer_exception('Object hash '.$object_hash.' is currently in use');

			$this->observers[$object_hash]=$observer;

			return $this;
		}
		public function detach(i_observer $observer)
		{
			$object_hash=spl_object_hash($observer);

			if(!isset($this->observers[$object_hash]))
				throw new observer_exception('Object hash '.$object_hash.' was not attached');

			unset($this->observers[$object_hash]);

			return $this;
		}
		public function notify()
		{
			foreach($this->observers as $observer)
				$observer->update($this);
		}
	}

	class observer
	{
		use t_observer;
	}
?>