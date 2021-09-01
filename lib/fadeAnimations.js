/*
 * Fade in-out library (on load - on beforeunload)
 * from server-admin-page/webadmin project
 *
 * Required CSS styles:
 *  place this in HTML header:
 *  <!--[if !IE]><!--><style>body{opacity:0}</style><noscript><style>body{opacity:1}</style></noscript><!--<![endif]-->
 */

document.addEventListener('DOMContentLoaded', function(){
	function animate()
	{
		setTimeout(function(){
			var bodyOpacity=document.body.style.opacity;
			if(bodyOpacity === '')
				bodyOpacity=0;
			if(parseFloat(bodyOpacity) === 0.96)
				document.body.style.opacity=1;
			else
			{
				bodyOpacity=parseFloat(bodyOpacity)+0.08;
				document.body.style.opacity=bodyOpacity;
				animate();
			}	
		}, 1);
	}
	animate();
}, false);
window.addEventListener('beforeunload', function(event){
	function animate()
	{
		setTimeout(function(){
			var bodyOpacity=document.body.style.opacity;
			if(bodyOpacity === '')
				bodyOpacity=1;
			if(bodyOpacity > 0.01)
			{
				bodyOpacity=parseFloat(bodyOpacity)-0.05;
				document.body.style.opacity=bodyOpacity;
				animate();
			}
			else
				document.body.style.opacity=0;
		}, 2);
	}
	animate();

	event.preventDefault();
}, false);