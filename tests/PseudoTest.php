<?php
class PseudoTest extends PHPUnit_Framework_TestCase {

	private function stripTabs($str) {
		return trim(str_replace(["\t", "\n", "\r"], '', $str));
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

	public function testFunctionCallAsConditonal5() {

	    $xml = '<div></div>';

	    $obj = new Foo();

	    $tss = 'div:[data().notExistantFunction()=1] {content: "test" }';

	    $template = new \Transphporm\Builder($xml, $tss);

	    $this->assertEquals($this->stripTabs($template->output($obj)->body), $this->stripTabs('<div></div>'));
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

	public function testEmptyFunctionCallAsConditonal() {

		$xml = '<div></div>';

		$data = "test";

		$tss = 'div:[data()="test"] {content: "test" }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output($data)->body), $this->stripTabs('<div>test</div>'));
	}

	public function testAttrPseudoSelectorAlternateSyntax() {
		$xml = '<div></div>';

		$tss = 'div:[data()=true] {content: "test" }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output(true)->body), $this->stripTabs('<div>test</div>'));
	}

    public function testAttrPseudoConditionalWithObj() {
        $xml = '<div><span></span></div>';

        $tss = 'span:data[obj1=data(obj2)] { display: none; }';

        $obj1 = (object) ['test' => 1];
        $obj2 = (object) ['test' => 2];

        $template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs($template->output(['obj1' => $obj1, 'obj2'=> $obj2])->body), $this->stripTabs('<div><span></span></div>'));
    }

    /*
     * Nth-child Pseudo tests
     */

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

	public function testNthChild4() {
		$xml = '
		<h2 class="name">Test</h2>
		<h3 class="name">Test</h3>
		<h4 class="name">Test</h4>
		';

		$tss = '.name:nth-child(2) { display: none; }

		.name:nth-child(3) { display: none; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($template->output()->body, $this->stripTabs('<h2 class="name">Test</h2>'));
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

    /*
     * Not Pseudo Tests
     */

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

    public function testNotWithPseudoInside() {
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

		$tss = 'div:not("div:nth-child(2)") {content: "foo"; }';


		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('
			<div class="one">foo</div>
			<div class="two"><p></p></div>
			<div class="three">foo</div>
		'), $this->stripTabs($template->output()->body));
    }

    public function testInOperator() {
    	// Issue #185 https://github.com/Level-2/Transphporm/issues/185
    	$xml = '<select name="teachers[]" id="teachers" multiple>
					<option value="1">T1</option>
				</select>';

		$tss = '
		select#teachers option {repeat: data(teachers); content: iteration(name)}
		select#teachers option:attr(value) {content: iteration(id)}
		select#teachers option:iteration[name in data(subject.teachers)]:attr(selected) { content: "selected"}
		';

		$data = json_decode('{
			"subject": {
				"id": 11,
				"name": "Subject 5",
				"year": 2,
				"teachers": [ "Teacher 3", "Teacher 11"]
			},
			"teachers": [
				{
					"id": 1,
					"name": "Teacher 1"
				},
				{
					"id": 3,
					"name": "Teacher 3"

				},
				{
					"id": 11,
					"name": "Teacher 11"
				}
			]
		}');


		$template = new \Transphporm\Builder($xml, $tss);
		$this->assertEquals($this->stripTabs('
			<select name="teachers[]" id="teachers" multiple>
					<option value="1">Teacher 1</option>
					<option value="3" selected>Teacher 3</option>
					<option value="11" selected>Teacher 11</option>
				</select>
		'), $this->stripTabs($template->output($data)->body));
    }
}
