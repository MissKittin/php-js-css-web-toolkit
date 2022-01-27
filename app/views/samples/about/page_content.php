<?php
	if(file_exists('./README.md'))
		echo '<div id="readme"><h1>About</h1><pre>'.htmlspecialchars(file_get_contents('./README.md')).'</pre></div>';
	if(file_exists('./HOWTO.md'))
		echo '<div id="howto"><h1>How to</h1><pre>'.htmlspecialchars(file_get_contents('./HOWTO.md')).'</pre></div>';
	if(file_exists('./LICENSE'))
		echo '<div id="license"><h1>License</h1><pre>'.htmlspecialchars(file_get_contents('./LICENSE')).'</pre></div>';
?>
<script>
	// this script is created for toolkit readme only
	window.addEventListener('DOMContentLoaded', function(){
		<?php readfile(__DIR__.'/markdown.js'); ?>
		format_readme(document.getElementById('readme').children[1], document.getElementById('readme'));
		format_readme(document.getElementById('howto').children[1], document.getElementById('howto'));
		format_license(document.getElementById('license'));
	});
</script>