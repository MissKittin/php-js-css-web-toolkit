function getJson(url, method, callback, data)
{
	/*
	 * Get/send JSON data
	 *
	 * Usage:
		getJson('/url', 'post'|'get', function(err, response){
			if(err == null)
				console.log(response);
			else
				console.error(err);
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
	xhr.onerror=function(e){ callback(e); };
	xhr.send(data);
}