<?php
	/*
	 * Observer design pattern
	 *
	 * Note:
	 *  observer class can be inherited or use t_observer trait
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
			->attach(new article())
			->attach(new rss())
			->attach($logger_object);
		$my_observer_object->add('New article content');
		$my_observer_object->detach($logger_object);
		$my_observer_object->add('Second article content');
	 */

	interface i_observer
	{
		public function update($observer);
	}

	trait t_observer
	{
		protected $observers=[];

		public function attach(i_observer $observer)
		{
			$this->observers[spl_object_hash($observer)]=$observer;
			return $this;
		}
		public function detach(i_observer $observer)
		{
			unset($this->observers[spl_object_hash($observer)]);
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