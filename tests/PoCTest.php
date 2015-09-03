<?php
class PoCTest extends PHPUnit_Framework_TestCase {

	public function testContentSimple() {
		$template = '<template>
				<ul><li>TEST1</li></ul>
		</template>';

		$css = 'ul li {content: data(user);}';


		$data = new \stdclass;
		$data->user = 'tom';

		
		$template = new \CDS\Builder($template, $css, $data);
		
		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output()); 
	}


	public function testContentObject() {
		$template = '<template>
				<ul><li>TEST1</li></ul>
		</template>';

		$css = 'ul li {content: data(user.name);}';


		$data = new stdclass;
		$data->user = new stdclass;
		$data->user->name = 'tom';

		
		$template = new \CDS\Builder($template, $css, $data);
		
		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output()); 
	}


	public function testRepeatBasic() {
		$template = '<template>
				<ul><li>TEST1</li></ul>
		</template>';

		//When using repeat to repeat some data, set the content to the data for the iteration
		$css = 'ul li {repeat: data(list); content: iteration()}';


		$data = new stdclass;
		$data->list = ['One', 'Two', 'Three'];

		
		$template = new \CDS\Builder($template, $css, $data);
		
		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output()); 
	}


	public function testRepeatObject() {
		$template = '<template>
				<ul><li>TEST1</li></ul>
		</template>';


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
		
		$template = new \CDS\Builder($template, $css, $data);
		
		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output()); 
	}

	private function stripTabs($str) {
		return trim(str_replace("\t", '', $str));
	}

	public function testRepeatObjectChildNode() {
		$template = '<template>
				<ul>
					<li>
						<span>TEST1</span>
					</li>
				</ul>
		</template>';

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
		
		$template = new \CDS\Builder($template, $css, $data);
		
		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<span>One</span>
			</li><li>
				<span>Two</span>
			</li><li>
				<span>Three</span>
			</li>
		</ul>') ,$this->stripTabs($template->output())); 
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


		$template = '<template>
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		</template>';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }';


		$template = new \CDS\Builder($template, $css, $data);


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
		</ul>'), $this->stripTabs($template->output()));

	}


	public function testQuotedContent() {
		$template = '<template><h1>Heading</h1></template>';

		$cds = 'h1 {content: "TEST";}';

		$template = new \CDS\Builder($template, $cds, []);

		$this->assertEquals('<h1>TEST</h1>', $template->output());
	}


	public function testQuotedContentWithEscape() {
		$template = '<template><h1>Heading</h1></template>';

		$cds = 'h1 {content: "TEST\"TEST";}';

		$template = new \CDS\Builder($template, $cds, []);

		$this->assertEquals('<h1>TEST"TEST</h1>', $template->output());
	}

	public function testMultipleContentValues() {
		$template = '<template><h1>Heading</h1></template>';

		$cds = 'h1 {content: "A", "B";}';

		$template = new \CDS\Builder($template, $cds, []);

		$this->assertEquals('<h1>AB</h1>', $template->output());
	}


	public function testMatchClassAndTag() {
		$template = '<template><h1>Test 1</h1><h1 class="test">Heading</h1><h1>Test 2</h1></template>';

		$cds = 'h1.test {content: "REPLACED";}';

		$template = new \CDS\Builder($template, $cds, []);

		$this->assertEquals('<h1>Test 1</h1><h1 class="test">REPLACED</h1><h1>Test 2</h1>', $template->output());
	}

	public function testMatchClassChild() {
		$template = '<template>
		<div>
			<span class="foo">test</span>
			<span class="bar">test</span>
		</div>
		</template>';

		$cds = 'div .foo {content: "REPLACED";}';

		$template = new \CDS\Builder($template, $cds, []);
		$this->assertEquals($this->stripTabs('<div>
			<span class="foo">REPLACED</span>
			<span class="bar">test</span>
		</div>'), $this->stripTabs($template->output()));
	}

	public function testChildNodeMatcher() {
		$template = '<template>
		<div>
			<span class="foo">test</span>
			<span class="bar">test</span>
		</div>
		</template>';

		$cds = 'div > .foo {content: "REPLACED";}';

		$template = new \CDS\Builder($template, $cds, []);
		$this->assertEquals($this->stripTabs('<div>
			<span class="foo">REPLACED</span>
			<span class="bar">test</span>
		</div>'), $this->stripTabs($template->output()));
	}


	public function testAttributeSelector() {
		$template = '<template>
		<div>
			<textarea name="foo">foo</textarea>
			<textarea>bar</textarea>
		</div>
		</template>';

		$cds = '[name="foo"] {content: "REPLACED";}';

		$template = new \CDS\Builder($template, $cds, []);
		$this->assertEquals($this->stripTabs('<div>
			<textarea name="foo">REPLACED</textarea>
			<textarea>bar</textarea>
		</div>'), $this->stripTabs($template->output()));
	}


	//check that it's not due to the order of the HTML
	public function testAttributeSelectorB() {
		$template = '<template>
		<div>
			<textarea>bar</textarea>
			<textarea name="foo">foo</textarea>			
		</div>
		</template>';

		$cds = '[name="foo"] {content: "REPLACED";}';

		$template = new \CDS\Builder($template, $cds, []);
		$this->assertEquals($this->stripTabs('<div>
			<textarea>bar</textarea>
			<textarea name="foo">REPLACED</textarea>
		</div>'), $this->stripTabs($template->output()));
	}


	public function testAttributeSelectorC() {
		$template = '<template>
		<div>
			<a name="foo">a link</a>
			<textarea name="foo">foo</textarea>			
		</div>
		</template>';

		$cds = 'textarea[name="foo"] {content: "REPLACED";}';

		$template = new \CDS\Builder($template, $cds, []);
		$this->assertEquals($this->stripTabs('		<div>
			<a name="foo">a link</a>
			<textarea name="foo">REPLACED</textarea>			
		</div>'), $this->stripTabs($template->output()));
	}


	public function testDisplayNone() {
		$template = '<template>
		<div>
			<a name="foo">a link</a>
			<textarea name="foo">foo</textarea>			
		</div>
		</template>';

		$cds = 'textarea[name="foo"] {display: none;}';

		$template = new \CDS\Builder($template, $cds, []);
		$this->assertEquals($this->stripTabs('		<div>
			<a name="foo">a link</a>

		</div>'), $this->stripTabs($template->output()));
	}

	public function testBefore() {
		$template =  '<template>
		<div>Test</div>
		</template>';

		$cds = 'div:before {content: "BEFORE";}';

		$template = new \CDS\Builder($template, $cds, []);

		$this->assertEquals($this->stripTabs('<div>BEFORETest</div>'), $this->stripTabs($template->output()));
	}

	public function testAfter() {
		$template =  '<template>
		<div>Test</div>
		</template>';

		$cds = 'div:after {content: "AFTER";}';

		$template = new \CDS\Builder($template, $cds, []);

		$this->assertEquals($this->stripTabs('<div>TestAFTER</div>'), $this->stripTabs($template->output()));
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


		$template = '<template>
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		</template>';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }
		ul li span:iteration[id="2"] {display: none;}
		';


		$template = new \CDS\Builder($template, $css, $data);


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
		</ul>'), $this->stripTabs($template->output()));

	}
}



