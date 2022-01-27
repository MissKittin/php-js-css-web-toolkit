<?php
	// for the login_single method defined in ./app/shared/samples/login_config.php
	$GLOBALS['login']['credentials']=[
		'test',
		'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'
	];

	// import new password
	@include './var/lib/login_component_test_new_password.php';
?>