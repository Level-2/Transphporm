<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
use Transphporm\Builder;
class TransphpormTest extends PHPUnit_Framework_TestCase {

	public function testUTF8() {
		$xml = "<p>this is half english and половина России &</p>";
		$template = new Builder($xml);

		$this->assertEquals('<p>this is half english and половина России &amp;</p>', $template->output()->body);
	}

	public function testLoadHTMLWithEntities() {
		$template = '<div>&nbsp; &lt;</div>';


		$template = new Builder($template);

		$this->assertEquals('<div>' . html_entity_decode('&nbsp;') . ' &lt;</div>', $template->output()->body);
	}

	public function testLoadHTMLUnclosed() {
		$template = '<div><img src="foo.jpg"></div>';



		$template = new Builder($template);

		$this->assertEquals('<div><img src="foo.jpg"></div>', $template->output()->body);
	}

	public function testNoCDATA() {
		$xml = '<div><style scoped="scoped">fieldset, legend { width:75%; }</style></div>';

		$template = new Builder($xml);

		$this->assertEquals('<div><style scoped="scoped">fieldset, legend { width:75%; }</style></div>', $template->output()->body);
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

		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$this->stripTabs($template->output($data)->body));
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

		$this->assertEquals('<ul><li>One</li><li>Two</li></ul>' , $this->stripTabs($template->output($data)->body));
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

		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' , $this->stripTabs($template->output($data)->body));
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

	public function testRepeatLoopMode() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		//When using repeat to repeat some data, set the content to the data for the iteration
		$css = 'ul li {repeat: 0, 2, loop; content: iteration()}';


		$template = new \Transphporm\Builder($template, $css);

		$this->assertEquals('<ul><li>0</li><li>1</li><li>2</li></ul>', $this->stripTabs($template->output()->body));
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

		$this->assertEquals('<h1>Test 1</h1><h1 class="test">REPLACED</h1><h1>Test 2</h1>', $this->striptabs($template->output()->body));
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

	public function testReadAttribute() {
		$template = '
			<div class="fromattribute">Test</div>
		';

		$tss = 'div {content: attr(class); }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="fromattribute">fromattribute</div>', $template->output()->body);
	}



	public function testReadDataFromAttribute() {
		$template = '
			<input name="foo" />
		';

		$tss = 'input:attr(value) {content: data(attr(name)); }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<input name="foo" value="bar">', $template->output(['foo' => 'bar'])->body);
	}

	public function testReadDataFromAttributeArray() {
		$template = '
			<input name="foo[bar]" />
		';

		$tss = 'input:attr(value) {content: data(attr(name)); }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<input name="foo[bar]" value="baz">', $template->output(['foo' => ['bar' => 'baz']])->body);
	}



	public function testWriteAttribute() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div:attr(class) {content: "classname"; }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="classname">Test</div>', $template->output()->body);
	}

	public function testWriteAttributeParamFromData() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div:attr(data()) {content: "classname"; }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="classname">Test</div>', $template->output("class")->body);
	}

    public function testReadConstant() {
        $template = '
            <div>Test</div>
        ';

        $tss = 'div {content: constant(MY_CONSTANT); }';

        define('MY_CONSTANT', 'constant value');
        $template = new \Transphporm\Builder($template, $tss);

        $this->assertEquals('<div>constant value</div>', $template->output()->body);
    }



    public function testReadConstantFromAttribute() {
        $template = '
            <input name="foo" data-value="MY_ATTR_CONSTANT" />
        ';

        $tss = 'input:attr(value) {content: constant(attr(data-value)); }';

        define('MY_ATTR_CONSTANT', 'attr constant value');
        $template = new \Transphporm\Builder($template, $tss);

        $this->assertEquals('<input name="foo" data-value="MY_ATTR_CONSTANT" value="attr constant value">', $template->output()->body);
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

		$tss = '/* div {content: "foo"; } */
		div {content: "bar"}
		';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>bar</div>', $template->output()->body);
	}

	public function testCommentBeforeRule() {
		$template = '
			<div>foo</div>
		';

		$tss = '
// Comment
div {content: "bar"; }
		';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>bar</div>', $template->output()->body);
	}

	public function testCommentInRule() {
		$template = '
			<div>foo</div>
		';

		$tss = '

div {// Comment
	content: "bar"; }
		';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>bar</div>', $template->output()->body);
	}

	public function testComments3() {
		$template = '
		<div>foo</div>
		';

		$tss = '
		/* CDN stuff */
		// Online
div { content: "bar"; }
		';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div>bar</div>', $template->output()->body);
	}

	public function testContentTemplate() {
		$template = '
			<div>Test</div>
		';

		$includeFile = '/tests/include.xml';

		$tss = "div {content: template('$includeFile'); }";
		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div><p>foo</p></div>', $this->stripTabs($template->output()->body));
	}

	public function testContentTemplateFromData() {
		$template = '
			<div>Test</div>
		';

		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'include.xml';
		$includeFile = str_replace(getcwd(), '/', $includeFile);

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
			<input type="text" name="one" value="VALUE-OF-ONE">
			<input type="text" name="two" value="VALUE-OF-TWO">
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
			<input type="text" name="one" value="VALUE-OF-ONE">
			<input type="text" name="two" value="VALUE-OF-TWO">
		</form>'), $this->stripTabs($template->output($data)->body));

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
		$template = '<!doctype html>
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

		$this->assertEquals($this->stripTabs('<form><input type="text" name="f1" value="v1"><input type="text" name="f2" value="v2"></form>'), $this->stripTabs($template->output($data)->body));

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

		$this->assertEquals($this->stripTabs('<form><input type="text" name="f1" value="v1"><input type="text" name="f2" value="v2"></form>'), $this->stripTabs($template->output($data)->body));

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
		$tpl = str_replace(getcwd(), '/', $tpl);

		$tss = 'span {content: template(\'' . $tpl . '\'); content-mode: replace; }';


		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output()->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<div><p>foo</p></div>'));
	}

	public function testPropertyOrderInsensitivity() {
		$xml = '<div>
			<span>Foo</span>
		</div>';

		$tss = 'span { content-mode: replace; content: "replaced";}';


		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output()->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<div>replaced</div>'));
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
		<option value="test2" selected>Test 2</option>

		</select>'));
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


		$includeFile = '/tests/include.xml';

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

    public function testMultiRule2() {
		$xml = '
		<div class="one">

		</div>
		<div class="two">

		</div>
		<div class="three">
		</div>
		';

		$tss = '.one,
.three {content: "foo"; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output()->body), $this->stripTabs('
			<div class="one">foo</div>
			<div class="two"></div>
			<div class="three">foo</div>
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
		$includeFile = str_replace(getcwd(), '/', $includeFile);
		$tss = 'div {content: template("' . $includeFile  . '");  bind: data(model.getData()); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div><p>foo</p></div>'));

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

	public function testCommentOutMultiLineComments() {
		$xml = '<div></div>';

		$tss = '
		//*
		div { content: "Test"; }
		//*/
		';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div>Test</div>', $template->output()->body);
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

	public function testJsonFile() {
		$data = '{
			"foo" : "bar"
		}';

		$file = __DIR__ . '/data.json';

		file_put_contents($file, $data);

		$xml = "
		<div></div>
		";

		$tss = '
		div { content: json("' . $file. '").foo; }
		';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<div>bar</div>'), $this->stripTabs($template->output($data)->body));

		unlink($file);
	}

    public function testFile() {
        $data = <<<JS
let j = 0;
if (j < 4) {
    console.log('j' + " is less than 4");
}
JS;

        $file = __DIR__ . 'data.file';
        file_put_contents($file, $data);

        $xml = "<script></script>";
        $tss = 'script { content: file("' . $file . '"); }';

        $template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs("<script>$data</script>"), $this->stripTabs($template->output($data)->body));

		unlink($file);
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

	public function testXmlComment() {
		$xml = "<div><!-- Comment --></div>";
		$tss = "div {}";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div><!-- Comment --></div>', $template->output()->body);
	}

	public function testXmlCommentInTemplate() {
		$xml = "<div></div>";
		$tss = "div { content: template('" . str_replace(getcwd(), '/', __DIR__) . "/xmlComment.xml'); }";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div><!-- Comment --><p></p></div>', $this->stripTabs($template->output()->body));
	}

	public function testXmlCommentInTemplate2() {
		$xml = "<div></div>";
		$tss = "div { content: template('" . str_replace(getcwd(), '/', __DIR__) . "/xmlComment2.xml'); }";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div><p></p><!-- Comment --></div>', $this->stripTabs($template->output()->body));
	}

	public function testObjFuncCallWithSameNameAsFunc() {
		$xml = "<div>content</div>";
		$tss = "div { bind: data(foo); content: data(root()) + root(test); }";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div>test5bar</div>', $template->output(['foo' => new Foo, 'test' => 'bar'])->body);
	}

	public function testNonexistantFieldResult1() {
		$xml = '<textarea name="test"></textarea>';
		$tss = "textarea { content: data(attr(name)); }";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<textarea name="test"></textarea>', $template->output([])->body);
	}

	public function testNonexistantFieldResult2() {
		$xml = "<textarea name='test'></textarea>";
		$tss = "textarea { content: data()[attr(name)]; }";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<textarea name="test"></textarea>', $template->output([])->body);
	}

	public function testNonexistantFieldResult3() {
		$xml = "<textarea name='test'></textarea>";
		$tss = "textarea { content: data(test); }";

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<textarea name="test"></textarea>', $template->output([])->body);
	}

	public function testFieldResult1() {
		$xml = "<textarea name='test'></textarea>";
		$tss = "textarea { content: data()[attr(name)]; }";
		$data = new stdClass;
		$data->test = 'content';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<textarea name="test">content</textarea>', $template->output($data)->body);
	}

	public function testAttrConcatWithNum() {
		$template = '
			<div class="test">Test</div>
		';

		$data = ['id' => 8];

		$tss = 'div:attr(class) {content: attr(class) + data(id); }';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals('<div class="test8">Test</div>', $template->output($data)->body);
	}

	public function testLoadScriptTagFromHTML() {
		$template = '<script>
			$().ready(function() {});
			</script>
			<br>';

		$template = new \Transphporm\Builder($template, '');

		$this->assertEquals($this->stripTabs('<script>
			$().ready(function() {});
			</script>
			<br>'), $this->stripTabs($template->output()->body));

	}

	public function testTemplateSelector() {
		$template = "<div></div>";

		$tss = "
		div { content: template('/tests/templateSelector.xml', '.test1'); }
		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div><div class="test1"><a>Test1</a><a>Test2</a></div></div>'),
			$this->stripTabs($template->output()->body));
	}

	public function testTemplateSelectorWithAsterisk() {
		$template = "<div></div>";

		$tss = "
		div { content: template('/tests/templateSelector.xml', '.test1 *'); }
		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div><a>Test1</a><a>Test2</a></div>'),
			$this->stripTabs($template->output()->body));
	}

	public function testDataFunctionReturnFalse() {

		$template = "<div></div>";

		$tss = "
		div:data[foo.returnFalse()=true] { content: 'test'; }
		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div></div>'),
			$this->stripTabs($template->output()->body));

	}


	public function testContentModeReplaceBlockInclude() {
		$xml = '<div>
			<include href="/tests/include.xml" />
		</div>';

		$tss = '
		@import "/tests/includeTest.tss";
		';

		$template = new \Transphporm\Builder($xml, $tss);

		$output = $template->output()->body;

		$this->assertEquals($this->stripTabs($output), $this->stripTabs('<div><p>foo</p></div>'));
	}

	public function testValueZero() {
		$xml = '<p>Exception code: <span class="code">unknown</span></p>';
		$tss = '.code { content: data(errorCode); }';
		$template = new Transphporm\Builder($xml, $tss);

		$data['errorCode'] = 0;
		$this->assertEquals($this->stripTabs($template->output($data)->body), $this->stripTabs('<p>Exception code: <span class="code">0</span></p>'));

		$data['errorCode'] = (string)0;
		$this->assertEquals($this->stripTabs($template->output($data)->body), $this->stripTabs('<p>Exception code: <span class="code">0</span></p>'));

	}

	public function testExtraLineBreak() {
		$xml1 = ' <div> </div> ';
		$tss1 = 'div {content: "2015-12-22"; format: date "jS M Y"}';
		$template1 = new \Transphporm\Builder($xml1, $tss1);
		$xml2 = ' <div> </div> ';
		$tss2 = 'div {content: "2015-12-22"; format: date "jS M Y"
}';
		$template2 = new \Transphporm\Builder($xml2, $tss2);
		$this->assertEquals($this->stripTabs($template1->output()->body), $this->stripTabs($template2->output()->body));
	}

	public function testDoctypeUsingLoadHTML() {
	$xml = '<!DOCTYPE html><html><head><title></title></head><body><img></body></html>';

	$tss = 'title {content: "My Site"}';

	$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<!DOCTYPE html><html>
	<head>
	<title>My Site</title>
	</head>
	<body><img></body>
	</html>'), $this->stripTabs($template->output()->body));
	}

	public function testRuleWithNewline() {
		$xml = '<p>My Link</p>';

		$tss = 'p {
			content: "http://foo/bar";
		}
		';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<p>http://foo/bar</p>'),
			$this->stripTabs($template->output()->body));
	}

	public function testCommentBlock() {

		$tss = 'div {content: "foo"}

   /*.comment {foo: bar} */

	span {content: "bar"}
	';

		$xml = '<div></div><span></span>';


		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<div>foo</div><span>bar</span>'),
			$this->stripTabs($template->output()->body));


	}


	// #165
	public function testNotWellFormedPartialBefore() {
		$xml = '<body><span>end</span></body>';

		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'not-well-formed.xml';
		$includeFile = str_replace(getcwd(), '/', $includeFile);

		$tss = 'body:before { content: template("' . $includeFile . '"); }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<body><header>foo</header>
			<nav>bar</nav><footer>baz</footer><span>end</span></body>'),
			$this->stripTabs($template->output()->body));

	}

	public function testConcatDataAndAttr() {
		$data = ['class' => ' AFTER'];
		$xml = '<h1 class="foo">Example Title</h1>';
		$tss = 'h1:attr(class) {content: attr(class) + data(class); }';
		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<h1 class="foo AFTER">Example Title</h1>'),
			$this->stripTabs($template->output($data)->body));
	}

	public function testConcatDataAndAttr2() {
		$data = ['class' => ' AFTER'];
		$xml = '<h1 class="foo">Example Title</h1>';
		$tss = 'h1:attr(class) {content: attr(class), data(class); }';
		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<h1 class="foo AFTER">Example Title</h1>'),
			$this->stripTabs($template->output($data)->body));
	}

	public function testAttrAfter() {
		$data = ['class' => ' AFTER'];
		$xml = '<h1 class="foo">Example Title</h1>';
		$tss = 'h1:attr(class):after {content: data(class); }';
		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<h1 class="foo AFTER">Example Title</h1>'),
			$this->stripTabs($template->output($data)->body));
	}

	public function testAttrBefore() {
		$data = ['class' => 'BEFORE '];
		$xml = '<h1 class="foo">Example Title</h1>';
		$tss = 'h1:attr(class):before {content: data(class); }';
		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<h1 class="BEFORE foo">Example Title</h1>'),
			$this->stripTabs($template->output($data)->body));
	}

	public function testImportHTMLIntoXML() {
		$xml = '<html>
			<head>

			</head>
			<body>
				This will be loaded as XML
			</body>
		</html>';

		$includeFile = __DIR__ . DIRECTORY_SEPARATOR . 'htmlcontent.xml';

		$tss = 'head {content: template("' . $includeFile . '"); }';


		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals(
			$this->stripTabs(html_entity_decode('<html>
			<head>
				<title>Head as&nbsp;HTML</title>
				<script>
					alert(1);
				</script>
			</head>
			<body>
				This will be loaded as XML
			</body>
		</html>'))
			,$this->stripTabs($template->output()->body));
	}


	public function testSetLocale() {
		$xml = '<div></div>';

		$tss = 'div {content: "2018-04-03"; format: date}';

		$template1 = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals('<div>03/04/2018</div>', $template1->output()->body);


		$template2 = new \Transphporm\Builder($xml, $tss);
		$template2->setLocale('enUS');

		$this->assertEquals('<div>04/03/2018</div>', $template2->output()->body);
	}

	public function testDebugOutput() {
		$xml = '<div></div>';

		$tss = 'div {content: data(foo); format: debug; }';

		$template = new \Transphporm\Builder($xml, $tss);


		$output = $template->output(['foo' => 'bar'])->body;

		$this->assertRegexp('/string\(3\) \"bar\"/', $output);
	}

	public function testGreater() {
		$xml = '<div><span class="one"></span><span class="two"></span></div>';


		//Should not set content on .one but shoud set content on .two
		$tss = '
			span.one[2>4] {content: "true"; }
			span.two[4>2] {content: "true"; }
		';

		$template = new Builder($xml, $tss);

		$this->assertEquals('<div><span class="one"></span><span class="two">true</span></div>', $this->stripTabs($template->output()->body));

	}

	public function testLess() {
		$xml = '<div><span class="one"></span><span class="two"></span></div>';


		//Should not set content on .two but shoud set content on .one
		$tss = '
			span.one[2<4] {content: "true"; }
			span.two[4<2] {content: "true"; }
		';

		$template = new Builder($xml, $tss);

		$this->assertEquals('<div><span class="one">true</span><span class="two"></span></div>', $this->stripTabs($template->output()->body));

	}

	public function testFillSelect() {
		$xml = '<select><option></option></select>';

		$tss = 'select option {repeat: data(options); }
		select option { content: iteration(); }
		select option:attr(value) { content: key(); }
		';

		$template = new Builder($xml, $tss);

		$data = ['options' => [
				'01' => 'January',
				'02' => 'Februrary',
				'03' => 'March'
		]];

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs('<select><option value="01">January</option>
<option value="02">Februrary</option>
<option value="03">March</option></select>'), $this->stripTabs($output));

	}



	public function testFillSelectWithNull() {
		$xml = '<select><option></option></select>';

		$tss = 'select option {repeat: data(options); }
		select option { content: iteration(); }
		select option:attr(value) { content: key(); }

		';

		$template = new Builder($xml, $tss);

		$data = ['options' => [
				'0' => null,
				'01' => 'January',
				'02' => 'Februrary',
				'03' => 'March'
		]];

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs('<select><option value="0"></option><option value="01">January</option>
<option value="02">Februrary</option>
<option value="03">March</option></select>'), $this->stripTabs($output));

	}


	public function testKeyBeforeIteration() {
		$xml = '<select><option></option></select>';

		$tss = 'select option {repeat: data(options); }
		select option:attr(value) { content: key(); }
		select option { content: iteration(); }

		';

		$template = new Builder($xml, $tss);

		$data = ['options' => [
				'01' => 'January',
				'02' => 'Februrary',
				'03' => 'March'
		]];

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs('<select><option value="01">January</option>
<option value="02">Februrary</option>
<option value="03">March</option></select>'), $this->stripTabs($output));

	}

	public function testRepeatAtEnd() {
		$xml = '<select><option></option></select>';

		$tss = '
		select option:attr(value) { content: key(); }
		select option { content: iteration(); }
		select option {repeat: data(options); }

		';

		$template = new Builder($xml, $tss);

		$data = ['options' => [
				'01' => 'January',
				'02' => 'Februrary',
				'03' => 'March'
		]];

		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs('<select><option value="01">January</option>
<option value="02">Februrary</option>
<option value="03">March</option></select>'), $this->stripTabs($output));

	}

	public function testContentModeAppendWithRepeat() {
		$xml = <<<XML
		<select class="status">
		<option value="0">Default option</option>
		</select>
XML;

		$tss = <<<TSS
		.status option {
		    repeat: data(status);
		    content-mode: append;
		    content: key(), " - ", iteration();
		}
		.status option:attr(value) {
		    content: key();
		}
TSS;

		$data['status'] = [
		    1 => "Good",
		    2 => "Bad",
		    3 => "Ugly",
		];

		$template = new Transphporm\Builder($xml, $tss);
		$output = $template->output($data)->body;

		$this->assertEquals($this->stripTabs('<select class="status">
<option value="0">Default option</option><option value="1">1 - Good</option><option value="2">2 - Bad</option><option value="3">3 - Ugly</option>
</select>'), $this->stripTabs($output));
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

	public function root() {
		return "test5";
	}
}

class Bar {
	public function getData() {
		return 'bar';
	}
}
