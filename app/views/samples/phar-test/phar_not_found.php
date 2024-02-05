<style>
	pre {
		white-space: pre-wrap;
	}
</style>

<h1>Make Phar</h1>
Run in the project directory:
<pre>php -d phar.readonly=0 ./bin/mkphar.php --compress=gz --source=com --source=lib --ignore=assets/ --ignore=bin/ --ignore=tests/ --ignore=tmp/ --ignore=README.md --output=toolkit.phar</pre>

<h3>Error: <?php echo realpath('.').DIRECTORY_SEPARATOR; ?>toolkit.phar is not a file</h3>