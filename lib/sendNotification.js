function sendNotification(content, title, icon=null)
{
	/*
	 * Send notification to the browser
	 *
	 * Note:
	 *  returns true if notification has been sent
	 *  if a permission request is sent
	 *   the function will return false even if the user agrees
	 *
	 * Usage:
	 *  sendNotification('Nice content', 'Nicest title', '/notification.png')
	 *  sendNotification('Nice content', 'Nicest title')
	 */

	var options={
		body: content
	};

	if(icon !== null)
		options.icon=icon;

	if(Notification.permission === 'granted')
	{
		new Notification(title, options);
		return true;
	}

	if(Notification.permission !== 'denied')
		Notification.requestPermission(function(permission){
			if(permission === 'granted')
			{
				new Notification(title, options);
				return true;
			}
		});

	return false;
}