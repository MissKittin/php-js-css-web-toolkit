<?php
	/*
	 * Combination of file_*_contexts and json_*code functions
	 *
	 * Usage:
		$data=json_get_contents('./file.json');
		json_put_contents('./file.json', $data);
	 */

	function json_get_contents(
		string $filename,
		?bool $associative=null,
		int $depth=512,
		int $flags=0,
		int $offset=0,
		?int $length=null
	){
		/*
		 * Get the contents of the JSON file and decode it
		 *
		 * Arguments:
		 *  string $filename // file_get_contents
		 *  bool $associative=null // json_decode
		 *  int $depth=512 // json_decode
		 *  int $flags // json_decode
		 *  int $offset=0 // file_get_contents
		 *  ?int $length=null // file_get_contents
		 *
		 * Usage:
		 *  $data=json_get_contents('./file.json');
		 */

		if(!is_file($filename))
			return false;

		if($length === null)
			$data=file_get_contents(
				$filename,
				false,
				null,
				$offset
			);
		else
			$data=file_get_contents(
				$filename,
				false,
				null,
				$offset,
				$length
			);

		if($data === false)
			return false;

		return json_decode(
			$data,
			$associative,
			$depth,
			$flags
		);
	}
	function json_put_contents(
		string $filename,
		$value,
		int $flags=0,
		int $depth=512,
		bool $lock_ex=false
	){
		/*
		 * Encode value to JSON and save to file
		 *
		 * Arguments:
		 *  string $filename // file_put_contents
		 *  mixed $value // json_encode
		 *  int $flags // json_encode
		 *  int $depth=512 // json_encode
		 *  bool $lock_ex=false // use LOCK_EX flag in file_put_contents
		 *
		 * Usage:
		 *  json_put_contents('./file.json', $data);
		 */

		$data=json_encode(
			$value,
			$flags,
			$depth
		);

		if($data === false)
			return false;

		$file_put_contents_flags=0;

		if($lock_ex)
			$file_put_contents_flags=LOCK_EX;

		return file_put_contents(
			$filename,
			$data,
			$file_put_contents_flags
		);
	}
?>