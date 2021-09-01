function enableTabOnTextarea(id)
{
	// Allow inserting tabs on selected textareas

	'use strict';

	document.getElementById(id).onkeydown=function(e){
		if((e.keyCode == 9) || (e.which == 9))
		{
			e.preventDefault();
			var s=this.selectionStart;
			this.value=this.value.substring(0, this.selectionStart) + "\t" + this.value.substring(this.selectionEnd);
			this.selectionEnd=s+1;
		}
	};
}