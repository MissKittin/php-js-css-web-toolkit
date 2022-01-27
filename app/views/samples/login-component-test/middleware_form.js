document.getElementsByClassName('input_button')[0].style.display='none';
document.getElementsByClassName('input_checkbox')[0].children[0].children[0].onclick=function()
{
	if(document.getElementsByClassName('input_checkbox')[0].children[0].children[0].checked)
		document.getElementsByClassName('input_button')[0].style.display='block';
	else
		document.getElementsByClassName('input_button')[0].style.display='none';
}