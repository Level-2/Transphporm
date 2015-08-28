<?php
class PoCTest extends PHPUnit_Framework_TestCase {

	public function testContentSimple() {
		$template = '<template name="">
				<ul><li>TEST1</li></ul>
		</template>';

		$css = 'ul li {content: data(user);}';


		$data = new \stdclass;
		$data->user = 'tom';

		
		$template = new \CDS\Builder($template, $css, $data);
		
		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output()); 
	}


	public function testContentObject() {
		$template = '<template name="">
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
		$template = '<template name="">
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
		$template = '<template name="">
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
		$template = '<template name="">
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


		$template = '<template name="">
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
}



