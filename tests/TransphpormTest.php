<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
use Transphporm\Builder;
class TransphpormTest extends PHPUnit_Framework_TestCase {

	public function testLoadHTMLWithEntities() {
		$template = '<div>&nbsp; &lt;</div>';


		$template = new Builder($template);

		$this->assertEquals('<div>' . html_entity_decode('&nbsp;') . ' &lt;</div>', $template->output()->body);
	}

	public function testLoadHTMLUnclosed() {
		$template = '<div><img src="foo.jpg"></div>';



		$template = new Builder($template);

		$this->assertEquals('<div><img src="foo.jpg" /></div>', $template->output()->body);
	}


	public function testContentSimple() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		$css = 'ul li {content: data(user);}';


		$data = new \stdclass;
		$data->user = 'tom';


		$template = new Builder($template, $css);

		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output($data)->body);
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

		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output($data)->body);
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

		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output($data)->body);
	}


	public function testRepeatMax() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		//When using repeat to repeat some data, set the content to the data for the iteration
		$css = 'ul li {repeat: data(list) 2; content: iteration()}';


		$data = new stdclass;
		$data->list = ['One', 'Two', 'Three'];


		$template = new \Transphporm\Builder($template, $css);

		$this->assertEquals('<ul><li>One</li><li>Two</li></ul>' ,$template->output($data)->body);
	}



	public function testRepeatMaxDynamic() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		//When using repeat to repeat some data, set the content to the data for the iteration
		$css = 'ul li {repeat: data(list) data(max); content: iteration()}';


		$data = new stdclass;
		$data->list = ['One', 'Two', 'Three'];
		$data->max = 1;

		$template = new \Transphporm\Builder($template, $css);

		$this->assertEquals('<ul><li>One</li></ul>' ,$template->output($data)->body);
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

		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output($data)->body);
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
		</ul>') ,$this->stripTabs($template->output($data)->body));
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
		</ul>'), $this->stripTabs($template->output($data)->body));

	}


	public function testQuotedContent() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "TEST";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>TEST</h1>', $template->output()->body);
	}


	public function testQuotedContentWithEscape() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "TEST\"TEST";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>TEST"TEST</h1>', $template->output()->body);
	}

	public function testMultipleContentValues() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "A", "B";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>AB</h1>', $template->output()->body);
	}


	public function testMatchClassAndTag() {
		$template = '<h1>Test 1</h1><h1 class="test">Heading</h1><h1>Test 2</h1>';

		$tss = 'h1.test {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<h1>Test 1</h1><h1 class="test">REPLACED</h1><h1>Test 2</h1>', $template->output()->body);
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
		</div>'), $this->stripTabs($template->output()->body));
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
		</div>'), $this->stripTabs($template->output()->body));
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
		</div>'), $this->stripTabs($template->output()->body));
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
		</div>'), $this->stripTabs($template->output()->body));
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
		</div>'), $this->stripTabs($template->output()->body));
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

		</div>'), $this->stripTabs($template->output()->body));
	}

	public function testBefore() {
		$template =  '
		<div>Test</div>
		';

		$tss = 'div:before {content: "BEFORE";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div>BEFORETest</div>'), $this->stripTabs($template->output()->body));
	}

	public function testAfter() {
		$template =  '
		<div>Test</div>
		';

		$tss = 'div:after {content: "AFTER";}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div>TestAFTER</div>'), $this->stripTabs($template->output()->body));
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
		</ul>'), $this->stripTabs($template->output($data)->body));

	}


	public function testIterationPseudoNotEquals() {
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
		ul li span:iteration[id!="2"] {display: none;}
		';


		$template = new \Transphporm\Builder($template, $css);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1</h2>
			</li><li>
				<h2>2</h2>
				<span>Two</span>
			</li><li>
				<h2>3</h2>
			</li>
		</ul>'), $this->stripTabs($template->output($data)->body));

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
		</ul>'), $this->stripTabs($template->output($data)->body));

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
			</ul>'), $this->stripTabs($template->output()->body));


	}

	public function testNthChild2() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(3) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<ul>
				<li>One</li>
				<li>Two</li>
				<li>REPLACED</li>
				<li>Four</li>
			</ul>'), $this->stripTabs($template->output()->body));

	}

	public function testNthChild3() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(1) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<ul>
				<li>REPLACED</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>'), $this->stripTabs($template->output()->body));

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
			</ul>'), $this->stripTabs($template->output()->body));

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
			</ul>'), $this->stripTabs($template->output()->body));

	}

	public function testReadAttribute() {
		$template = '
			<div class="fromattribute">Test</div>
		';

		$tss = 'div {content: attr(class); }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="fromattribute">fromattribute</div>', $template->output()->body);
	}


	public function testWriteAttribute() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div:attr(class) {content: "classname"; }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="classname">Test</div>', $template->output()->body);
	}

	public function testComments() {
			$template = '
			<div>Test</div>
		';

		$tss = '/* test */ div {content: "foo"; }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>foo</div>', $template->output()->body);
	}

	public function testComments2() {
		$template = '
			<div>Test</div>
		';

		$tss = '/* div {content: "foo"; } */';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>Test</div>', $template->output()->body);
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

		$this->assertEquals('<div>foo</div>', $template->output()->body);
	}

	public function testImportDynamic() {
		$template = '
			<div>Test</div>
		';


		$tss = "
			@import data(filename);
		";

		$data = [
			'filename' => __DIR__  . DIRECTORY_SEPARATOR . 'import.tss'
		];

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>foo</div>', $template->output($data)->body);
	}

	public function testImportMiddleOfFile() {
		$template = '
			<span>foo</span>
			<div>Test</div>
			<h1>foo</h1>
		';

		$file = __DIR__ . DIRECTORY_SEPARATOR . 'import.tss';
		$tss = "
			span {content: 'test1';}
			@import '$file';
			h1 {content: 'h1';}
		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<span>test1</span><div>foo</div><h1>h1</h1>'), $this->stripTabs($template->output()->body));
	}

	public function testMultiImport() {
		$template = '
			<span>test</span>
			<div>Test</div>
			<h1>foo</h1>
		';

		$file = __DIR__ . DIRECTORY_SEPARATOR . 'import.tss';
		$file2 = __DIR__ . DIRECTORY_SEPARATOR . 'import2.tss';
		$tss = "
			span {content: 'test1';}
			@import '$file';
			h1 {content: 'h1';}
			@import '$file2';
		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<span>bar</span><div>foo</div><h1>h1</h1>'), $this->stripTabs($template->output()->body));

	}


	public function testMultiImportTogether() {
		$template = '
			<span>test</span>
			<div>Test</div>
			<h1>foo</h1>
		';

		$file = __DIR__ . DIRECTORY_SEPARATOR . 'import.tss';
		$file2 = __DIR__ . DIRECTORY_SEPARATOR . 'import2.tss';
		$tss = "
			span {content: 'test1';}
			@import '$file';
			@import '$file2';
			h1 {content: 'h1';}

		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<span>bar</span><div>foo</div><h1>h1</h1>'), $this->stripTabs($template->output()->body));

	}

	public function testContentTemplate() {
		$template = '
			<div>Test</div>
		';

		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'include.xml';
		$includeFile = str_replace('\\', '/', $includeFile);

		$tss = "div {content: template('$includeFile'); }";
		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div><p>foo</p></div>', $this->stripTabs($template->output()->body));
	}

	public function testContentTemplateFromData() {
		$template = '
			<div>Test</div>
		';

		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'include.xml';
		$includeFile = str_replace('\\', '/', $includeFile);

		$data = new \stdClass;
		$data->includeFile = $includeFile;

		$tss = "div {content: template(data(includeFile)); }";
		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div><p>foo</p></div>', $this->stripTabs($template->output($data)->body));
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
		</form>'), $this->stripTabs($template->output($data)->body));
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
		</form>'), $this->stripTabs($template->output($data)->body));

	}

	public function testFormatNumber() {
		$template = '
			<div>Test</div>
		';;
		$tss = 'div {content: "1.234567"; format: decimal 2;}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>1.23</div>', $this->stripTabs($template->output()->body));


	}

	public function testFormatCurrency() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "1.234567"; format: currency;}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>£1.23</div>', $this->stripTabs($template->output()->body));


	}


	public function testFormatCurrencyAfter() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "1.234567"; format: currency;}';

		$template = new \Transphporm\Builder($template, $tss);
		$locale = json_decode(file_get_contents('src/Formatter/Locale/enGB.json'), true);
		$locale['currency_position'] = 'after';
		$template->loadModule(new \Transphporm\Module\Format($locale));

		$this->assertEquals('<div>1.23£</div>', $this->stripTabs($template->output()->body));
	}

	public function testFormatStringUpper() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "test"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);
		$template->loadModule(new \Transphporm\Module\Format);
		$this->assertEquals('<div>TEST</div>', $this->stripTabs($template->output()->body));


	}


	public function testFormatStringLower() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "test"; format: lowercase}';

		$template = new \Transphporm\Builder($template, $tss);
		$template->loadModule(new \Transphporm\Module\Format);
		$this->assertEquals('<div>test</div>', $this->stripTabs($template->output()->body));


	}

	public function testCustomFormatter() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "test"; format: reverse}';

		$template = new \Transphporm\Builder($template, $tss);
		require_once 'tests/ReverseFormatter.php';
		$template->loadModule(new ReverseFormatterModule);

		$this->assertEquals('<div>tset</div>', $this->stripTabs($template->output()->body));

	}

	public function testFormatStringTitle() {
		$template = '
			<div>test</div>
		';

		$tss = 'div {content: "a test title"; format: titlecase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>A Test Title</div>', $this->stripTabs($template->output()->body));


	}


	public function testSemicolonInString() {

		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "te;st"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>TE;ST</div>', $this->stripTabs($template->output()->body));
	}


	public function testColonInString() {

		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "te:st"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>TE:ST</div>', $this->stripTabs($template->output()->body));
	}


	public function testSemicolonInStrings() {

		$template = '
			<div>Test</div>
		';

		$tss = 'div {content: "t;e;s;t", "t;w;o"; format: uppercase}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>T;E;S;TT;W;O</div>', $this->stripTabs($template->output()->body));
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
		</ul>'), $this->stripTabs($template->output($data)->body));

	}

	public function testLoadTssFromFile() {
		$template = new \Transphporm\Builder('tests/external.xml', 'tests/external.tss');
		$this->assertEquals('<div>Content from external TSS</div>', $this->stripTabs($template->output()->body));
	}

	public function testHTTPHeader() {
		$template = '
			<html><div>Test</div></html>
		';

		$tss = 'html:header[location] {content: "/test"}';

		$template = new \Transphporm\Builder($template, $tss);
		$this->assertEquals([['location', '/test']], $template->output()->headers);

	}

	public function testTemplateWithXmlns() {

		$template = new \Transphporm\Builder('tests/namespaced.xml', 'tests/namespaced.tss');

		$this->assertEquals($this->stripTabs('<foo xmlns="http://foo/"><bar>yy</bar></foo>'), $this->stripTabs($template->output()->body));
	}

	public function testFormatDate() {

		$template = '
			<div>test</div>
		';

		$tss = 'div {content: "2015-10-05"; format: date}';

		$template = new \Transphporm\Builder($template, $tss);


		$this->assertEquals($this->stripTabs('<div>05/10/2015</div>'), $this->stripTabs($template->output()->body));
	}

	public function testFormatDateCustom() {

		$template = '
			<div>test</div>
		';

		$tss = 'div {content: "2015-10-05"; format: date "jS M"}';

		$template = new \Transphporm\Builder($template, $tss);


		$this->assertEquals($this->stripTabs('<div>5th Oct</div>'), $this->stripTabs($template->output()->body));
	}

	public function testHTMLFormat() {
		$template = '
			<div>test</div>
		';

		$tss = 'div {content: "<span>foobar</span>"; format: html}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div><span>foobar</span></div>'), $this->stripTabs($template->output()->body));
	}


	public function testIncludeTemplatePartial() {
		$template = '
			<div>test</div>
		';

		$tss = 'div {content: template("tests/include2.xml", ".test"); }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div><span class="test">foobar</span></div>'), $this->stripTabs($template->output()->body));

	}

	public function testBindFormArray() {
		$tss = 'form {bind: data(array); }
				form input[name]:attr(value) {content: data(attr(name))}';

		$xml = '<form><input type="text" name="f1" /><input type="text" name="f2" /></form>';


		$data = ['array' => ['f1' => 'v1', 'f2' => 'v2']];

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<form><input type="text" name="f1" value="v1" /><input type="text" name="f2" value="v2" /></form>'), $this->stripTabs($template->output($data)->body));

	}



	public function testBindFormObject() {
		$tss = 'form {bind: data(object); }
				form input[name]:attr(value) {content: data(attr(name))}';

		$xml = '<form><input type="text" name="f1" /><input type="text" name="f2" /></form>';


		$obj = new stdclass;
		$obj->f1 = 'v1';
		$obj->f2 = 'v2';
		$data = ['object' => $obj];

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<form><input type="text" name="f1" value="v1" /><input type="text" name="f2" value="v2" /></form>'), $this->stripTabs($template->output($data)->body));

	}


	public function testDoctype() {
		$xml = '<!DOCTYPE html><html><body>foo</body></html>';

		$tss = 'body {content: "bar";}';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<!DOCTYPE html><html><body>bar</body></html>', $this->stripTabs($template->output()->body));
	}

	public function testDoctypeFile() {
		file_put_contents(__DIR__ . '/test.xml', '<!DOCTYPE html><html><body>foo</body></html>');

		$xml = __DIR__ . '/test.xml';
		$tss = 'body {content: "bar";}';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<!DOCTYPE html><html><body>bar</body></html>', $this->stripTabs($template->output()->body));
	}

	public function testIterationAndData() {
		$xml = '<ul>
			<li>Foo</li>
		</ul>';

		$tss = 'li {repeat: data(repeat); content: data(fromData), iteration(fromIteration); }';

		$data = new stdclass;
		$data->repeat = [
			[
				'fromIteration' => 1
			],
			[
				'fromIteration' => 2
			],
			[
				'fromIteration' => 3
			]
		];


		$data->fromData = 'TEST';

		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<ul>
			<li>TEST1</li>
			<li>TEST2</li>
			<li>TEST3</li>
		</ul>'));
	}

	public function testIterationKey() {
		$xml = '<ul>
			<li>
				<span>key</span>
				<p>value</p>
			</li>
		</ul>';


		$data = [
			'One' => '1',
			'Two' => '2',
			'Three' => '3'
		];

		$tss = 'li {repeat: data(); }
		li span {content: key();}
		li p {content: iteration();}
		';

		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<ul>
			<li>
				<span>One</span>
				<p>1</p>
			</li>
			<li>
				<span>Two</span>
				<p>2</p>
			</li>
			<li>
				<span>Three</span>
				<p>3</p>
			</li>
		</ul>'));

	}

	public function testContentModeReplace() {
		$xml = '<div>
			<span>Foo</span>
		</div>';

		$tss = 'span {content: "replaced"; content-mode: replace; }';


		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output()->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<div>replaced</div>'));
	}

	public function testContentModeReplaceBlock() {
		$xml = '<div>
			<span>Foo</span>
		</div>';

		$tpl = __DIR__ . '/include.xml';
		$tpl = str_replace('\\', '/', $tpl);

		$tss = 'span {content: template(\'' . $tpl . '\'); content-mode: replace; }';


		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output()->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<div><p>foo</p></div>'));
	}

	public function testSelectAttributeFromData() {
		$data = ['test' => 'bar'];

		$xml = '<div>
			<span foo="bar">Foo</span>
		</div>';

		$tss = 'span[foo=data(test)] {content: "replaced"; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<div>
			<span foo="bar">replaced</span>
		</div>'));
	}


	public function testSelectAttributeFromData2() {
		$xml = '<select name="foo">
		<option value="test">Test</option>
		<option value="test2">Test 2</option>
		</select>';

		$data = ['x' => 'y', 'z'=> 'bar', 'foo' => 'test2'];

		$tss = '
select { bind: data(attr(name)); }
select option[value=data()]:attr(selected) { content: "selected"; }
		';

		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<select name="foo">
		<option value="test">Test</option>
		<option value="test2" selected="selected">Test 2</option>

		</select>'));
	}

	public function testOverrideAfter() {
			$xml = '<div>
			<span>Test</span>
		</div>';


		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'include.xml';
		$includeFile = str_replace('\\', '/', $includeFile);

		$tss = "div:after {content: 'foo' }
div:after {content: 'bar' }
		";
		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div><span>Test</span>bar</div>', $this->stripTabs($template->output()->body));

	}


	public function testOverrideBefore() {
			$xml = '<div>
			<span>Test</span>
		</div>';


		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'include.xml';

		$tss = "div:before {content: 'foo' }
		div:before {content: 'bar';}
		";
		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div>bar<span>Test</span></div>', $this->stripTabs($template->output()->body));

	}
	public function testAttrDisplayNone() {
		$xml = '<div>
			<span class="bar">test</span>
			<span id="foo">baz</span>
		</div>';


		$tss = 'span:attr(class) {display: none; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output()->body;


		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<div>
			<span>test</span>
			<span id="foo">baz</span>
		</div>'));
	}

	public function testBeforeTemplate() {
		$xml = '<div>
			<span>Test</span>
		</div>';


		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'include.xml';
		$includeFile = str_replace('\\', '/', $includeFile);

		$tss = "div:before {content: template('$includeFile'); }";
		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div><p>foo</p><span>Test</span></div>', $this->stripTabs($template->output()->body));

	}

	public function testAttrRead() {
		$xml = '
		<div class="one">

		</div>
		<div class="two">

		</div>
		<div class="three">
		</div>
		';

		$tss = 'div {content: attr(class); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output()->body), $this->stripTabs('
			<div class="one">one</div>
			<div class="two">two</div>
			<div class="three">three</div>
		'));
	}

	public function testFilterDataArray() {
		$data = [
			'anArray' => [
				'one' => 'foo',
				'two' => 'bar'
			]
		];

		$xml = '
		<div class="one">

		</div>
		<div class="two">

		</div>
		<div class="three">
		</div>
		';

		$tss = 'div:data[anArray[attr("class")]] {content: "set"; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('
			<div class="one">set</div>
			<div class="two">set</div>
			<div class="three"></div>
		'), $this->stripTabs($template->output($data)->body));

	}

	public function testFilterDataArrayWithEquals() {
		$data = [
			'anArray' => [
				'one' => 'foo',
				'two' => 'bar'
			]
		];

		$xml = '
		<div class="one">

		</div>
		<div class="two">

		</div>
		<div class="three">
		</div>
		';

		$tss = 'div:data[anArray[attr(class)]="foo"] {content: data(anArray[attr(class)]) }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($data)->body), $this->stripTabs('
			<div class="one">foo</div>
			<div class="two"></div>
			<div class="three">
			</div>
		'));

	}


	public function testMultiRule() {
		$xml = '
		<div class="one">

		</div>
		<div class="two">

		</div>
		<div class="three">
		</div>
		';

		$tss = '.one, .three {content: "foo"; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output()->body), $this->stripTabs('
			<div class="one">foo</div>
			<div class="two"></div>
			<div class="three">foo</div>
		'));


	}

	public function testNot() {
		$xml = '
		<div class="one">

		</div>
		<div class="two">

		</div>
		<div class="three">
		</div>
		';

		$tss = 'div:not(.two) {content: "foo"; }';


		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output()->body), $this->stripTabs('
			<div class="one">foo</div>
			<div class="two"></div>
			<div class="three">foo</div>
		'));

	}

	public function testNotWithMoreDepth() {
		$xml = '
		<div class="one">
			<p></p>
		</div>
		<div class="two">
			<p></p>
		</div>
		<div class="three">
			<p></p>
		</div>
		';

		$tss = 'p:not("div.two p") {content: "foo"; }';


		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output()->body), $this->stripTabs('
			<div class="one"><p>foo</p></div>
			<div class="two"><p></p></div>
			<div class="three"><p>foo</p></div>
		'));

	}

	public function testKeyChild() {
		$xml = '<ul>
            <li>
            <span></span>
            <a>Edit</a></li>
        </ul>';


        $data = ['array' => [
        'one' => 'foo',
        'two' => 'bar']
        ];


          $tss = 'ul li {repeat: data(array); }
ul li span {
  content: iteration();
}
       ul li a { content: key() }
        ';


        $template = new \Transphporm\Builder($xml, $tss);
        $this->assertEquals($this->stripTabs($template->output($data)->body), $this->stripTabs('<ul>
            <li>
            <span>foo</span>
            <a>one</a></li><li>
            <span>bar</span>
            <a>two</a></li>
        </ul>'));
	}

	public function testFunctionCall1() {

		$xml = '<div></div>';

		$obj = new stdClass;
		$obj->foo = function($bar) {
			return $bar;
		};

		$tss = 'div {content: data(foo("test")); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>test</div>'));


	}

	public function testFunctionCallWithDataArg1() {

		$xml = '<div></div>';

		$obj = new stdClass;
		$obj->foo = function($bar) {
			return $bar;
		};
		$obj->x = 'Y';

		$tss = 'div {content: data(foo(data(x)))); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>Y</div>'));


	}


	public function testFunctionCallWithDataArg2() {

		$xml = '<div></div>';

		$obj = new Foo();

		$tss = 'div {content: data(getBar(\'test\')); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>test</div>'));
	}


	public function testFunctionCallWithMultipleArgs() {

		$xml = '<div></div>';

		$obj = new Foo();

		$tss = 'div {content: data().add(2,3); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>5</div>'));
	}


	public function testFuncCalledGetData() {
		$xml = '<div></div>';

		$obj = new Foo();

		$tss = 'div {content: data(model.getData()); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>bar</div>'));

	}


	public function testFuncCalledGetDataBind() {
		$xml = '<div></div>';

		$obj = new Foo();

		$includeFile = __DIR__ . '/include.xml';
		$includeFile = str_replace('\\', '/', $includeFile);
		$tss = 'div {content: template("' . $includeFile  . '");  bind: data(model.getData()); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div><p>foo</p></div>'));

	}

	public function testAttrPseudoSelectorAlternateSyntax() {
		$xml = '<div></div>';

		$tss = 'div:[data()=true] {content: "test" }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output(true)->body), $this->stripTabs('<div>test</div>'));
	}

	public function testFunctionCallAsConditonal() {

		$xml = '<div></div>';

		$obj = new Foo();

		$tss = 'div:data[returnTrue()=true] {content: "test" }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>test</div>'));
	}

	public function testFunctionCallAsConditonal2() {

		$xml = '<div></div>';

		$obj = new Foo();

		$tss = 'div:data[returnFalse()=false] {content: "test" }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>test</div>'));
	}

	public function testFunctionCallAsConditonal3() {

	    $xml = '<div></div>';

	    $obj = new Foo();

	    $tss = 'div:data[returnOne()=1] {content: "test" }';

	    $template = new \Transphporm\Builder($xml, $tss);

	    $this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>test</div>'));
	}

	public function testFunctionCallAsConditonal4() {

	    $xml = '<div></div>';

	    $obj = new Foo();

	    $tss = 'div:[data(returnOne())=1] {content: "test" }';

	    $template = new \Transphporm\Builder($xml, $tss);

	    $this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div>test</div>'));
	}


	public function testRuleOneComment() {
		$xml = '<div></div>';

		$tss = 'div {
			//Foo
		 }';

		 $template = new \Transphporm\Builder($xml, $tss);

		 $this->assertEquals('<div></div>', $template->output()->body);
	}

	public function testCommentAtEnd() {
		$xml = '<div></div>';

		$tss = 'div {

		 }


		 //Foo';

		 $template = new \Transphporm\Builder($xml, $tss);

		 $this->assertEquals('<div></div>', $template->output()->body);
	}

	public function testPlusConcat() {
		$xml = '<div></div>';

		$tss = 'div {
			content: "foo" + "bar";
		 }';

		 $template = new \Transphporm\Builder($xml, $tss);

		 $this->assertEquals('<div>foobar</div>', $template->output()->body);
	}

	public function testPlusConcatWithLookup() {
		$xml = '<div></div>';

		$data = ['foo' => 'foo'];

		$tss = 'div {
			content: data(foo) + "bar";
		 }';

		 $template = new \Transphporm\Builder($xml, $tss);

		 $this->assertEquals('<div>foobar</div>', $template->output($data)->body);
	}


	public function testNestedData() {
		        $data = json_decode('{ "children": [
  {
    "target":"Parent",
    "title":"Parent",
    "children":
      [
        {
          "target":"Child 2",
          "title":"Child 2",
          "children":
            [
              {
                 "target":"two.1",
                 "title":"grandchild 1"
               },
               {
                 "target":"two.2",
                 "title":"grandchildchild 2"
               }
             ]
          },
          {
     "target":"child 1",
     "title":"child 1"
  }
      ]
  },
  {
     "target":"http://facebook.com",
     "title":"Facebook"
  }
]}', true);


	  $template = new \Transphporm\Builder(__DIR__ . '/nav.xml', __DIR__ . '/nav.tss');

	   $this->assertEquals($this->stripTabs('<ul>
				<li>
					<a href="Parent">Parent</a>
					<ul>
						<li>
							<a href="Child 2">Child 2</a>
							<ul>
								<li>
									<a href="two.1">grandchild 1</a>
								</li>
								<li>
									<a href="two.2">grandchildchild 2</a>
								</li>
							</ul>
						</li>
						<li>
							<a href="child 1">child 1</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="http://facebook.com">Facebook</a>
				</li>
	</ul>'), $this->stripTabs($template->output($data)->body));
	}

	public function testJsonBasic() {
		$data = '{
			"foo" : "bar"
		}';

		$xml = "
		<div></div>
		";

		$tss = "
		div { content: json(data()).foo; }
		";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<div>bar</div>'), $this->stripTabs($template->output($data)->body));
	}

	public function testNl2brBasic() {
        $xml = "
        <div></div>
        ";

        $tss = "
        div { content: 'Test Line 1 \n Test Line 2'; format: nl2br; }
        ";

        $transphporm = new Builder($xml, $tss);

        $this->assertEquals($this->stripTabs('<div>Test Line 1 <br /> Test Line 2</div>'), $this->stripTabs($transphporm->output()->body));
    }

    public function testNl2brBasicFromData() {
        $xml = "
        <div></div>
        ";

        $tss = "
        div { content: data(); format: nl2br; }
        ";

        $data = "Test Line 1 \n Test Line 2";

        $transphporm = new Builder($xml, $tss);

        $this->assertEquals($this->stripTabs('<div>Test Line 1 <br /> Test Line 2</div>'), $this->stripTabs($transphporm->output($data)->body));
    }

	public function testRoot() {
		$xml = "
		<div></div>
		";

		$tss = "
		div { bind: data(foo1); content: data() + root(foo2); }
		";

		$data = [
			"foo1" => "bar1",
			"foo2" => "bar2"
		];

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<div>bar1bar2</div>'), $this->stripTabs($template->output($data)->body));
	}

	public function testEmptyFunctionCallAsConditonal() {

		$xml = '<div></div>';

		$data = "test";

		$tss = 'div:[data()="test"] {content: "test" }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($data)->body), $this->stripTabs('<div>test</div>'));
	}

	public function testRepeatNotExistant() {
		$template = '
				<main><ul><li>TEST1</li></ul></main>
		';

		//When using repeat to repeat some data, set the content to the data for the iteration
		$css = 'ul li {repeat: data(list); content: iteration()}';


		$data = new stdclass;


		$template = new \Transphporm\Builder($template, $css);

		$this->assertEquals('<main><ul></ul></main>' ,$template->output($data)->body);
	}

	public function testAttributeExists() {
		$xml = "
		<div data-test='test'>Test1</div>
		<div>Test2</div>
		";

		$tss = "div[data-test] { display: none; }";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div>Test2</div>', $template->output()->body);
	}
}


class Foo {
	public $model;

	public function __construct() {
		$this->model = new Bar();
	}
	public function getBar($bar) {
		return $bar;
	}

	public function returnFalse() {
		return false;
	}

	public function returnTrue() {
		return true;
	}

	public function returnOne() {
		return 1;
	}

	public function add($a, $b) {
		return $a+$b;
	}
}

class Bar {
	public function getData() {
		return 'bar';
	}
}
