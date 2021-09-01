<?php
	function directoryIterator_sort($path)
	{
		// Sort directoryIterator output by name

		$returnArray=array();
		$arrayIndicator=0;

		foreach(new directoryIterator($path) as $file)
			$returnArray[$file->getFilename()]=array('name' => $file->getFilename(), 'size' => $file->getSize(), 'ctime' => $file->getCTime());

		uksort($returnArray, function(){
			return strcmp($a, $b);
		});
		return $returnArray;
	}
?>