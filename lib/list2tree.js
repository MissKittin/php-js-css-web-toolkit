function list2tree(id, elementType, toggleCallback)
{
	/*
	 * Convert <ul> or <ol> to expandable tree
	 *
	 * HTML template:
	 *  create <ul> or <ol> with id
	 *  put text to the <span class="tree_open_tick"> (or another tag) in expandable nodes (see 3rd line from the stylesheet) [1]
	 *   <span> if is not expandable
	 *  mixing ul with ol is allowed
	 *  layout can be from PHP's print_array_recursive() from array_tree.php
	 *
	 * Starting function:
		list2tree('tree_list', 'span', function(object, action){
			if(action)
				object.classList.add('tree_opened');
			else
				object.classList.remove('tree_opened');
		});
	 *  where:
	 *   'tree_list' is <ul> or <ol> id
	 *   'span' is tag type [1]
	 *   function(object, action) is callback that changes arrow on expand and shrink
	 *    object is clickable span [1]
	 *    action is boolean: true on expand, false on shrink
	 *    'tree_opened' is opened node class (last line from the stylesheet)
	 *
	 * Sample stylesheet:
		.tree, .tree ul, .tree ol{ list-style-type: none; }
		.tree li span { cursor: pointer; }
		.tree li .tree_open_tick::before { content: "\25B6"; margin-right: 6px; }
		.tree li .tree_opened::before { content: "\25BC"; }
	 */

	'use strict';
	var i;

	var list=document.getElementById(id);

	var ul=list.getElementsByTagName('ul');
	for(i=0; i<ul.length; i++)
		ul[i].style.display='none';
	var ol=list.getElementsByTagName('ol');
	for(i=0; i<ol.length; i++)
		ol[i].style.display='none';

	var spans=list.getElementsByTagName(elementType);
	for(i=0; i<spans.length; i++)
		spans[i].addEventListener('click', function(a, b=toggleCallback){ // a is MouseEvent
			var lists=this.parentElement.children;
			for(var i=0; i<lists.length; i++)
			{
				if((lists[i].tagName === 'UL') || (lists[i].tagName === 'OL'))
				{
					if(lists[i].style.display === 'none')
					{
						lists[i].style.display='block';
						b(this, true);
					}
					else
					{
						lists[i].style.display='none';
						b(this, false);
					}
				}
			}
		}, false);
}