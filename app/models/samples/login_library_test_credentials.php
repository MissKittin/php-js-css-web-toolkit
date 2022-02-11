<?php
	$login_credentials_single=['test', '$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO'];

	$login_credentials_multi=array(
		'test'=>'$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO',
		'test2'=>'$2y$10$e6.i2KXM3orn1cFz3KVuKOCOx4WI9TXt0wCHgS3UM98MMNWsi7yau'
	);

	function callback_function($login)
	{
		// also you can access database in this function
		switch($login)
		{
			case 'test':
				return '$2y$10$H2UEollYJTP0l1Qe4njXl.B.2OlJ1/CkhZSIBGn.OLvUGeWNebXPO';
			case 'test2':
				return '$2y$10$e6.i2KXM3orn1cFz3KVuKOCOx4WI9TXt0wCHgS3UM98MMNWsi7yau';
		}
		return null; // login failed
	}
?>