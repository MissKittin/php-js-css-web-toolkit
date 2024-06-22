<?php
	class mem_zip
	{
		/*
		 * Create zip file in memory
		 *
		 * Usage:
			// send http headers, create and send the zip
			echo mem_zip::set_headers('string_my-archive-filename.zip', 'String Example description'); // second arg is optional
			->	set_do_write() // be memory efficient
			->	add('string_file_content', 'string_file_name') // add files to the zip
			->	add('string_file_content', 'string_dir_name/file_name') // add to subdirectory
			->	get(); // send the zip content to the client
		 *
		 * Source: https://github.com/phpmyadmin/phpmyadmin/blob/RELEASE_4_5_5_1/libraries/zip.lib.php
		 * License: GNU GPL2
		 * Official ZIP file format: http://www.pkware.com/support/zip-app-note
		 */

		protected $do_write=false;
		protected $datasec=[];
		protected $ctrl_dir=[];
		protected $eof_ctrl_dir="\x50\x4b\x05\x06\x00\x00\x00\x00";
		protected $old_offset=0;

		public static function set_headers(string $file_name, string $description=null)
		{
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.$file_name);

			if($description !== null)
				header('Content-Description: '.$description);

			return new static();
		}

		protected function unix2dos($unix_time=0)
		{
			if($unix_time === 0)
				$time_array=getdate();
			else
				$time_array=getdate($unix_time);

			if($time_array['year'] < 1980)
			{
				$time_array['year']=1980;
				$time_array['mon']=1;
				$time_array['mday']=1;
				$time_array['hours']=0;
				$time_array['minutes']=0;
				$time_array['seconds']=0;
			}

			return (($time_array['year']-1980) << 25)
			|	($time_array['mon'] << 21)
			|	($time_array['mday'] << 16)
			|	($time_array['hours'] << 11)
			|	($time_array['minutes'] << 5)
			|	($time_array['seconds'] >> 1);
		}

		public function set_do_write()
		{
			$this->do_write=true;
			return $this;
		}
		public function add(string $data, string $name, int $time=0)
		{
			$name=str_replace('\\', '/', $name);
			$hexdtime=pack('V', $this->unix2dos($time));

			$fr=''
			.	"\x50\x4b\x03\x04"
			.	"\x14\x00"
			.	"\x00\x00"
			.	"\x08\x00"
			.	$hexdtime;

			$unc_len=strlen($data);
			$crc=crc32($data);
			$zdata=gzcompress($data);
			$zdata=substr(substr($zdata, 0, strlen($zdata)-4), 2);
			$c_len=strlen($zdata);

			$fr.=''
			.	pack('V', $crc)
			.	pack('V', $c_len)
			.	pack('V', $unc_len)
			.	pack('v', strlen($name))
			.	pack('v', 0)
			.	$name
			.	$zdata;

			if($this->do_write)
				echo $fr;
			else
				$this->datasec[]=$fr;

			$cdrec=''
			.	"\x50\x4b\x01\x02"
			.	"\x00\x00"
			.	"\x14\x00"
			.	"\x00\x00"
			.	"\x08\x00"
			.	$hexdtime
			.	pack('V', $crc)
			.	pack('V', $c_len)
			.	pack('V', $unc_len)
			.	pack('v', strlen($name))
			.	pack('v', 0)
			.	pack('v', 0)
			.	pack('v', 0)
			.	pack('v', 0)
			.	pack('V', 32)
			.	pack('V', $this->old_offset);

			$this->old_offset+=strlen($fr);
			$cdrec.=$name;
			$this->ctrl_dir[]=$cdrec;

			return $this;
		}
		public function get()
		{
			$ctrldir=implode('', $this->ctrl_dir);

			$header=''
			.	$ctrldir
			.	$this->eof_ctrl_dir
			.	pack('v', sizeof($this->ctrl_dir))
			.	pack('v', sizeof($this->ctrl_dir))
			.	pack('V', strlen($ctrldir))
			.	pack('V', $this->old_offset)
			.	"\x00\x00";

			if($this->do_write)
			{
				echo $header;
				return '';
			}

			$data=implode('', $this->datasec);

			return $data.$header;
		}
	}
?>