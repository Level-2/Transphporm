<?php
use Transphporm\Builder;
class TransphpormTest extends PHPUnit_Framework_TestCase {

	public function testContentSimple() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		$css = 'ul li {content: data(user);}';


		$data = new \stdclass;
		$data->user = 'tom';

		
		$template = new Builder($template, $css);
		
		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output($data)['body']); 
	}


	public function testContentObject() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		$css = 'ul li {content: data(user.name);}';


		$data = new stdclass;
		$data->user = new stdclass;
		$data->user->name = 'tom';

		
		$template = new \Transphporm\Builder($template, $css);
		
		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output($data)['body']); 
	}


	public function testRepeatBasic() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		//When using repeat to repeat some data, set the content to the data for the iteration
		$css = 'ul li {repeat: data(list); content: iteration()}';


		$data = new stdclass;
		$data->list = ['One', 'Two', 'Three'];

		
		$template = new \Transphporm\Builder($template, $css);
		
		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output($data)['body']); 
	}


	public function testRepeatObject() {
		$template = '
				<ul><li>TEST1</li></ul>
		';


		//This time read a specific value from the data of the current iteration
		$css = 'ul li {repeat: data(list); content: iteration(id)}';


		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->id = 'One';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->id = 'Two';
		$data->list[] = $two;

		$three = new stdclass;		
		$three->id = 'Three';
		$data->list[] = $three;
		
		$template = new \Transphporm\Builder($template, $css);
		
		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output($data)['body']); 
	}

	private function stripTabs($str) {
		return trim(str_replace(["\t", "\n", "\r"], '', $str));
	}

	public function testRepeatObjectChildNode() {
		$template = '
				<ul>
					<li>
						<span>TEST1</span>
					</li>
				</ul>
		';

		//Rather than setting the value to the 
		$css = 'ul li {repeat: data(list);}
		ul li span {content: iteration(id)}';


		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->id = 'One';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->id = 'Two';
		$data->list[] = $two;

		$three = new stdclass;		
		$three->id = 'Three';
		$data->list[] = $three;
		
		$template = new \Transphporm\Builder($template, $css);
		
		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<span>One</span>
			</li><li>
				<span>Two</span>
			</li><li>
				<span>Three</span>
			</li>
		</ul>') ,$this->stripTabs($template->output($data)['body'])); 
	}

	public function testRepeatObjectChildNodes() {
		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->name = 'One';
		$one->id = '1';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->name = 'Two';
		$two->id = '2';
		$data->list[] = $two;

		$three = new stdclass;
		$three->name = 'Three';
		$three->id = '3';
		$data->list[] = $three;


		$template = '
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }';


		$template = new \Transphporm\Builder($template, $css);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1</h2>
				<span>One</span>
			</li><li>
				<h2>2</h2>
				<span>Two</span>
			</li><li>
				<h2>3</h2>
				<span>Three</span>
			</li>			
		</ul>'), $this->stripTabs($template->output($data)['body']));

	}


	public function testQuotedContent() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "TEST";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>TEST</h1>', $template->output()['body']);
	}


	public function testQuotedContentWithEscape() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "TEST\"TEST";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>TEST"TEST</h1>', $template->output()['body']);
	}

	public function testMultipleContentValues() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "A", "B";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>AB</h1>', $template->output()['body']);
	}


	public function testMatchClassAndTag() {
		$template = '<h1>Test 1</h1><h1 class="test">Heading</h1><h1>Test 2</h1>';

		$tss = 'h1.test {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>Test 1</h1><h1 class="test">REPLACED</h1><h1>Test 2</h1>', $template->output()['body']);
	}

	public function testMatchClassChild() {
		$template = '
		<div>
			<span class="foo">test</span>
			<span class="bar">test</span>
		</div>
		';

		$tss = 'div .foo {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss);
		$this->assertEquals($this->stripTabs('<div>
			<span class="foo">REPLACED</span>
			<span class="bar">test</span>
		</div>'), $this->stripTabs($template->output()['body']));
	}

	public function testChildNodeMatcher() {
		$template = '
		<div>
			<span class="foo">test</span>
			<span class="bar">test</span>
		</div>
		';

		$tss = 'div > .foo {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss);
		$this->assertEquals($this->stripTabs('<div>
			<span class="foo">REPLACED</span>
			<span class="bar">test</span>
		</div>'), $this->stripTabs($template->output()['body']));
	}


	public function testAttributeSelector() {
		$template = '
		<div>
			<textarea name="foo">foo</textarea>
			<textarea>bar</textarea>
		</div>
		';

		$tss = '[name="foo"] {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss);
		$this->assertEquals($this->stripTabs('<div>
			<textarea name="foo">REPLACED</textarea>
			<textarea>bar</textarea>
		</div>'), $this->stripTabs($template->output()['body']));
	}


	//check that it's not due to the order of the HTML
	public function testAttributeSelectorB() {
		$template = '
		<div>
			<textarea>bar</textarea>
			<textarea name="foo">foo</textarea>			
		</div>
		';

		$tss = '[name="foo"] {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss);
		$this->assertEquals($this->stripTabs('<div>
			<textarea>bar</textarea>
			<textarea name="foo">REPLACED</textarea>
		</div>'), $this->stripTabs($template->output()['body']));
	}


	public function testAttributeSelectorC() {
		$template = '
		<div>
			<a name="foo">a link</a>
			<textarea name="foo">foo</textarea>			
		</div>
		';

		$tss = 'textarea[name="foo"] {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss);
		$this->assertEquals($this->stripTabs('		<div>
			<a name="foo">a link</a>
			<textarea name="foo">REPLACED</textarea>			
		</div>'), $this->stripTabs($template->output()['body']));
	}


	public function testDisplayNone() {
		$template = '
		<div>
			<a name="foo">a link</a>
			<textarea name="foo">foo</textarea>			
		</div>
		';

		$tss = 'textarea[name="foo"] {display: none;}';

		$template = new \Transphporm\Builder($template, $tss);
		$this->assertEquals($this->stripTabs('		<div>
			<a name="foo">a link</a>

		</div>'), $this->stripTabs($template->output()['body']));
	}

	public function testBefore() {
		$template =  '
		<div>Test</div>
		';

		$tss = 'div:before {content: "BEFORE";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div>BEFORETest</div>'), $this->stripTabs($template->output()['body']));
	}

	public function testAfter() {
		$template =  '
		<div>Test</div>
		';

		$tss = 'div:after {content: "AFTER";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div>TestAFTER</div>'), $this->stripTabs($template->output()['body']));
	}


	public function testIterationPseudo() {
		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->name = 'One';
		$one->id = '1';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->name = 'Two';
		$two->id = '2';
		$data->list[] = $two;

		$three = new stdclass;
		$three->name = 'Three';
		$three->id = '3';
		$data->list[] = $three;


		$template = '
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }
		ul li span:iteration[id="2"] {display: none;}
		';


		$template = new \Transphporm\Builder($template, $css);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1</h2>
				<span>One</span>
			</li><li>
				<h2>2</h2>

			</li><li>
				<h2>3</h2>
				<span>Three</span>
			</li>			
		</ul>'), $this->stripTabs($template->output($data)['body']));

	}


	public function testMultiPseudo() {
		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->name = 'One';
		$one->id = '1';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->name = 'Two';
		$two->id = '2';
		$data->list[] = $two;

		$three = new stdclass;
		$three->name = 'Three';
		$three->id = '3';
		$data->list[] = $three;


		$template = '
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }
		ul li span:iteration[id="2"]:before {content: "BEFORE";}
		';


		$template = new \Transphporm\Builder($template, $css);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1</h2>
				<span>One</span>
			</li><li>
				<h2>2</h2>
				<span>BEFORETwo</span>				
			</li><li>
				<h2>3</h2>
				<span>Three</span>
			</li>			
		</ul>'), $this->stripTabs($template->output($data)['body']));

	}

	public function testNthChild() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(2) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<ul>
				<li>One</li>
				<li>REPLACED</li>
				<li>Three</li>
				<li>Four</li>
			</ul>'), $this->stripTabs($template->output()['body']));


	}

	public function testNthChildOdd() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(odd) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<ul>
				<li>REPLACED</li>
				<li>Two</li>
				<li>REPLACED</li>
				<li>Four</li>
			</ul>'), $this->stripTabs($template->output()['body']));

	}

	public function testNthChildEven() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(even) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<ul>
				<li>One</li>
				<li>REPLACED</li>
				<li>Three</li>
				<li>REPLACED</li>
			</ul>'), $this->stripTabs($template->output()['body']));

	}

	public function testReadAttribute() {
		$template = '
			<div class="fromattribute">Test</div>
		';

		$tss = 'div {content: attr(class); }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="fromattribute">fromattribute</div>', $template->output()['body']);
	}


	public function testWriteAttribute() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div:attr(class) {content: "classname"; }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="classname">Test</div>', $template->output()['body']);
	}

	public function testComments() {
			$template = '
			<div>Test</div>
		';

		$tss = '/* test */ div {content: "foo"; }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>foo</div>', $template->output()['body']);
	}

	public function testComments2() {
		$template = '
			<div>Test</div>
		';

		$tss = '/* div {content: "foo"; } */';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>Test</div>', $template->output()['body']);
	}

	public function testImport() {
		$template = '
			<div>Test</div>
		';

		$file = __DIR__ . DIRECTORY_SEPARATOR . 'import.tss';
		$tss = "
			@import '$file';
		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>foo</div>', $template->output()['body']);
	}

	public function testContentTemplate() {
		$template = '
			<div>Test</div>
		';

		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'include.xml';

		$tss = "div {content: template($includeFile); }";
		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div><p>foo</p></div>', $this->stripTabs($template->output()['body']));
	}

	public function testNestedFunction() {
		//Reads from the data using an attribute from the HTML
		//In this case, sets the input's value attribute by reading it's name from data
		// attr(name) reads the value of the name attribute from the input
		// data(foo) reads the `foo` property from the supplied data
		// so data(attr(name)) will read the data with the key of the element's name attribute
		// input:attr(value) {content: "text"} will set the content of the value attribute
		// Putting all this together allows us to fill all inputs with a single TSS command

		$template = '<form>
			<input type="text" name="one" />
			<input type="text" name="two" />
		</form>';

		$data = ['one' => 'VALUE-OF-ONE', 'two' => 'VALUE-OF-TWO'];

		$tss = 'input:attr(value) {content: data(attr(name));}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<form>
			<input type="text" name="one" value="VALUE-OF-ONE" />
			<input type="text" name="two" value="VALUE-OF-TWO" />
		</form>'), $this->stripTabs($template->output($data)['body']));
	}


	public function testBind() {
		//Binds a specific pice of data (which may be an array to an element)
		//All calls to data() for child elements will use this as the root data object

		$template = '<form>
			<input type="text" name="one" />
			<input type="text" name="two" />
		</form>';

		//This time, the form data isn't the root data() object

		$data = ['formdata' => ['one' => 'VALUE-OF-ONE', 'two' => 'VALUE-OF-TWO']];

		//First bind the form data to the form element, then populate the inputs using it.
		//For inputs, as they're children of the form element, will use this data
		$tss = '
		form {bind: data(formdata);}
		input:attr(value) {content: data(attr(name));}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<form>
			<input type="text" name="one" value="VALUE-OF-ONE" />
			<input type="text" name="two" value="VALUE-OF-TWO" />
		</form>'), $this->stripTabs($template->output($data)['body']));

	}

	public function testFormatNumber() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "1.234567"; format: decimal 2;}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>1.23</div>', $this->stripTabs($template->output()['body']));


	}

	public function testFormatString() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "test"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>TEST</div>', $this->stripTabs($template->output()['body']));


	}

	public function testSemicolonInString() {

		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "te;st"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>TE;ST</div>', $this->stripTabs($template->output()['body']));
	}


	public function testColonInString() {

		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "te:st"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>TE:ST</div>', $this->stripTabs($template->output()['body']));
	}


	public function testSemicolonInStrings() {

		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "t;e;s;t", "t;w;o"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>T;E;S;TT;W;O</div>', $this->stripTabs($template->output()['body']));
	}

	public function testMultipleReadIteration() {
		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->name = 'One';
		$one->id = '1';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->name = 'Two';
		$two->id = '2';
		$data->list[] = $two;

		$three = new stdclass;
		$three->name = 'Three';
		$three->id = '3';
		$data->list[] = $three;


		$template = '
				<ul>
					<li>
						<h2>header</h2>
					</li>
				</ul>
		';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id), " ", iteration(name); }';


		$template = new \Transphporm\Builder($template, $css);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1 One</h2>
			</li><li>
				<h2>2 Two</h2>
			</li><li>
				<h2>3 Three</h2>
			</li>			
		</ul>'), $this->stripTabs($template->output($data)['body']));

	}

}



