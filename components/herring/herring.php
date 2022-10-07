<?php
	class herring
	{
		protected $pdo_handler;
		protected $table_name_prefix='herring_';
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

		public function __construct(array $params)
		{
			if(!isset($params['pdo_handler']))
				throw new Exception('No pdo_handler given');

			foreach([
				'pdo_handler',
				'table_name_prefix',
				'timestamp',
				'ip',
				'user_agent',
				'cookie_name',
				'cookie_value',
				'referer',
				'uri',
				'uri_without_get',
				'maintenance_mode'
			] as $param)
				if(isset($params[$param]))
					$this->$param=$params[$param];

			if(!in_array(
				$this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME),
				['pgsql', 'mysql', 'sqlite']
			))
				throw new Exception($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME).' driver is not supported');

			if($this->maintenance_mode === true)
				return;

			if(isset($params['setcookie_callback']))
				$this->setcookie_callback['callback']=$params['setcookie_callback'];

			if($this->ip === null)
			{
				if(!isset($_SERVER['REMOTE_ADDR']))
					throw new Exception('$_SERVER["REMOTE_ADDR"] is not set');

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
					throw new Exception('$_SERVER["REQUEST_URI"] is not set');

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
							if((include $location.'/'.$library) === false)
								throw new Exception($library.' loading failed');

							$load_library=true;
							break;
						}

					if(!$load_library)
						throw new Exception('Library '.$library.' not found');
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
				throw new Exception('You cannot add records in maintenance mode');

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

			switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				case 'pgsql':
					if($this->pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'visitors'
					.	'('
					.		'id SERIAL PRIMARY KEY,'
					.		'timestamp INTEGER,'
					.		'ip VARCHAR(39),'
					.		'user_agent TEXT,'
					.		'cookie_id VARCHAR(40),'
					.		'referer VARCHAR(2083),'
					.		'uri TEXT'
					.	')'
					) === false)
						throw new Exception('PDO exec error (CREATE TABLE)');
				break;
				case 'mysql':
					if($this->pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'visitors'
					.	'('
					.		'id INTEGER NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),'
					.		'timestamp INTEGER,'
					.		'timestamp INTEGER,'
					.		'ip VARCHAR(39),'
					.		'user_agent TEXT,'
					.		'cookie_id VARCHAR(40),'
					.		'referer VARCHAR(2083),'
					.		'uri TEXT'
					.	')'
					) === false)
						throw new Exception('PDO exec error (CREATE TABLE)');
				break;
				case 'sqlite':
					if($this->pdo_handler->exec(''
					.	'CREATE TABLE IF NOT EXISTS '.$this->table_name_prefix.'visitors'
					.	'('
					.		'id INTEGER PRIMARY KEY AUTOINCREMENT,'
					.		'timestamp INTEGER,'
					.		'ip VARCHAR(39),'
					.		'user_agent TEXT,'
					.		'cookie_id VARCHAR(40),'
					.		'referer VARCHAR(2083),'
					.		'uri TEXT'
					.	')'
					) === false)
						throw new Exception('PDO exec error (CREATE TABLE)');
			}

			$query=$this->pdo_handler->prepare(''
			.	'INSERT INTO '.$this->table_name_prefix.'visitors'
			.	'('
			.		'timestamp,'
			.		'ip,'
			.		'user_agent,'
			.		'cookie_id,'
			.		'referer,'
			.		'uri'
			.	') VALUES ('
			.		':timestamp,'
			.		':ip,'
			.		':user_agent,'
			.		':cookie_value,'
			.		':referer,'
			.		':uri'
			.	')'
			);

			if($query === false)
				throw new Exception('PDO prepare error');

			if(!$query->execute([
				':timestamp'=>$this->timestamp,
				':ip'=>$this->ip,
				':user_agent'=>$this->user_agent,
				':cookie_value'=>$this->cookie_value,
				':referer'=>$this->referer,
				':uri'=>$this->uri
			]))
				throw new Exception('PDO execute error');
		}
		public function move_to_archive(int $days)
		{
			if($this->maintenance_mode !== true)
				throw new Exception('You haven\'t turned on maintenance mode');

			if($days < 0)
				throw new Exception('The days argument must be greater or equal to 0');

			$days*=86400;
			$days=time()-$days;
			$moved_ids=[];

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
						throw new Exception('PDO exec error (CREATE TABLE)');
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
						throw new Exception('PDO exec error (CREATE TABLE)');
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
						throw new Exception('PDO exec error (CREATE TABLE)');
			}

			$select_query=$this->pdo_handler->query(''
			.	'SELECT * '
			.	'FROM '.$this->table_name_prefix.'visitors '
			.	'WHERE timestamp<'.$days
			);

			if($select_query === false)
				throw new Exception('PDO query error (SELECT FROM '.$this->table_name_prefix.'visitors)');

			while($row=$select_query->fetch(PDO::FETCH_ASSOC))
			{
				$insert_query=$this->pdo_handler->prepare(''
				.	'INSERT INTO '.$this->table_name_prefix.'archive'
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

				if($insert_query === false)
					throw new Exception('PDO prepare error (INSERT INTO '.$this->table_name_prefix.'archive)');

				if(!$insert_query->execute([
					':timestamp'=>$row['timestamp'],
					':date'=>gmdate('Y-m-d', $row['timestamp']),
					':ip'=>$row['ip'],
					':user_agent'=>$row['user_agent'],
					':cookie_value'=>$row['cookie_id'],
					':referer'=>$row['referer'],
					':uri'=>$row['uri']
				]))
					throw new Exception('PDO execute error (INSERT INTO '.$this->table_name_prefix.'archive)');

				if($this->pdo_handler->exec(''
				.	'DELETE FROM '.$this->table_name_prefix.'visitors '
				.	'WHERE id='.$row['id']
				) === false)
					throw new Exception('PDO exec error (DELETE FROM '.$this->table_name_prefix.'visitors)');

				$moved_ids[]=$row['id'];
			}

			return $moved_ids;
		}
		public function flush_archive()
		{
			if($this->maintenance_mode !== true)
				throw new Exception('You haven\'t turned on maintenance mode');

			if($this->pdo_handler->exec('DELETE FROM '.$this->table_name_prefix.'archive') === false)
				throw new Exception('Failed to flush the '.$this->table_name_prefix.'archive');
		}
		public function dump_archive_to_csv(string $output_file=null)
		{
			if($this->maintenance_mode !== true)
				throw new Exception('You haven\'t turned on maintenance mode');

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
					throw new Exception($output.' already exists');

				if(!is_dir(dirname($output)))
					throw new Exception(dirname($output).' is not a directory');

				if(file_put_contents($output, '') === false)
					throw new Exception($output.' write error');

				$save_report=function($output, $content)
				{
					if(file_put_contents($output, $content, FILE_APPEND) === false)
						throw new Exception($output.' write error');
				};
			}

			$query=$this->pdo_handler->query(''
			.	'SELECT * '
			.	'FROM '.$this->table_name_prefix.'archive'
			);

			if($query === false)
				throw new Exception('PDO query error (SELECT * FROM '.$this->table_name_prefix.'archive)');

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
			$this->load_library([
				'measure_exec_time.php'=>['class', 'measure_exec_time_from_here']
			]);

			if($this->maintenance_mode !== true)
				throw new Exception('You haven\'t turned on maintenance mode');

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
					throw new Exception($output.' already exists');

				if(!is_dir(dirname($output)))
					throw new Exception(dirname($output).' is not a directory');

				if(file_put_contents($output, '') === false)
					throw new Exception($output.' write error');

				$save_report=function($output, $content)
				{
					if(file_put_contents($output, $content, FILE_APPEND) === false)
						throw new Exception($output.' write error');
				};
			}

			$generator_time=new measure_exec_time_from_here();

			// top of the document
				ob_start();
				include $this->_views_path.'/views/top.php';
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
					throw new Exception('PDO query error (SELECT date, hits FROM '.$this->table_name_prefix.'archive)');

				while($row=$query->fetch(PDO::FETCH_ASSOC))
				{
					$hits[$row['date']]['hits']=$row['hits'];
					$hits[$row['date']]['unique_hits']=0;
				}

				switch($this->pdo_handler->getAttribute(PDO::ATTR_DRIVER_NAME))
				{
					case 'pgsql':
						$query=$this->pdo_handler->query(''
						.	'SELECT date, max(ip), max(user_agent), max(cookie_id) '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'GROUP BY date'
						);
					break;
					case 'mysql':
					case 'sqlite':
						$query=$this->pdo_handler->query(''
						.	'SELECT date, ip, user_agent, cookie_id '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'GROUP BY date'
						);
				}

				if($query === false)
					throw new Exception('PDO query error (SELECT date, ip, user_agent, cookie_id FROM '.$this->table_name_prefix.'archive)');

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
					throw new Exception('PDO query error (SELECT date, uri FROM '.$this->table_name_prefix.'archive)');

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
						.	'SELECT DISTINCT ON (date, uri) date, uri, ip, user_agent, cookie_id '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'ORDER BY date, uri'
						);
					break;
					case 'mysql':
					case 'sqlite':
						$query=$this->pdo_handler->query(''
						.	'SELECT date, uri, ip, user_agent, cookie_id '
						.	'FROM '.$this->table_name_prefix.'archive '
						.	'GROUP BY date, uri'
						);
				}

				if($query === false)
					throw new Exception('PDO query error (SELECT date, uri, ip, user_agent, cookie_id FROM '.$this->table_name_prefix.'archive)');

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
					throw new Exception('PDO query error (SELECT date, timestamp FROM '.$this->table_name_prefix.'archive)');

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

			// fourth table: ip - date - hits - page - referer - cookie_id - user agent
				$query=$this->pdo_handler->query(''
				.	'SELECT ip, date, cookie_id, user_agent, referer, uri '
				.	'FROM '.$this->table_name_prefix.'archive '
				.	'ORDER BY timestamp ASC'
				);

				if($query === false)
					throw new Exception('PDO query error (SELECT ip, date, cookie_id, user_agent, referer, uri FROM '.$this->table_name_prefix.'archive)');

				$save_report(
					$output,
					$this->generate_html_table('begin', [
						'IP',
						'Date',
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
							'cookie_id'=>$row['cookie_id'],
							'user_agent'=>$row['user_agent'],
							'referer'=>$row['referer'],
							'page'=>$row['uri'],
							'hits'=>1
						];
				}

				foreach($hits as $hit)
					$save_report($output, $this->generate_html_table('data', [
						$hit['ip'],
						$hit['date'],
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
				include $this->_views_path.'/views/bottom.php';
				$save_report($output, ob_get_contents());
				ob_end_clean();

			if($output_file === null)
				return $output;
		}
	}
?>