function enableTabOnTextarea(id)
{
	/*
	 * Allow inserting tabs on selected textareas
	 *
	 * Usage:
	 *  enableTabOnTextarea(document.getElementById('example_textarea'))
	 */

	'use strict';

	id.onkeydown=function(event)
	{
		if((event.keyCode == 9) || (event.which == 9))
		{
			event.preventDefault();

			var start=this.selectionStart;

			this.value=''
			+	this.value.substring(0, this.selectionStart)
			+	"\t"
			+	this.value.substring(this.selectionEnd);

			this.selectionEnd=start+1;
		}
	};
}