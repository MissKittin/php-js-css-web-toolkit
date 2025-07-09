<?php
	if(!class_exists('php_psr_exception'))
		require __DIR__.'/bootstrap.php';

	_php_psr_load_library('class', 'logger.php', 'log_to_generic');
	_php_psr_load_library('function', 'string_interpolator.php', 'string_interpolator');

	if(!interface_exists(
		'\Psr\Log\LoggerInterface'
	))
		throw new php_psr_exception(
			'psr/log package is not installed'
		);

	class log_to_psr
	implements Psr\Log\LoggerInterface
	{
		protected $logger;

		public function __construct(log_to_generic $logger)
		{
			$this->logger=$logger;
		}

		public function emergency($message, array $context=[])
		{
			$this->logger->emerg(string_interpolator(
				$message,
				$context
			));
		}
		public function alert($message, array $context=[])
		{
			$this->logger->alert(string_interpolator(
				$message,
				$context
			));
		}
		public function critical($message, array $context=[])
		{
			$this->logger->crit(string_interpolator(
				$message,
				$context
			));
		}
		public function error($message, array $context=[])
		{
			$this->logger->error(string_interpolator(
				$message,
				$context
			));
		}
		public function warning($message, array $context=[])
		{
			$this->logger->warn(string_interpolator(
				$message,
				$context
			));
		}
		public function notice($message, array $context=[])
		{
			$this->logger->notice(string_interpolator(
				$message,
				$context
			));
		}
		public function info($message, array $context=[])
		{
			$this->logger->info(string_interpolator(
				$message,
				$context
			));
		}
		public function debug($message, array $context=[])
		{
			$this->logger->debug(string_interpolator(
				$message,
				$context
			));
		}
		public function log(
			$level,
			$message, array $context=[]
		){
			$this->$level(
				$message,
				$context
			);
		}

		public function emerg($message, array $context=[])
		{
			$this->emergency($message, $context);
		}
		public function crit($message, array $context=[])
		{
			$this->critical($message, $context);
		}
		public function warn($message, array $context=[])
		{
			$this->warning($message, $context);
		}
	}
?>