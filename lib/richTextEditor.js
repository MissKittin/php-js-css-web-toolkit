function richTextEditor(RTEBox, sourceName, params={})
{
	/*
	 * Basic WYSIWYG editor
	 * mainly designed for forms
	 *
	 * Note:
	 *  all script tags and on* actions will be removed from input and output
	 *  may conflict with the Content Security Policy
	 *
	 * Usage:
	 *  create a box for the editor:
		<div id="richTextEditorBox">
	 *  add textarea in the box (optional):
		<textarea name="richTextEditorOutput">Initial content</textarea>
	 *  add box styles (note the height):
		#richTextEditorBox {
			border: 1px solid black;
		}
		#richTextEditorBox textarea {
			height: 400px;
			width: 100%;
			resize: none;
			border: none;
			outline: none;
		}
	 *  start the editor (3rd arg is optional, note the height):
		richTextEditor(document.getElementById('richTextEditorBox'), 'richTextEditorOutput', {
			'sourceId':'richTextEditorOutputId', // textarea id
			'content':'Initial content', // JSON encoded string
			'height':'400px', // same as in styles
			'linkCallback':function()
			{
				return 'https://google.com';
			},
			'imgCallback':function()
			{
				return [
					'http://localhost:8080/image.jpg',
					'Alternate text'
				];
			},
			'fontColorCallback':function()
			{
				return 'blue';
			},
			'backColorCallback':function()
			{
				return '#ff0000';
			}
		})
	 *
	 * Callbacks:
	 *  callbacks allow you to style prompts
	 *  callbacks are triggered when a button is clicked
	 *  the function must return a string
	 *   an empty string, null or false will cancel the action
	 */

	var sourceId='', address, i, color;

	RTEBox.innerHTML=''
	+	 '<div>'
	+		'<input type="button" value="B">'
	+		'<input type="button" value="I">'
	+		'<input type="button" value="U">'
	+		'<input type="button" value="L">'
	+		'<input type="button" value="C">'
	+		'<input type="button" value="R">'
	+		'<input type="button" value="P">'
	+		'<input type="button" value="A">'
	+		'<input type="button" value="AX">'
	+		'<input type="button" value="IMG">'
	+		'<input type="button" value="OL">'
	+		'<input type="button" value="UL">'
	+		'<input type="button" value="SRC">'
	+	'</div>'
	+	'<div>'
	+		'<select>'
	+			'<option value="none">-H-</option>'
	+			'<option value="1">H1</option>'
	+			'<option value="2">H2</option>'
	+			'<option value="3">H3</option>'
	+			'<option value="4">H4</option>'
	+			'<option value="5">H5</option>'
	+			'<option value="6">H6</option>'
	+		'</select>'
	+		'<select>'
	+			'<option value="3">-Fs-</option>'
	+			'<option value="1">1</option>'
	+			'<option value="2">2</option>'
	+			'<option value="4">4</option>'
	+			'<option value="5">5</option>'
	+			'<option value="6">6</option>'
	+			'<option value="7">7</option>'
	+		'</select>'
	+		'<input type="button" value="Fc">'
	+		'<input type="button" value="Bc">'
	+		'<input type="button" value="">'
	+		'<input type="button" value="X">'
	+	'</div>'
	+	'<hr>'
	+	'<div>'
	+		'<iframe></iframe>'
	+		'<textarea name="'+sourceName+'" '+sourceId+'></textarea>'
	+	'</div>'
	;

	var RTElements=RTEBox.children;
	var RTEButtons=RTElements[0].children;
	var RTELists=RTElements[1].children;
	var RTEIframe=RTElements[3].children[0];
	var RTEditor=RTEIframe.contentDocument;
	var RTESrc=RTElements[3].children[1];

	var sanitizeHTML=function(string)
	{
		return string
			.replace(/<[^>]+/g, function(match){
				return match
					.replace(/on\w+="[^"]*"/g, '')
					.replace(/on\w+='[^"]*'/g, '');
			})
			.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
	};

	document.addEventListener('submit', function(){
		if(RTEIframe.style.display !== 'none')
			RTESrc.value=RTEditor.body.innerHTML;

		RTESrc.value=sanitizeHTML(RTESrc.value);
	}, true);

	if(typeof params.sourceId !== 'undefined')
		sourceId=' id="'+params.sourceId+'"';

	RTEditor.designMode='on';

	RTElements[0].style.overflow='auto'; // buttons div
		for(i=0; i<RTElements[0].children.length; i++) // buttons
		{
			RTElements[0].children[i].style.margin='0';
			RTElements[0].children[i].style.float='left';
		}
	RTElements[1].children[0].style.marginRight='0.5%'; // heading list
		RTElements[1].children[1].style.marginRight='0.5%'; // font size list
		RTElements[1].children[2].style.marginRight='0.5%'; // font color button
		RTElements[1].children[4].style.visibility='hidden'; // dummy button
	if(typeof params.height !== 'undefined') // editor box
		RTElements[3].style.height=params.height;
	RTElements[3].children[0].style.border='none'; // editor iframe
		RTElements[3].children[0].style.width='100%';
		RTElements[3].children[0].style.height='100%';
	RTElements[3].children[1].style.display='none'; // source texarea
		RTElements[3].children[1].style.width='100%';
		RTElements[3].children[1].style.height='100%';
		RTElements[3].children[1].style.resize='none';
		RTElements[3].children[1].style.border='none';
		RTElements[3].children[1].style.outline='none';

	if(typeof params.content !== 'undefined')
	{
		RTESrc.value=sanitizeHTML(params.content);
		RTEditor.body.innerHTML=RTESrc.value;
		RTESrc.value=sanitizeHTML(RTEditor.body.innerHTML);
		RTEditor.body.innerHTML=RTESrc.value;
	}

	RTEButtons[0].addEventListener('click', function(){
		RTEditor.execCommand('bold', false, null);
	}, true);
	RTEButtons[1].addEventListener('click', function(){
		RTEditor.execCommand('italic', false, null);
	}, true);
	RTEButtons[2].addEventListener('click', function(){
		RTEditor.execCommand('underline', false, null);
	}, true);
	RTEButtons[3].addEventListener('click', function(){
		RTEditor.execCommand('justifyLeft', false, null);
	}, true);
	RTEButtons[4].addEventListener('click', function(){
		RTEditor.execCommand('justifyCenter', false, null);
	}, true);
	RTEButtons[5].addEventListener('click', function(){
		RTEditor.execCommand('justifyRight', false, null);
	}, true);
	RTEButtons[6].addEventListener('click', function(){
		RTEditor.execCommand('formatBlock', false, 'p');
	}, true);
	RTEButtons[7].addEventListener('click', function(){
		if(typeof params.linkCallback === 'undefined')
			address=prompt('', 'http://');
		else
			address=params.linkCallback();

		if((address !== '') && (address !== null) && (address !== false))
			RTEditor.execCommand('createLink', false, address);
	}, true);
	RTEButtons[8].addEventListener('click', function(){
		RTEditor.execCommand('unlink', false, null);
	}, true);
	RTEButtons[9].addEventListener('click', function(){
		var altText;

		if(typeof params.imgCallback === 'undefined')
		{
			address=prompt('', 'http://');
			altText=prompt('', 'Alternate text');
		}
		else
		{
			var result=params.imgCallback();

			address=result[0];
			altText=result[1];
		}

		if((altText === '') || (altText === null) || (altText === false))
			altText='';
		else
			altText=' alt="'+altText+'"';

		if((address !== '') && (address !== null) && (address !== false))
			RTEditor.execCommand('insertHTML', false, '<img src="'+address+'"'+altText+'>');
	}, true);
	RTEButtons[10].addEventListener('click', function(){
		RTEditor.execCommand('insertOrderedList', false, null);
	}, true);
	RTEButtons[11].addEventListener('click', function(){
		RTEditor.execCommand('insertUnorderedList', false, null);
	}, true);
	RTEButtons[12].addEventListener('click', function(){
		if(RTEIframe.style.display === 'none')
		{
			for(i=0; i<RTEButtons.length-1; i++)
				RTEButtons[i].style.visibility='visible';

			for(i=0; i<RTELists.length; i++)
				if(i !== 4)
					RTELists[i].style.visibility='visible';

			RTESrc.value=sanitizeHTML(RTESrc.value);
			RTEditor.body.innerHTML=RTESrc.value;

			RTEIframe.style.display='block';
			RTESrc.style.display='none';
		}
		else
		{
			for(i=0; i<RTEButtons.length-1; i++)
				RTEButtons[i].style.visibility='hidden';

			for(i=0; i<RTELists.length; i++)
				RTELists[i].style.visibility='hidden';

			RTESrc.value=RTEditor.body.innerHTML;
			RTEIframe.style.display='none';
			RTESrc.style.display='block';
		}
	}, true);

	RTELists[0].addEventListener('change', function(){
		if(this.value === 'none')
			RTEditor.execCommand('formatBlock', false, 'div');
		else
			RTEditor.execCommand('formatBlock', false, 'h'+this.value);
	}, true);
	RTELists[1].addEventListener('click', function(){
		RTEditor.execCommand('fontSize', false, this.value);
	}, true);
	RTELists[2].addEventListener('click', function(){
		if(typeof params.fontColorCallback === 'undefined')
			color=prompt('', '#');
		else
			color=params.fontColorCallback();

		if((color !== '') && (color !== null) && (color !== false))
			RTEditor.execCommand('foreColor', false, color);
	}, true);
	RTELists[3].addEventListener('click', function(){
		if(typeof params.backColorCallback === 'undefined')
			color=prompt('', '#');
		else
			color=params.backColorCallback();

		if((color !== '') && (color !== null) && (color !== false))
			RTEditor.execCommand('backColor', false, color);
	}, true);
	RTELists[5].addEventListener('click', function(){
		RTEditor.execCommand('removeFormat', false, null);
	}, true);
}