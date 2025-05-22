<?php
	function assets_compiler(
		string $asset_dir,
		?string $output_file=null
	){
		/*
		 * Asset compiler
		 *
		 * Usage:
			assets_compiler(
				'path/to/source.css',
				'path/to/output-file.css'
			);
		 * where
		 *  source.css can be file or directory
		 *  and output-file.css must be file or not exist
		 *
		 * Asset sources/Examples:
		 *  app/assets is a directory
		 *  public/assets is a directory
		 *  1) preprocessed assets
		 *   create directory in app/assets with output file name
		 *   create main.php file in this directory with CSS/Js/PHP/etc code
		 *   open <?php tag and write dynamic code - these block will be executed during compilation
		 *   use print or echo to add content to the output file
		 *   also put css or js files in this directory that you want to manually include in main.php
		 *  2) concatenated assets
		 *   create directory in app/assets with output file name
		 *   place all css or js files in this directory
		 *   all files will be merged to one file in public/assets
		 *   also you can create this directory in app/views and softlink it to the app/assets
		 *  3) single file
		 *   create/put file in app/assets
		 *   it will be copied to the public/assets
		 *
		 * Returns integer or array for concatenated assets
		 *
		 * Return codes:
		 *  0 -> asset compiled sucessfully
		 *  1 -> unable to clear output file
		 *  2 -> unable to copy the file
		 *  empty array -> no files concatenated
		 *
		 * Usage without output file:
		 *  if no output file is provided
		 *  the compiled asset (string) will be returned
			$compiled_asset=assets_compiler('path/to/source.css');
		 */

		if(
			($output_file !== null) &&
			file_exists($output_file) &&
			(file_put_contents($output_file, '') === false)
		)
			return 1;

		$return_content='';

		if(is_file(''
		.	$asset_dir
		.	'/main.php'
		)){
			if($output_file === null)
				ob_start(function($content) use(&$return_content){
					$return_content.=$content;
				});
			else
				ob_start(function($content) use($output_file){
					file_put_contents(
						$output_file,
						$content,
						FILE_APPEND
					);
				});

			include $asset_dir.'/main.php';

			ob_end_clean();

			if($output_file === null)
				return $return_content;

			return 0;
		}

		if(is_dir($asset_dir))
		{
			$processed_files=[];

			foreach(array_diff(
				scandir($asset_dir),
				['.', '..']
			) as $file)
				if(is_file(''
				.	$asset_dir
				.	'/'
				.	$file
				)){
					if($output_file === null)
					{
						$return_content.=file_get_contents(
							$asset_dir.'/'.$file
						);

						continue;
					}

					file_put_contents(
						$output_file,
						file_get_contents(
							$asset_dir.'/'.$file
						),
						FILE_APPEND
					);

					$processed_files[]=$file;
				}

			if($output_file === null)
				return $return_content;

			return $processed_files;
		}

		if($output_file === null)
			return file_get_contents($asset_dir);

		if(file_put_contents(
			$output_file,
			file_get_contents($asset_dir)
		) === false)
			return 2;

		return 0;
	}
?>