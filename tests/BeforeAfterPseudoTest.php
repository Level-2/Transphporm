<?php
use Transphporm\Builder;
class BeforeAfterPseudoTest extends PHPUnit_Framework_TestCase {

	private function stripTabs($str) {
		return trim(str_replace(["\t", "\n", "\r"], '', $str));
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

    public function testConditionalAfterFalse() {
        $template =  '
		<div>Test</div>
		';

        $tss = 'div:[data()="test"]:after {content: "AFTER";}';

        $template = new \Transphporm\Builder($template, $tss);

        $this->assertEquals($this->stripTabs('<div>Test</div>'), $this->stripTabs($template->output("test1")->body));
    }

    public function testConditionalAfterFalseCondition2nd() {
        $template =  '
		<div>Test</div>
		';

        $tss = 'div:after:[data()="test"] {content: "AFTER";}';

        $template = new \Transphporm\Builder($template, $tss);

        $this->assertEquals($this->stripTabs('<div>Test</div>'), $this->stripTabs($template->output("test1")->body));
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
}
