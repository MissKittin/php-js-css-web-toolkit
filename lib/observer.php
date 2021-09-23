<?php
	class observer
	{
		/*
		 * Observer design pattern
		 *
		 * Usage:
			class logger
			{
				public function update($observer)
				{
					echo __CLASS__.' update(): '.$observer->get_content();
				}
			}
			class rss
			{
				public function update($observer)
				{
					echo __CLASS__.' update(): '.$observer->get_content();
				}
			}
			class article
			{
				public function update($observer)
				{
					echo __CLASS__.' update(): '.$observer->get_content();
				}
			}

			class my_observer extends observer
			{
				private $content;

				public function add($content)
				{
					echo __CLASS__.' add(): '.$content;
					$this->content=$content;
					$this->notify();
				}
				public function get_content()
				{
					return $this->content;
				}
			}

			$my_observer_object=new my_observer();
			$my_observer_object->attach(new article())->attach(new rss())->attach(new logger());
			$my_observer_object->add('New article content');
		 */

		private $observers=array();

		public function attach($observer)
		{
			$this->observers[spl_object_hash($observer)]=$observer;
			return $this;
		}
		public function detach($observer)
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