<?php
	/*
	 * mk_calendar.php library test
	 *
	 * Note:
	 *  looks for a library at ../lib
	 *  looks for a library at ..
	 *
	 * Warning:
	 *  var_export_contains.php library is required
	 */

	echo ' -> Including var_export_contains.php';
		if(is_file(__DIR__.'/../lib/var_export_contains.php'))
		{
			if(@(include __DIR__.'/../lib/var_export_contains.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../var_export_contains.php'))
		{
			if(@(include __DIR__.'/../var_export_contains.php') === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	echo ' -> Including '.basename(__FILE__);
		if(is_file(__DIR__.'/../lib/'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../lib/'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else if(is_file(__DIR__.'/../'.basename(__FILE__)))
		{
			if(@(include __DIR__.'/../'.basename(__FILE__)) === false)
			{
				echo ' [FAIL]'.PHP_EOL;
				exit(1);
			}
		}
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			exit(1);
		}
	echo ' [ OK ]'.PHP_EOL;

	$failed=false;

	echo ' -> Testing mk_calendar_m2w';
		//echo ' ('.var_export_contains(mk_calendar_m2w(10, 2024), '', true).')';
		if(var_export_contains(
			mk_calendar_m2w(10, 2024),
			'array(1=>2,2=>3,3=>4,4=>5,5=>6,6=>0,7=>1,8=>2,9=>3,10=>4,11=>5,12=>6,13=>0,14=>1,15=>2,16=>3,17=>4,18=>5,19=>6,20=>0,21=>1,22=>2,23=>3,24=>4,25=>5,26=>6,27=>0,28=>1,29=>2,30=>3,31=>4,)'
		))
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing mk_calendar_table (Sunday)';
		$output=mk_calendar_table(mk_calendar_m2w(10, 2024), [
			4=>[
				'params'=>'class="calendar_event"',
				'link'=>'href="/events/2024/10/04"'
			],
			12=>[
				'params'=>'class="calendar_event"',
				'link'=>'href="/events/2024/10/12"'
			]
		]);
		//echo ' ('.$output.')';
		if(
			$output
			===
			'<tr><td></td><td></td><td>1</td><td>2</td><td>3</td><td class="calendar_event"><a href="/events/2024/10/04">4</a></td><td>5</td></tr><tr><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td class="calendar_event"><a href="/events/2024/10/12">12</a></td></tr><tr><td>13</td><td>14</td><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td></tr><tr><td>20</td><td>21</td><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td></tr><tr><td>27</td><td>28</td><td>29</td><td>30</td><td>31</td><td></td><td></td></tr>'
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	echo ' -> Testing mk_calendar_table (Monday)';
		$output=mk_calendar_table(mk_calendar_m2w(10, 2024, 2), [
			4=>[
				'params'=>'class="calendar_event"',
				'link'=>'href="/events/2024/10/04"'
			],
			12=>[
				'params'=>'class="calendar_event"',
				'link'=>'href="/events/2024/10/12"'
			]
		]);
		//echo ' ('.$output.')';
		if(
			$output
			===
			'<tr><td></td><td>1</td><td>2</td><td>3</td><td class="calendar_event"><a href="/events/2024/10/04">4</a></td><td>5</td><td>6</td></tr><tr><td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td class="calendar_event"><a href="/events/2024/10/12">12</a></td><td>13</td></tr><tr><td>14</td><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td></tr><tr><td>21</td><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td><td>27</td></tr><tr><td>28</td><td>29</td><td>30</td><td>31</td><td></td><td></td><td></td></tr>'
		)
			echo ' [ OK ]'.PHP_EOL;
		else
		{
			echo ' [FAIL]'.PHP_EOL;
			$failed=true;
		}

	if($failed)
		exit(1);
?>