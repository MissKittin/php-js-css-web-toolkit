function titleScroller(timeout=140, title=document.title+' ', start=-1, titleLength=null)
{
	/*
	 * Infinity title scrolling
	 * Usage:
	 *  titleScroller()
	 *  titleScroller(140)
	 *    where 140 is time in miliseconds by default
	 */

	start++;
	if(start === titleLength) start=0;
	if(titleLength === null) titleLength=title.length;
	document.title=title.substring(start, titleLength) + title.substring(0, start); // By Graeme Robinson (me@graemerobinson.co.uk), http://www.dynamicdrive.com
	setTimeout('titleScroller('+timeout+', "'+title+'", '+start+', '+titleLength+')', timeout);
}