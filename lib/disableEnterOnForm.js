function disableEnterOnForm(element)
{
	/*
	 * Disable submit by Enter behavior
	 *
	 * Usage:
	 *  disableEnterOnForm(document.getElementById('elementId'));
	 */

	element.addEventListener('keydown', function(e){
		if((e.keyIdentifier == 'U+000A') || (e.keyIdentifier == 'Enter') || (e.keyCode == 13))
		{
			e.preventDefault();
			return false;
		}
	}, true);
}