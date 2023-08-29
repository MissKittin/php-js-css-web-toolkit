<?php
	/*
	 * Console prompter
	 *
	 * Warning:
	 *  only for *nix systems
	 *  cli_gethstr requires stty utility
	 *  cli_getch requires stty utility
	 *  exec() is required
	 *  system() is required
	 *
	 * Note:
	 *  throws an cli_prompt_exception if stty utility not found
	 */

	class cli_prompt_exception extends Exception {}

	function cli_getstr()
	{
		/*
		 * Console prompter
		 * get a string from the user
		 */

		$stdin=fopen('php://stdin', 'r');
		$output=fgets($stdin);
		fclose($stdin);

		return trim($output);
	}
	function cli_gethstr()
	{
		/*
		 * Console prompter
		 * get a string from the user
		 * without printing characters
		 *
		 * Warning:
		 *  only for *nix systems
		 *  stty utility is required
		 *  exec() is required
		 *  system() is required
		 *
		 * Note:
		 *  throws an cli_prompt_exception if stty utility not found
		 */

		exec('stty 2>&1', $tool_test_output, $tool_test_code);

		if($tool_test_code !== 0)
			throw new cli_prompt_exception('stty utility not found');

		system('stty -echo');

		$stdin=fopen('php://stdin', 'r');
		$output=fgets($stdin);
		fclose($stdin);

		system('stty echo');

		return trim($output);
	}
	function cli_getch()
	{
		/*
		 * Console prompter
		 * get one character from user
		 *
		 * Warning:
		 *  only for *nix systems
		 *  stty utility is required
		 *  exec() is required
		 *  system() is required
		 *
		 * Note:
		 *  throws an cli_prompt_exception if stty utility not found
		 */

		exec('stty 2>&1', $tool_test_output, $tool_test_code);

		if($tool_test_code !== 0)
			throw new cli_prompt_exception('stty utility not found');

		exec('stty -g', $term_settings);
		system('stty -icanon');

		$stdin=fopen('php://stdin', 'r');
		$output=fread($stdin, 1);
		fclose($stdin);

		system('stty icanon');
		system('stty "'.$term_settings[0].'"');

		return $output;
	}
?>