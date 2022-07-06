/*
 * wicdPhpGuiWindows
 * CSS window objects wrapped in javascript automation
 * From webadmin/wicd module
 *
 * wicdPhpGuiWindows parts:
 *  [1] wicdPhpGuiWindows - window controller
 *  [2] wicdPhpGuiWindowsElement - window builder
 *  [3] wicdPhpGuiWindowsLayout - layout provider
 *  [4] wicdPhpGuiWindowsDefault - class that applies colors - default gray version
 *  [4] wicdPhpGuiWindowsBright - class that applies colors - yellow version
 *  note: in theme class (4) all methods must be defined (also with boxWithBorder) except windowFloat
 *   if you want write your own theme, see wicdPhpGuiWindowsLayout class first
 *
 * Example usage (add <div id="app"></div> in the <body>):
	// initialize theme
	var theme=new wicdPhpGuiWindowsDefault();

	// create main window
		// initialize static window
		var main=new wicdPhpGuiWindows({
			'containerId':'app',
			'windowId':'mainWindow',
			'theme':theme,
			'width':'300px',
			'height':'400px',
			'title':'wicdPhpGuiWindows',
			'isFloat':false,
			'isClosable':false,
			'hasCloseButton':false
		});

		// create menu
		main.createMenuButton('About', function(){
			about.displayWindow();
		});
		main.createMenuButton('Alert', function(){
			alert('Alert button clicked');
		});

		// create text label, text input and checkbox
		main.createLabel('Text label', 'color: #0000aa').createLineBreak();
		main.createFormInput('text', 'inputText', 'Placeholder', 'Default value').createLineBreak();
		main.createFormCheckBox('This is a checkbox', 'checkboxName').createHorizontalLine();

		// create box with border, image and inline HTML and add it to the main window
		var box=new wicdPhpGuiWindowsElement({
			'theme':theme,
			'hasBorder':true
		});
		box.addImage('/image.png', 'Alt text');
		box.appendInlineHTML('<h1>Inline HTML</h1>');
		main.appendObjectHTML(box.getContent()).createLineBreak();

		// create button
		main.createButton('Nice button', function(){
			alert('See the message in console');
			main.hideWindow();
		});

		// window is ready to display
		main.createWindow().displayWindow();

	// create about window
		// initialize floating window
		var about=new wicdPhpGuiWindows({
			'containerId':'app',
			'windowId':'aboutWindow',
			'theme':theme,
			'width':'600px',
			'height':'600px',
			'title':'About',
			'isFloat':true,
			'isClosable':true,
			'hasCloseButton':true
		});

		// add text
		about.appendInlineHTML('<h1 style="text-align: center;">About</h1><div style="text-align: center;">Sample text</div>');

		// create button at the bottom
		about.createBottomButton('OK', function(){
			about.hideWindow();
		});

		// create text label at the bottom
		about.createBottomLabel('Made by me :)');

		// now push the window to the app container
		about.createWindow();
 */

class wicdPhpGuiWindows
{
	/*
	 * wicdPhpGuiWindows - controller
	 *
	 * Warning:
	 *  wicdPhpGuiWindowsLayout and wicdPhpGuiWindowsElement classes required
	 *  constructor accepts associative array only
	 *  createWindow() ends the window building
	 *
	 * Available methods - elements:
	 *  see wicdPhpGuiWindowsElement class
	 *
	 * Available methods - window:
	 *  createWindow()
	 *   create window div and push it into container
	 *  displayWindow()
	 *   remove display=none from window div
	 *  displayTimeoutWindow(int_timeoutInMs)
	 *   displayWindow() and run hideWindow() after timeout
	 *  hideWindow()
	 *   add display=none to window div
	 *  destroyWindow()
	 *   remove window from container
	 *  displayAnotherWindow(string_anotherElementId)
	 *   run displayWindow() for another window
	 *
	 * Available methods - window layout:
	 *  createMenuButton(string_label, [callback_onclick])
	 *   add button before content
	 *  createBottomButton(string_label, [callback_onclick])
	 *   add button on the bottom (like Apply, Cancel, OK, etc)
	 *   warning: reserved for floating windows only
	 *  createBottomLabel(string_label, [string_styleParameter])
	 *   add text label in left lower corner
	 *   warning: reserved for floating windows only
	 *
	 * Required constructor parameters:
	 *  containerId [string]
	 *   main div
	 *  theme [object]
	 *   like new wicdPhpGuiWindowsDefault();
	 *  windowId [string]
	 *   current window div id
	 *  width [string]
	 *   like 300px
	 *  height [string]
	 *
	 * Optional constructor parameters:
	 *  title [string]
	 *   for title bar
	 *  isFloat [bool]
	 *   set true to display window centered above another elements
	 *  isClosable [bool]
	 *   set true to allow hiding window
	 *  hasCloseButton [bool]
	 *   set true to display close button on the right upper corner
	 */

	#containerObject;
	#layoutObject=new wicdPhpGuiWindowsLayout();
	#themeObject;
	#elementsObject;

	#elementId;

	#windowTitle='';
	#isWindowFloat=false;
	#isWindowClosable=false;
	#hasCloseButton=false;
	#windowWidth;
	#windowHeight;

	#windowContent=document.createElement('div');
	#menuContent=null;
	#bottomButtonsContent=null;
	#bottomLabelContent=null;

	constructor(params)
	{
		this.#containerObject=document.getElementById(params.containerId);
		this.#themeObject=params.theme;
		this.#elementsObject=new wicdPhpGuiWindowsElement({
			'theme':params.theme
		});

		this.#elementId=params.windowId;

		if(typeof params.title !== undefined)
			this.#windowTitle=params.title;
		if(typeof params.isFloat !== undefined)
			this.#isWindowFloat=params.isFloat;
		if(typeof params.isClosable !== undefined)
			this.#isWindowClosable=params.isClosable;
		if(typeof params.hasCloseButton !== undefined)
			this.#hasCloseButton=params.hasCloseButton;

		this.#windowWidth=params.width;
		this.#windowHeight=params.height;
	}

	addImage(src, alt, style=null)
	{
		this.#windowContent.appendChild(this.#elementsObject.addImage(src, alt, style).getContent().children[0]);
		return this;
	}
	createButton(label, onclick=null)
	{
		this.#windowContent.appendChild(this.#elementsObject.createButton(label, onclick).getContent().children[0]);
		return this;
	}
	createFormCheckBox(label, name=null,)
	{
		this.#windowContent.appendChild(this.#elementsObject.createFormCheckBox(label, name).getContent().children[0]);
		return this;
	}
	createFormInput(type, name, placeholder=null, defaultValue=null)
	{
		this.#windowContent.appendChild(this.#elementsObject.createFormInput(type, name, placeholder, defaultValue).getContent().children[0]);
		return this;
	}
	createHorizontalLine(style=null)
	{
		this.#windowContent.appendChild(this.#elementsObject.createHorizontalLine(style).getContent().children[0]);
		return this;
	}
	createLabel(label, style=null)
	{
		this.#windowContent.appendChild(this.#elementsObject.createLabel(label, style).getContent().children[0]);
		return this;
	}
	createLineBreak()
	{
		this.#windowContent.appendChild(this.#elementsObject.createLineBreak().getContent().children[0]);
		return this;
	}
	appendInlineHTML(content)
	{
		this.#windowContent.innerHTML+=content;
		return this;
	}
	appendObjectHTML(object)
	{
		this.#windowContent.appendChild(object);
		return this;
	}

	createWindow()
	{
		var windowBox=document.createElement('div');
			var titleBox=document.createElement('div');
					var titleSpan=document.createElement('span');
					titleSpan.innerHTML=this.#windowTitle;
					this.#layoutObject.titleSpan(titleSpan); this.#themeObject.titleSpan(titleSpan);
					titleBox.appendChild(titleSpan);

					if(this.#hasCloseButton)
					{
						var titleCloseButton=document.createElement('button');
						titleCloseButton.innerHTML='X';
						var titleCloseButtonCurrentWindow=this;
						titleCloseButton.addEventListener('click', function(a, b=titleCloseButtonCurrentWindow){
							b.hideWindow();
						});
						this.#layoutObject.titleCloseButton(titleCloseButton); this.#themeObject.titleCloseButton(titleCloseButton);
						titleBox.appendChild(titleCloseButton);
					}

				this.#layoutObject.titleBox(titleBox); this.#themeObject.titleBox(titleBox);
				windowBox.appendChild(titleBox);

			if(this.#menuContent !== null)
			{
				this.#layoutObject.menuContent(this.#menuContent); this.#themeObject.menuContent(this.#menuContent);
				windowBox.appendChild(this.#menuContent);
				windowBox.appendChild(document.createElement('hr'));
			}

			this.#layoutObject.windowContent(this.#windowContent); this.#themeObject.windowContent(this.#windowContent);
			windowBox.appendChild(this.#windowContent);

			if(this.#bottomButtonsContent !== null)
			{
				this.#layoutObject.bottomButtons(this.#bottomButtonsContent); this.#themeObject.bottomButtons(this.#bottomButtonsContent);
				windowBox.appendChild(this.#bottomButtonsContent);
			}
			if(this.#bottomLabelContent !== null)
			{
				this.#layoutObject.bottomLabel(this.#bottomLabelContent); this.#themeObject.bottomLabel(this.#bottomLabelContent);
				windowBox.appendChild(this.#bottomLabelContent);
			}

		windowBox.setAttribute('id', this.#elementId);

		if(this.#isWindowFloat)
			this.#layoutObject.windowFloat(windowBox);
		else
			this.#layoutObject.window(windowBox);

		this.#layoutObject.window(windowBox); this.#themeObject.window(windowBox);
		windowBox.style.width=this.#windowWidth;
		windowBox.style.height=this.#windowHeight;
		windowBox.style.display='none';

		this.#containerObject.appendChild(windowBox);

		return this;
	}
	displayWindow()
	{
		this.#containerObject.querySelector('#'+this.#elementId).style.display='block';
	}
	displayTimeoutWindow(timeout)
	{
		if(this.#isWindowClosable)
		{
			this.displayWindow();

			var that=this;
			setTimeout(function(){
				that.hideWindow();
			}, timeout);
		}
		else
		{
			this.displayWindow();
			console.warn('displayTimeoutWindow() is not permitted for '+this.#elementId+', displayWindow() was used');
		}
	}
	hideWindow()
	{
		if(this.#isWindowClosable)
			this.#containerObject.querySelector('#'+this.#elementId).style.display='none';
		else
			console.error('hideWindow() is not permitted for '+this.#elementId);
	}
	destroyWindow()
	{
		this.#containerObject.querySelector('#'+this.#elementId).remove();
	}
	createMenuButton(label, onclick=null)
	{
		if(this.#menuContent === null)
			this.#menuContent=document.createElement('div');

		var button=document.createElement('button');
		button.innerHTML=label;
		if(onclick !== null)
		{
			var currentWindow=this;
			button.addEventListener('click', function(a, b=onclick, c=currentWindow){
				b(c);
			});
		}

		this.#layoutObject.button(button); this.#themeObject.button(button);
		this.#menuContent.appendChild(button);

		return this;
	}
	createBottomButton(label, onclick=null)
	{
		if(this.#isWindowFloat === true)
		{
			if(this.#bottomButtonsContent === null)
				this.#bottomButtonsContent=document.createElement('div');

			var button=document.createElement('button');
			button.innerHTML=label;
			if(onclick !== null)
			{
				var currentWindow=this;
				button.addEventListener('click', function(a, b=onclick, c=currentWindow){
					b(c);
				});
			}

			this.#layoutObject.button(button); this.#themeObject.button(button);
			this.#bottomButtonsContent.appendChild(button);

			return this;
		}

		console.error(this.#elementId+': createBottomButton() is for floating windows only');
	}
	createBottomLabel(label, style=null)
	{
		if(this.#isWindowFloat === true)
		{
			var labelBox=document.createElement('div');
			labelBox.innerHTML=label;
			if(style !== null)
				labelBox.setAttribute('style', style);

			this.#bottomLabelContent=labelBox;

			return this;
		}

		console.error(this.#elementId+': createBottomLabel() is for floating windows only');
	}

	displayAnotherWindow(anotherElementId)
	{
		this.#containerObject.querySelector('#'+anotherElementId).style.display='block';
	}
}
class wicdPhpGuiWindowsElement
{
	/*
	 * wicdPhpGuiWindows - window elements provider
	 *
	 * Warning:
	 *  wicdPhpGuiWindowsLayout class required
	 *  constructor accepts associative array only
	 *
	 * Available methods - elements (shared with wicdPhpGuiWindows):
	 *  addImage(string_src, string_alt, [string_styleParameter])
	 *  createButton(string_label, [callback_onclick])
	 *  createFormCheckBox(string_label, [string_nameParameter])
	 *  createFormInput(string_type, string_name, [string_placeholder], [string_defaultValue])
	 *  createHorizontalLine([string_styleParameter])
	 *  createLabel(string_label, [string_styleParameter])
	 *   text inside span
	 *  createLineBreak()
	 *  appendInlineHTML(string_content)
	 *  appendObjectHTML(dom_object)
	 *
	 * Get built element: getContent()
	 *
	 * Required constructor parameters:
	 *  theme [object]
	 *
	 * Optional constructor parameters:
	 *  hasBorder [bool]
	 *   will be div with border if true, or span if false
	 */

	#layoutObject=new wicdPhpGuiWindowsLayout();
	#themeObject;

	#elementContent;
	#hasBorder=false;

	#windowContent; // means elementContent in this class

	constructor(params)
	{
		this.#themeObject=params.theme;

		if(typeof params.hasBorder !== undefined)
			this.#hasBorder=params.hasBorder;

		this.#initContent();
	}
	#initContent()
	{
		if(this.#hasBorder)
		{
			this.#windowContent=document.createElement('div');
			this.#themeObject.boxWithBorder(this.#windowContent);
		}
		else
			this.#windowContent=document.createElement('span');
	}

	addImage(src, alt, style=null)
	{
		var image=document.createElement('img');
		image.setAttribute('src', src);
		image.setAttribute('alt', alt);
		if(style !== null)
			image.setAttribute('style', style);

		this.#windowContent.appendChild(image);

		return this;
	}
	createButton(label, onclick=null)
	{
		var button=document.createElement('button');
		button.innerHTML=label;
		if(onclick !== null)
			button.addEventListener('click', function(a, b=onclick){
				b();
			});

		this.#layoutObject.button(button); this.#themeObject.button(button);
		this.#windowContent.appendChild(button);

		return this;
	}
	createFormCheckBox(label, name=null)
	{
		var mainBox=document.createElement('span');
			var checkBox=document.createElement('input');
			checkBox.setAttribute('type', 'checkbox');
			if(name !== null)
				checkBox.setAttribute('name', name);

			mainBox.appendChild(checkBox);
			mainBox.innerHTML+=label;

		this.#windowContent.appendChild(mainBox);

		return this;
	}
	createFormInput(type, name, placeholder=null, defaultValue=null)
	{
		var inputForm=document.createElement('input');
		inputForm.setAttribute('type', type);
		inputForm.setAttribute('name', name);

		if(placeholder !== null)
			inputForm.setAttribute('placeholder', placeholder);

		if(defaultValue !== null)
			inputForm.setAttribute('value', defaultValue);

		this.#windowContent.appendChild(inputForm);

		return this;
	}
	createHorizontalLine(style=null)
	{
		var hr=document.createElement('hr');
		if(style !== null)
			hr.setAttribute('style', style);

		this.#windowContent.appendChild(hr);

		return this;
	}
	createLabel(label, style=null)
	{
		var span=document.createElement('span');
		span.innerHTML=label;

		if(style !== null)
			span.setAttribute('style', style);

		this.#windowContent.appendChild(span);

		return this;
	}
	createLineBreak()
	{
		this.#windowContent.appendChild(document.createElement('br'));
		return this;
	}
	appendInlineHTML(content)
	{
		this.#windowContent.innerHTML+=content;
		return this;
	}
	appendObjectHTML(object)
	{
		this.#windowContent.appendChild(object);
		return this;
	}

	getContent()
	{
		var content=this.#windowContent;
		this.#initContent();

		return content;
	}
}
class wicdPhpGuiWindowsLayout
{
	/*
	 * wicdPhpGuiWindows - window layout provider
	 *
	 * HTML layout:
		<div id="elementId"><!-- window || windowFloat -->
			<div><!-- titleBox -->
				<span>title</span><!-- titleSpan -->
				<button>X</button><!-- titleCloseButton -->
			</div>
			<div><!-- menuContent -->
				<button>Menu button</button><!-- button -->
			</div>
			<div></div><!-- windowContent -->
			<div><!-- bottomButtons -->
				<button>Apply</button><!-- button -->
			</div>
			<div>Text label</div><!-- bottomLabel -->
		</div>
	 */

	window() {}
	windowFloat(windowObject)
	{
		windowObject.style.position='absolute';
		windowObject.style.top='0';
		windowObject.style.bottom='0';
		windowObject.style.left='0';
		windowObject.style.right='0';
		windowObject.style.margin='auto';
	}
	titleBox(titleBoxObject)
	{
		titleBoxObject.style.overflow='auto';
	}
	titleSpan(titleSpanObject)
	{
		titleSpanObject.style.paddingLeft='5px';
	}
	titleCloseButton(titleCloseButtonObject)
	{
		titleCloseButtonObject.style.display='inline';
		titleCloseButtonObject.style.float='right';
	}
	menuContent(menuContentObject)
	{
		menuContentObject.style.margin='5px';
	}
	windowContent(windowContentObject)
	{
		windowContentObject.style.margin='5px';
	}
	bottomButtons(bottomButtonsObject)
	{
		bottomButtonsObject.style.position='absolute';
		bottomButtonsObject.style.bottom='0';
		bottomButtonsObject.style.right='0';
		bottomButtonsObject.style.padding='5px';
		bottomButtonsObject.style.overflow='auto';
	}
	bottomLabel(bottomLabelObject)
	{
		bottomLabelObject.style.position='absolute';
		bottomLabelObject.style.bottom='3px';
		bottomLabelObject.style.left='3px';
	}

	button(buttonObject)
	{
		buttonObject.style.marginRight='5px';
	}
}

class wicdPhpGuiWindowsDefault
{
	/*
	 * wicdPhpGuiWindows - theme plugin
	 * default (gray)
	 */

	window(windowObject)
	{
		windowObject.style.backgroundColor='#bbbbbb';
		windowObject.style.color='#000000';
	}
	titleBox(titleBoxObject)
	{
		titleBoxObject.style.backgroundColor='#000099';
	}
	titleSpan(titleSpanObject)
	{
		titleSpanObject.style.color='#ffffff';
		titleSpanObject.style.fontWeight='bold';
	}
	titleCloseButton(titleCloseButtonObject)
	{
		this.button(titleCloseButtonObject);
	}
	menuContent() {}
	windowContent() {}
	bottomButtons() {}
	bottomLabel() {}

	button(buttonObject)
	{
		buttonObject.style.border='1px solid #666666';
		buttonObject.style.borderRadius='15px';
	}
	boxWithBorder(boxWithBorderObject)
	{
		boxWithBorderObject.style.border='1px solid #000000';
	}
}
class wicdPhpGuiWindowsBright
{
	/*
	 * wicdPhpGuiWindows - theme plugin
	 * bright
	 */

	window(windowObject)
	{
		windowObject.style.backgroundColor='#eeedd0';
		windowObject.style.border='1px solid #aaaaaa';
		windowObject.style.borderRadius='5px';
		windowObject.style.color='#000000';
	}
	titleBox(titleBoxObject)
	{
		titleBoxObject.style.backgroundColor='#000099';
		titleBoxObject.style.borderRadius='5px';
	}
	titleSpan(titleSpanObject)
	{
		titleSpanObject.style.color='#ffffff';
		titleSpanObject.style.fontWeight='bold';
	}
	titleCloseButton(titleCloseButtonObject)
	{
		this.button(titleCloseButtonObject);
	}
	menuContent() {}
	windowContent() {}
	bottomButtons() {}
	bottomLabel() {}

	button(buttonObject)
	{
		buttonObject.style.border='1px solid #000000';
		buttonObject.style.borderRadius='15px';
		buttonObject.style.backgroundColor='#fffee0';
	}
	boxWithBorder(boxWithBorderObject)
	{
		boxWithBorderObject.style.border='1px solid #aaaaaa';
	}
}