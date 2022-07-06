/*
 * Fade in-out library (on load - on beforeunload)
 * from server-admin-page/webadmin project
 * fade-out part
 */

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