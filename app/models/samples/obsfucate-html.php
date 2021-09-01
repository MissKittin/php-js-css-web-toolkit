<?php
	$ob_sfucator=array(
		'title'=>'Title from models/obsfucate-html.php',
		'label'=>'<h1>Enable javascript to view content</h1>'
	);
?>
<?php $view['content']=function($view) { ?>
	<h1>Obsfucated HTML</h1>
	See page source
<?php }; ?>