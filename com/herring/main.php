<?php
	class herring_exception extends Exception {}
	class herring
	{
		protected $pdo_handler;
		protected $table_name_prefix='herring_';
		protected $create_table=true;
		protected $timestamp=null;
		protected $ip=null;
		protected $user_agent=null;
		protected $cookie_name=null;
		protected $cookie_value=null;
		protected $setcookie_callback=['callback'=>null];
		protected $referer=null;
		protected $uri=null;
		protected $uri_without_get=true;
		protected $maintenance_mode=false;

		// for testing purposes
		protected $_views_path=__DIR__;
		protected $_no_view_date=false;

		public static function generate_report_from_csv(string $input_file, string $output_file=null)
		{
			if(!class_exists('PDO'))
				throw new herring_exception('PDO extension is not loaded');

			if(!in_array('sqlite', PDO::getAvailableDrivers()))
				throw new herring_exception('pdo_sqlite extension is not loaded');

			if(!is_file($input_file))
				throw new herring_exception($input_file.' is not a file');

			$pdo_handler=new PDO('sqlite::memory:');
			$csv_handler=fopen($input_file, 'r');

			if($csv_handler === false)
				throw new herring_exception($input_file.' fopen failed');

			if($pdo_handler->exec(''
			.	'CREATE TABLE IF NOT EXISTS herring_archive'
			.	'('
			.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
			.		'timestamp INTEGER,'
			.		'date VARCHAR(10),'
			.		'ip VARCHAR(39),'
			.		'user_agent TEXT,'
			.		'cookie_id VARCHAR(40),'
			.		'referer VARCHAR(2083),'
			.		'uri TEXT'
			.	')'
			) === false)
				throw new herring_exception('PDO exec error (CREATE TABLE)');

			fgets($csv_handler);
			while(($csv_data=fgetcsv($csv_handler, 1000, ',')) !== false)
			{
				$query=$pdo_handler->prepare(''
				.	'INSERT INTO herring_archive'
				.	'('
				.		'timestamp,'
				.		'date,'
				.		'ip,'
				.		'user_agent,'
				.		'cookie_id,'
				.		'referer,'
				.		'uri'
				.	') VALUES ('
				.		':timestamp,'
				.		':date,'
				.		':ip,'
				.		':user_agent,'
				.		':cookie_value,'
				.		':referer,'
				.		':uri'
				.	')'
				);

				if($query === false)
					throw new herring_exception('PDO prepare error');

				if(!$query->execute([
					':timestamp'=>$csv_data[1],
					':date'=>$csv_data[2],
					':ip'=>$csv_data[3],
					':user_agent'=>$csv_data[4],
					':cookie_value'=>$csv_data[5],
					':referer'=>$csv_data[6],
					':uri'=>$csv_data[7]
				]))
					throw new herring_exception('PDO execute error');
			}

			fclose($csv_handler);

			return (new static([
				'pdo_handler'=>$pdo_handler,
				'maintenance_mode'=>true
			]))->generate_report($output_file);
		}

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handler']))
				throw new herring_exception('No pdo_handler given');

			foreach([
				'pdo_handler'=>'object',
				'table_name_prefix'=>'string',
				'create_table'=>'boolean',
				'timestamp'=>'integer',
				'ip'=>'string',
				'user_agent'=>'string',
				'cookie_name'=>'string',
				'cookie_value'=>'string',
				'referer'=>'string',
				'uri'=>'string',
				'uri_without_get'=>'boolean',
				'maintenance_mode'=>'boolean'
			] as $param=>$param_type)
				if(isset($params[$param]))
				{
					if(gettype($params[$param]) !== $param_type)
						throw new herring_exception('The input array parameter '.$param.' is not a '.$param_type);

					$this->$param=$params[$param];
				}

			if(!in_array(
				$this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new herring_exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');

			if($this->maintenance_mode === true)
				return;

			if(isset($params['setcookie_callback']))
			{
				if(!is_callable($params['setcookie_callback']))
					throw new herring_exception('The input array parameter setcookie_callback is not callable');

				$this->setcookie_callback['callback']=$params['setcookie_callback'];
			}

			if($this->ip === null)
			{
				if(!isset($_SERVER['REMOTE_ADDR']))
					throw new herring_exception('$_SERVER["REMOTE_ADDR"] is not set');

				$this->ip=$_SERVER['REMOTE_ADDR'];
			}

			if(($this->user_agent === null) && isset($_SERVER['HTTP_USER_AGENT']))
				$this->user_agent=$_SERVER['HTTP_USER_AGENT'];

			if(
				($this->cookie_name !== null) &&
				($this->cookie_value === null) &&
				isset($_COOKIE[$this->cookie_name])
			)
				$this->cookie_value=$_COOKIE[$this->cookie_name];

			if(($this->referer === null) && isset($_SERVER['HTTP_REFERER']))
				$this->referer=$_SERVER['HTTP_REFERER'];

			if($this->uri === null)
			{
				if(!isset($_SERVER['REQUEST_URI']))
					throw new herring_exception('$_SERVER["REQUEST_URI"] is not set');

				$this->uri=$_SERVER['REQUEST_URI'];
			}

			if(($this->uri !== null) && ($this->uri_without_get === true))
				$this->uri=strtok($this->uri, '?');
		}

		protected function load_library($libraries)
		{
			foreach($libraries as $library=>$params)
			{
				$load_library=true;

				if($params !== null)
					switch($params[0])
					{
						case 'function':
							if(function_exists($params[1]))
								$load_library=false;
						break;
						case 'class':
							if(class_exists($params[1]))
								$load_library=false;
					}

				if($load_library)
				{
					$load_library=false;

					foreach([__DIR__.'/lib', __DIR__.'/../../lib'] as $location)
						if(file_exists($location.'/'.$library))
						{
							require $location.'/'.$library;

							$load_library=true;
							break;
						}

					if(!$load_library)
						throw new herring_exception('Library '.$library.' not found');
				}
			}
		}
		protected function generate_html_table($phase, $data)
		{
			switch($phase)
			{
				case 'begin':
					$output='<table><tr>';

					foreach($data as $column)
						$output.='<th>'.$column.'</th>';

					$output.='</tr>';
				break;
				case 'data':
					$output='<tr>';

					foreach($data as $column)
						$output.='<td>'.htmlspecialchars($column).'</td>';

					$output.='</tr>';
				break;
				case 'end':
					$output='</table>';
			}

			return $output;
		}

		public function add()
		{
			$this->load_library(['rand_str.php'=>['function', 'rand_str']]);

			if($this->maintenance_mode === true)
				throw new herring_exception('You cannot add records in maintenance mode');

			if($this->timestamp === null)
				$this->timestamp=time();

			if(($this->cookie_name !== null))
			{
				if($this->cookie_value === null)
					$this->cookie_value=rand_str(40);

				if($this->setcookie_callback['callback'] === null)
					setcookie(
						$this->cookie_name,
						$this->cookie_value,
						time()+63113852 // 2 years
					);
				else
					$this->setcookie_callback['callback'](
						$this->cookie_name,
						$this->cookie_value
					);
			}

			foreach(['user_agent', 'cookie_value', 'referer'] as $param)
				if($this->$param === null)
					$this->$param='null';

			if($this->create_table)
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'visitors'
						.	'('
						.		'id SERIAL PRIMARY KEY,'
						.		'timestamp INTEGER,'
						.		'date VARCHAR(10),'
						.		'ip VARCHAR(39),'
						.		'user_agent TEXT,'
						.		'cookie_id VARCHAR(40),'
						.		'referer VARCHAR(2083),'
						.		'uri TEXT'
						.	')'
						) === false)
							throw new herring_exception('PDO exec error (CREATE TABLE)');
					break;
					case 'mysql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'visitors'
						.	'('
						.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
						.		'timestamp INTEGER,'
						.		'date VARCHAR(10),'
						.		'ip VARCHAR(39),'
						.		'user_agent TEXT,'
						.		'cookie_id VARCHAR(40),'
						.		'referer VARCHAR(2083),'
						.		'uri TEXT'
						.	')'
						) === false)
							throw new herring_exception('PDO exec error (CREATE TABLE)');
					break;
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'visitors'
						.	'('
						.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
						.		'timestamp INTEGER,'
						.		'date VARCHAR(10),'
						.		'ip VARCHAR(39),'
						.		'user_agent TEXT,'
						.		'cookie_id VARCHAR(40),'
						.		'referer VARCHAR(2083),'
						.		'uri TEXT'
						.	')'
						) === false)
							throw new herring_exception('PDO exec error (CREATE TABLE)');
				}

			$query=$this->pdo_handler->prepare(''
			.	'INSERT INTO '.$this->table_name_prefix.'visitors'
			.	'('
			.		'timestamp,'
			.		'date,'
			.		'ip,'
			.		'user_agent,'
			.		'cookie_id,'
			.		'referer,'
			.		'uri'
			.	') VALUES ('
			.		':timestamp,'
			.		':date,'
			.		':ip,'
			.		':user_agent,'
			.		':cookie_value,'
			.		':referer,'
			.		':uri'
			.	')'
			);

			if($query === false)
				throw new herring_exception('PDO prepare error');

			if(!$query->execute([
				':timestamp'=>$this->timestamp,
				':date'=>date('Y-m-d', $this->timestamp),
				':ip'=>$this->ip,
				':user_agent'=>$this->user_agent,
				':cookie_value'=>$this->cookie_value,
				':referer'=>$this->referer,
				':uri'=>$this->uri
			]))
				throw new herring_exception('PDO execute error');
		}
		public function move_to_archive(int $days)
		{
			if($this->maintenance_mode !== true)
				throw new herring_exception('You haven\'t turned on maintenance mode');

			if($days < 0)
				throw new herring_exception('The days argument must be greater or equal to 0');

			$days*=86400;
			$days=time()-$days;

			if($this->create_table)
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'archive'
						.	'('
						.		'id SERIAL PRIMARY KEY,'
						.		'timestamp INTEGER,'
						.		'date VARCHAR(10),'
						.		'ip VARCHAR(39),'
						.		'user_agent TEXT,'
						.		'cookie_id VARCHAR(40),'
						.		'referer VARCHAR(2083),'
						.		'uri TEXT'
						.	')'
						) === false)
							throw new herring_exception('PDO exec error (CREATE TABLE)');
					break;
					case 'mysql':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'archive'
						.	'('
						.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
						.		'timestamp INTEGER,'
						.		'date VARCHAR(10),'
						.		'ip VARCHAR(39),'
						.		'user_agent TEXT,'
						.		'cookie_id VARCHAR(40),'
						.		'referer VARCHAR(2083),'
						.		'uri TEXT'
						.	')'
						) === false)
							throw new herring_exception('PDO exec error (CREATE TABLE)');
					break;
					case 'sqlite':
						if($this->pdo_handler->exec(''
						.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'archive'
						.	'('
						.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
						.		'timestamp INTEGER,'
						.		'date VARCHAR(10),'
						.		'ip VARCHAR(39),'
						.		'user_agent TEXT,'
						.		'cookie_id VARCHAR(40),'
						.		'referer VARCHAR(2083),'
						.		'uri TEXT'
						.	')'
						) === false)
							throw new herring_exception('PDO exec error (CREATE TABLE)');
				}

			$affected_rows=$this->pdo_handler->exec(''
			.	'INSERT INTO '.$this->table_name_prefix.'archive '
			.		'SELECT id, timestamp, date, ip, user_agent, cookie_id, referer, uri '
			.		'FROM '.$this->table_name_prefix.'visitors '
			.		'WHERE timestamp<'.$days
			);

			if($affected_rows === false)
				throw new herring_exception('PDO query error (INSERT INTO '.$this->table_name_prefix.'archive SELECT FROM '.$this->table_name_prefix.'visitors)');

			if($affected_rows === 0)
				return 0;

			if($this->pdo_handler->exec(''
			.	'DELETE FROM '.$this->table_name_prefix.'visitors '
			.	'WHERE timestamp<'.$days
			) === false)
				throw new herring_exception('PDO exec error (DELETE FROM '.$this->table_name_prefix.'visitors)');

			return $affected_rows;
		}
		public function flush_archive()
		{
			if($this->maintenance_mode !== true)
				throw new herring_exception('You haven\'t turned on maintenance mode');

			if($this->pdo_handler->exec('DELETE FROM '.$this->table_name_prefix.'archive') === false)
				throw new herring_exception('Failed to flush the '.$this->table_name_prefix.'archive');
		}
		public function dump_archive_to_csv(string $output_file=null)
		{
			if($this->maintenance_mode !== true)
				throw new herring_exception('You haven\'t turned on maintenance mode');

			if($output_file === null)
			{
				$output='';

				$save_report=function(&$output, $content)
				{
					$output.=$content;
				};
			}
			else
			{
				$output=$output_file;

				if(file_exists($output))
					throw new herring_exception($output.' already exists');

				if(!is_dir(dirname($output)))
					throw new herring_exception(dirname($output).' is not a directory');

				if(file_put_contents($output, '') === false)
					throw new herring_exception($output.' write error');

				$save_report=function($output, $content)
				{
					if(file_put_contents($output, $content, FILE_APPEND) === false)
						throw new herring_exception($output.' write error');
				};
			}

			$query=$this->pdo_handler->query(''
			.	'SELECT * '
			.	'FROM '.$this->table_name_prefix.'archive'
			);

			if($query === false)
				throw new herring_exception('PDO query error (SELECT * FROM '.$this->table_name_prefix.'archive)');

			$save_report($output, ''
			.	'"id",'
			.	'"timestamp",'
			.	'"date",'
			.	'"ip",'
			.	'"user_agent",'
			.	'"cookie_id",'
			.	'"referer",'
			.	'"uri"'
			."\n");

			while($row=$query->fetch(PDO::FETCH_ASSOC))
			{
				$row_output='';

				foreach($row as $cell)
					$row_output.='"'.str_replace('"', '""', $cell).'",';

				$save_report($output, substr($row_output, 0, -1)."\n");
			}

			if($output_file === null)
				return $output;
		}
		public function generate_report(string $output_file=null)
		{
			if($this->maintenance_mode !== true)
				throw new herring_exception('You haven\'t turned on maintenance mode');

			$this->load_library([
				'measure_exec_time.php'=>['class', 'measure_exec_time_from_here']
			]);

			if($output_file === null)
			{
				$output='';

				$save_report=function(&$output, $content)
				{
					$output.=$content;
				};
			}
			else
			{
				$output=$output_file;

				if(file_exists($output))
					throw new herring_exception($output.' already exists');

				if(!is_dir(dirname($output)))
					throw new herring_exception(dirname($output).' is not a directory');

				if(file_put_contents($output, '') === false)
					throw new herring_exception($output.' write error');

				$save_report=function($output, $content)
				{
					if(file_put_contents($output, $content, FILE_APPEND) === false)
						throw new herring_exception($output.' write error');
				};
			}

			$generator_time=new measure_exec_time_from_here();

			// top of the document
				ob_start();
				require $this->_views_path.'/views/top.php';
				$save_report($output, ob_get_contents());
				ob_end_clean();

			// first table: date - hits - unique hits
				$hits=[];

				$query=$this->pdo_handler->query(''
				.	'SELECT date, COUNT(date) AS hits '
				.	'FROM '.$this->table_name_prefix.'archive '
				.	'GROUP BY date'
				);

				if($query === false)
					throw new herring_exception('PDO query error (SELECT date, hits FROM '.$this->table_name_prefix.'archive)');

				while($row=$query->fetch(PDO::FETCH_ASSOC))
				{
					$hits[$row['date']]['hits']=$row['hits'];
					$hits[$row['date']]['unique_hits']=0;
				}

				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						$query=$this->pdo_handler->query(''
						.	'SELECT date, MAX(ip), MAX(user_agent), MAX(cookie_id) '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'GROUP BY cookie_id, date'
						);
					break;
					case 'mysql':
					case 'sqlite':
						$query=$this->pdo_handler->query(''
						.	'SELECT date, ip, user_agent, cookie_id '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'GROUP BY cookie_id, date'
						);
				}

				if($query === false)
					throw new herring_exception('PDO query error (SELECT date, ip, user_agent, cookie_id FROM '.$this->table_name_prefix.'archive)');

				while($row=$query->fetch(PDO::FETCH_ASSOC))
					++$hits[$row['date']]['unique_hits'];

				$save_report(
					$output,
					$this->generate_html_table('begin', ['Date', 'Hits', 'Unique hits'])
				);

				// I AM AN ENGINEER!!!
				if($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql')
					ksort($hits);

				foreach($hits as $hit_date=>$hit_count)
					$save_report($output, $this->generate_html_table(
						'data',
						[$hit_date, $hit_count['hits'], $hit_count['unique_hits']]
					));

				$save_report($output, $this->generate_html_table('end', null));

			// second table begin, left
				$save_report($output, ''
				.	'<table class="grid_table">'
				.	'<tr class="grid_tr">'
				.	'<td class="grid_td">'
				);

			// second table left: date - page - hits
				$query=$this->pdo_handler->query(''
				.	'SELECT date, uri '
				.	'FROM '.$this->table_name_prefix.'archive'
				);

				if($query === false)
					throw new herring_exception('PDO query error (SELECT date, uri FROM '.$this->table_name_prefix.'archive)');

				$save_report(
					$output,
					$this->generate_html_table('begin', ['Date', 'Page', 'Hits'])
				);

				$hits=[];
				$last_hit_date=null;

				while($row=$query->fetch(PDO::FETCH_ASSOC))
				{
					if($last_hit_date === null)
						$last_hit_date=$row['date'];
					else if($last_hit_date !== $row['date'])
					{
						foreach($hits as $hit_date=>$hit_page_array)
							foreach($hit_page_array as $hit_page=>$hit_count)
								$save_report($output, $this->generate_html_table(
									'data',
									[$hit_date, $hit_page, $hit_count]
								));

						$hits=[];
						$last_hit_date=$row['date'];
					}

					if(isset($hits[$row['date']][$row['uri']]))
						++$hits[$row['date']][$row['uri']];
					else
						$hits[$row['date']][$row['uri']]=1;
				}

				foreach($hits as $hit_date=>$hit_page_array)
					foreach($hit_page_array as $hit_page=>$hit_count)
						$save_report($output, $this->generate_html_table(
							'data',
							[$hit_date, $hit_page, $hit_count]
						));

				$save_report($output, $this->generate_html_table('end', null));

			// second table begin, right
				$save_report($output, '</td><td class="grid_td">');

			// second table right: date - page - unique hits
				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						$query=$this->pdo_handler->query(''
						.	'SELECT DISTINCT ON (date, uri, cookie_id) date, uri, ip, user_agent, cookie_id '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'ORDER BY date, uri, cookie_id'
						);
					break;
					case 'mysql':
					case 'sqlite':
						$query=$this->pdo_handler->query(''
						.	'SELECT date, uri, ip, user_agent, cookie_id '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'GROUP BY date, uri, cookie_id'
						);
				}

				if($query === false)
					throw new herring_exception('PDO query error (SELECT date, uri, ip, user_agent, cookie_id FROM '.$this->table_name_prefix.'archive)');

				$save_report(
					$output,
					$this->generate_html_table('begin', ['Date', 'Page', 'Unique hits'])
				);

				$hits=[];
				$last_hit_date=null;

				while($row=$query->fetch(PDO::FETCH_ASSOC))
				{
					if($last_hit_date === null)
						$last_hit_date=$row['date'];
					else if($last_hit_date !== $row['date'])
					{
						foreach($hits as $hit_date=>$hit_page_array)
							foreach($hit_page_array as $hit_page=>$hit_count)
								$save_report($output, $this->generate_html_table(
									'data',
									[$hit_date, $hit_page, $hit_count]
								));

						$hits=[];
						$last_hit_date=$row['date'];
					}

					if(isset($hits[$row['date']][$row['uri']]))
						++$hits[$row['date']][$row['uri']];
					else
						$hits[$row['date']][$row['uri']]=1;
				}

				foreach($hits as $hit_date=>$hit_page_array)
					foreach($hit_page_array as $hit_page=>$hit_count)
						$save_report($output, $this->generate_html_table(
							'data',
							[$hit_date, $hit_page, $hit_count]
						));

				$save_report($output, $this->generate_html_table('end', null));

			// second table end
				$save_report($output, '</td></tr></table>');

			// third table: date - hour - hits
				$query=$this->pdo_handler->query(''
				.	'SELECT date, timestamp '
				.	'FROM '.$this->table_name_prefix.'archive'
				);

				if($query === false)
					throw new herring_exception('PDO query error (SELECT date, timestamp FROM '.$this->table_name_prefix.'archive)');

				$save_report(
					$output,
					$this->generate_html_table('begin', ['Date', 'Hour', 'Hits'])
				);

				$hits=[];
				$last_hit_date=null;

				while($row=$query->fetch(PDO::FETCH_ASSOC))
				{
					if($last_hit_date === null)
						$last_hit_date=$row['date'];
					else if($last_hit_date !== $row['date'])
					{
						foreach($hits as $hit_date=>$hit_hours)
							foreach($hit_hours as $hit_hour=>$hit_count)
								$save_report($output, $this->generate_html_table(
									'data',
									[$hit_date, $hit_hour, $hit_count]
								));

						$hits=[];
						$last_hit_date=$row['date'];
					}

					$current_hit_hour=gmdate('H', $row['timestamp']);

					if(isset($hits[$row['date']][$current_hit_hour]))
						++$hits[$row['date']][$current_hit_hour];
					else
						$hits[$row['date']][$current_hit_hour]=1;
				}

				foreach($hits as $hit_date=>$hit_hours)
					foreach($hit_hours as $hit_hour=>$hit_count)
						$save_report($output, $this->generate_html_table(
							'data',
							[$hit_date, $hit_hour, $hit_count]
						));

				$save_report($output, $this->generate_html_table('end', null));

			// fourth table: ip - date - hour - hits - page - referer - cookie_id - user agent
				$query=$this->pdo_handler->query(''
				.	'SELECT ip, date, timestamp, cookie_id, user_agent, referer, uri '
				.	'FROM '.$this->table_name_prefix.'archive '
				.	'ORDER BY timestamp ASC'
				);

				if($query === false)
					throw new herring_exception('PDO query error (SELECT ip, date, cookie_id, user_agent, referer, uri FROM '.$this->table_name_prefix.'archive)');

				$save_report(
					$output,
					$this->generate_html_table('begin', [
						'IP',
						'Date',
						'Hour',
						'Hits',
						'Page',
						'Referer',
						'Cookie ID',
						'User agent'
					])
				);

				$hits=[];
				$last_hit_date=null;

				while($row=$query->fetch(PDO::FETCH_ASSOC))
				{
					if($last_hit_date === null)
						$last_hit_date=$row['date'];
					else if($last_hit_date !== $row['date'])
					{
						foreach($hits as $hit)
							$save_report($output, $this->generate_html_table('data', [
								$hit['ip'],
								$hit['date'],
								gmdate('H:i:s', $hit['timestamp']),
								$hit['hits'],
								$hit['page'],
								$hit['referer'],
								$hit['cookie_id'],
								$hit['user_agent']
							]));

						$hits=[];
						$last_hit_date=$row['date'];
					}

					$hit_id=''
					.	$row['ip']
					.	$row['date']
					.	$row['cookie_id']
					.	$row['user_agent']
					.	$row['referer']
					.	$row['uri']
					;

					if(isset($hits[$hit_id]))
						++$hits[$hit_id]['hits'];
					else
						$hits[$hit_id]=[
							'ip'=>$row['ip'],
							'date'=>$row['date'],
							'timestamp'=>$row['timestamp'],
							'cookie_id'=>$row['cookie_id'],
							'user_agent'=>$row['user_agent'],
							'referer'=>$row['referer'],
							'page'=>$row['uri'],
							'hits'=>1
						];
				}

				// last day in the database
				foreach($hits as $hit)
					$save_report($output, $this->generate_html_table('data', [
						$hit['ip'],
						$hit['date'],
						gmdate('H:i:s', $hit['timestamp']),
						$hit['hits'],
						$hit['page'],
						$hit['referer'],
						$hit['cookie_id'],
						$hit['user_agent']
					]));

				$save_report($output, $this->generate_html_table('end', null));

			$save_report($output, 'Generated in '.$generator_time->get_exec_time().' seconds');

			// bottom of the document
				ob_start();
				require $this->_views_path.'/views/bottom.php';
				$save_report($output, ob_get_contents());
				ob_end_clean();

			if($output_file === null)
				return $output;
		}
	}
?>