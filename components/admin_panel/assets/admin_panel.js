document.addEventListener('DOMContentLoaded', function(){
	var menuButton=document.getElementById('admin_header').children[0];
	var menuBox=document.getElementById('admin_content').children[0];
	var moduleBox=document.getElementById('admin_content').children[1];

	menuButton.style.display='block';
	menuBox.style.display='none';
	moduleBox.style.borderRadius='0';
	moduleBox.style.transition='border-radius 2s';

	menuButton.addEventListener('click', function(){
		if(menuBox.style.display === 'none')
		{
			menuBox.style.display='block';
			moduleBox.style.borderRadius='15px 0 0 15px';
		}
		else
		{
			menuBox.style.display='none';
			moduleBox.style.borderRadius='0';
		}
	}, true);
}, true);