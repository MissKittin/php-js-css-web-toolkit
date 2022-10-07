function getJson(url, method, callback, data)
{
	/*
	 * Get/send JSON data
	 *
	 * Usage:
		getJson('/url', 'post'|'get', function(error, response){
			if(error == null)
				console.log(response);
			else
				console.error(error);
		}, '{"json":"data"}'|null);
	 */

	'use strict';

	var xhr=new XMLHttpRequest();

	xhr.open(method, url, true);
	xhr.responseType='json';

	xhr.onload=function()
	{
		var status=xhr.status;

		if(status === 200)
			callback(null, xhr.response);
		else
			callback(status);
	};
	xhr.onerror=function(error)
	{
		callback(error);
	};

	xhr.send(data);
}