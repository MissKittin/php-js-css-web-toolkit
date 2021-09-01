function sendNotification(content, title, icon=null)
{
	/*
	 * Send notification to the browser
	 *
	 * Usage:
	 *  sendNotification('Nice content', 'Nicest title', '/notification.png')
	 *  sendNotification('Nice content', 'Nicest title')
	 *
	 * Returns true if notification has been sent
	 */

	if(icon === null)
		var options={ body: content };
	else
		var options={
			body: content,
			icon: icon
		};

	if(Notification.permission === 'granted')
	{
		new Notification(title, options);
		return true;
	}
	else if(Notification.permission !== 'denied')
		Notification.requestPermission(function(permission){
			if(permission === 'granted')
			{
				new Notification(title, options);
				return true;
			}
		});

	return false;
}