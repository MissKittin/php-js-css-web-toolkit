<?php
	/*
	 * Observer design pattern
	 *
	 * Note:
	 *  observer class can be inherited
	 *
	 * Example usage:
		class logger implements observer_interface
		{
			public function update($observer)
			{
				echo __CLASS__.' update(): '.$observer->get_content().PHP_EOL;
			}
		}
		class rss implements observer_interface
		{
			public function update($observer)
			{
				echo __CLASS__.' update(): '.$observer->get_content().PHP_EOL;
			}
		}
		class article implements observer_interface
		{
			public function update($observer)
			{
				echo __CLASS__.' update(): '.$observer->get_content().PHP_EOL;
			}
		}

		class my_observer extends observer
		{
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
		$my_observer_object->attach(new article())->attach(new rss())->attach($logger_object);
		$my_observer_object->add('New article content');
		$my_observer_object->detach($logger_object);
		$my_observer_object->add('Second article content');
	 */

	interface observer_interface
	{
		public function update($observer);
	}
	class observer
	{
		protected $observers=array();

		public function attach(observer_interface $observer)
		{
			$this->observers[spl_object_hash($observer)]=$observer;
			return $this;
		}
		public function detach(observer_interface $observer)
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
?>