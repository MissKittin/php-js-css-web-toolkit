<?php
	class dw_tar_exception extends Exception {}
	class dw_tar
	{
		/*
		 * Create tar file in memory
		 * Based on Dennis Wronka's tar library
		 *
		 * Note:
		 *  throws an dw_tar_exception on error
		 *
		 * Quick usage: send http headers, create, gzip and send the tar
			echo gzencode(dw_tar::set_headers('string_my-archive-filename.tar.gz', 'String Example description'); // second arg is optional
			->	add_data('string_file_name', 'string_file_content', [ // add files to the tar
					// this array is optional
					'is_dir'=>false,
					'permissions'=>644,
					'uid'=>0,
					'gid'=>0
				])
			->	add_data('string_dir_name/file_name', 'string_file_content') // add files to the subdirectory
			->	add_data('string_dir_name/subdir_name', '', [ // add empty directory to the tar
					'is_dir'=>true,
					'permissions'=>755, // optional, you can use eg 1755
					'uid'=>1000,
					'gid'=>1000
				])
			->	add_file('string_file_name') // add file to the tar
			->	add_file('string_file_name', 'string_new_file_name') // add file to the tar with new name
			->	add_file('string_file_name', 'string_dir_name/file_name') // add to subdirectory
			->	get_tar(), 2); // send the tar content to the client
		 *
		 * Saving to file:
		 *  to save memory you can use save_tar method:
				$tar=new dw_tar();
				$tar->add_file('path/to/file', 'subdir/file_name');

				if(!$tar->save_tar('path/to/new.tar'))
					echo 'The tarball could not be saved';
		 *
		 * Source: https://stackoverflow.com/a/33826158
		 * License: GNU LGPL2.1 http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
		 */

		protected $file_list=[];

		public static function set_headers(
			string $file_name,
			?string $description=null
		){
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.$file_name);

			if($description !== null)
				header('Content-Description: '.$description);

			return new static();
		}

		protected function _add_file_header($file_path, $file_meta, &$indicator)
		{
			$file_name=$file_meta[0];

			if($file_name === null)
				$file_name=$file_path;

			if(
				$file_meta[1] && // is_dir()
				(substr($file_name, -1) !== '/')
			)
				$file_name.='/';

			while(strlen($file_name) < 100)
				$file_name.=chr(0);

			$permissions=sprintf('%o', $file_meta[2]).chr(0);
			$userid=sprintf('%o', $file_meta[3]).chr(0);
			$groupid=sprintf('%o', $file_meta[4]).chr(0);
			$modtime=sprintf('%o', $file_meta[5]).chr(0);
			$checksum='        ';
			$indicator=0;
			$linkname='';
			$ustar='ustar  '.chr(0);
			$user='';
			$group='';
			$devmajor='';
			$devminor='';
			$prefix='';

			while(strlen($permissions) < 8)
				$permissions='0'.$permissions;

			while(strlen($userid) < 8)
				$userid='0'.$userid;

			while(strlen($groupid) < 8)
				$groupid='0'.$groupid;

			if($file_meta[1]) // is_dir()
				$filesize='0'.chr(0);
			else
				$filesize=sprintf('%o', $file_meta[6]).chr(0);

			while(strlen($filesize) < 12)
				$filesize='0'.$filesize;

			if($file_meta[1]) // is_dir()
				$indicator=5;

			while(strlen($linkname) < 100)
				$linkname.=chr(0);

			if(function_exists('posix_getpwuid'))
			{
				$user=posix_getpwuid(octdec($userid));
				$user=$user['name'];
			}

			while(strlen($user) < 32)
				$user.=chr(0);

			if(function_exists('posix_getgrgid'))
			{
				$group=posix_getgrgid(octdec($groupid));
				$group=$group['name'];
			}

			while(strlen($group) < 32)
				$group.=chr(0);

			while(strlen($devmajor) < 8)
				$devmajor.=chr(0);

			while(strlen($devminor) < 8)
				$devminor.=chr(0);

			while(strlen($prefix) < 155)
				$prefix.=chr(0);

			$header=''
			.	$file_name
			.	$permissions
			.	$userid.$groupid
			.	$filesize
			.	$modtime
			.	$checksum
			.	$indicator
			.	$linkname
			.	$ustar
			.	$user.$group
			.	$devmajor.$devminor
			.	$prefix;

			while(strlen($header) < 512)
				$header.=chr(0);

			$checksum=0;

			for($y=0; $y<strlen($header); ++$y)
				$checksum+=ord($header[$y]);

			$checksum=sprintf('%o', $checksum).chr(0).' ';

			while(strlen($checksum) < 8)
				$checksum='0'.$checksum;

			$header=''
			.	$file_name
			.	$permissions
			.	$userid.$groupid
			.	$filesize
			.	$modtime
			.	$checksum
			.	$indicator
			.	$linkname
			.	$ustar
			.	$user.$group
			.	$devmajor.$devminor
			.	$prefix;

			while(strlen($header) < 512)
				$header.=chr(0);

			return $header;
		}
		protected function _add_file_data($file_path, $data)
		{
			if($data === null)
				$data=file_get_contents($file_path);

			while((strlen($data)%512) !== 0)
				$data.=chr(0);

			return $data;
		}

		public function add_data(
			string $file_name,
			string $content,
			array $file_meta=[]
		){
			if(
				isset($file_meta['is_dir']) &&
				$file_meta['is_dir'] &&
				(!isset($file_meta['permissions']))
			)
				$file_meta['permissions']=755;

			$file_meta=array_merge([
				'is_dir'=>false,
				'permissions'=>644,
				'uid'=>0,
				'gid'=>0
			], $file_meta);

			foreach([
				'permissions',
				'uid',
				'gid']
			as $param)
				if(!is_int($file_meta[$param]))
					throw new dw_tar_exception(
						'The file '.$param.' metadata parameter is not an integer'
					);

			$this->file_list[$file_name]=[
				null,
				$file_meta['is_dir'],
				octdec($file_meta['permissions']+100000),
				$file_meta['uid'],
				$file_meta['gid'],
				time(),
				strlen($content),
				$content
			];

			return $this;
		}
		public function add_file(string $file_name, ?string $new_name=null)
		{
			if(!file_exists($file_name))
				throw new dw_tar_exception(
					$file_name.' does not exist'
				);

			if(!is_readable($file_name))
				throw new dw_tar_exception(
					$file_name.' is not readable'
				);

			$is_dir=is_dir($file_name);
			$this->file_list[$file_name]=[
				$new_name,
				$is_dir,
				fileperms($file_name),
				fileowner($file_name),
				filegroup($file_name),
				filectime($file_name),
				filesize($file_name),
				null
			];

			if($is_dir)
				foreach(array_diff(
					scandir($file_name),
					['.', '..']
				) as $file){
					if($new_name !== null)
						$new_name=$new_name.'/'.$file;

					$this->{__FUNCTION__}($file_name.'/'.$file, $new_name);
				}

			return $this;
		}

		public function get_tar()
		{
			$output='';
			$indicator=0;

			foreach($this->file_list as $file_path=>$file_meta)
			{
				$output.=$this->_add_file_header(
					$file_path, $file_meta,
					$indicator
				);

				if($indicator === 0)
					$output.=$this->_add_file_data(
						$file_path, $file_meta[7]
					);
			}

			return ''
			.	$output
			.	str_repeat(chr(0), 512);
		}
		public function save_tar(string $save_file_name)
		{
			$tar_file=fopen($save_file_name, 'w');
			$indicator=0;

			if($tar_file === false)
				return false;

			foreach($this->file_list as $file_path=>$file_meta)
			{
				fwrite(
					$tar_file,
					$this->_add_file_header(
						$file_path, $file_meta,
						$indicator
					)
				);

				if($indicator === 0)
					fwrite($tar_file, $this->_add_file_data(
						$file_path, $file_meta[7]
					));
			}

			fwrite(
				$tar_file,
				str_repeat(chr(0), 512)
			);
			fclose($tar_file);

			return true;
		}
	}
?>