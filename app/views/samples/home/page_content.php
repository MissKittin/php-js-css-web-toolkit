<h1>PHP-JS-CSS web toolkit</h1>
<script>
	document.addEventListener('DOMContentLoaded', function(){
		sendNotification('sendNotification() works', 'Your nice web app', '');
	});
</script>
<?php
	foreach($view['home_links'] as $link_name=>$link_url)
		echo '<a href="' . $link_url . '">' . $link_name . '</a><br>';
?>