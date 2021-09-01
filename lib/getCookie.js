function getCookie(name)
{
	// Read cookie value

	'use strict';

	var value='; '+document.cookie;
	var parts=value.split('; ' + name + '=');
	if(parts.length === 2)
	return parts.pop().split(';').shift();
}