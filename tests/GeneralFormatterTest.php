<?php
use Transphporm\Builder;
class GeneralFormatterTest extends PHPUnit_Framework_TestCase {

	private function stripTabs($str) {
		return trim(str_replace(["\t", "\n", "\r"], '', $str));
	}

    /*
     * Number Formatter Tests
     */

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

    /*
     * String Formatter Tests
     */

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

	public function testHTMLFormat() {
		$template = '
			<div>test</div>
		';

		$tss = 'div {content: "<span>foobar</span>"; format: html}';

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<div><span>foobar</span></div>'), $this->stripTabs($template->output()->body));
	}

	public function testHTMLFormatWithLooseFormat() {
		$xml = '<div></div>';
		$tss = 'div { content: "<span></span><p></p>"; format: html; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<div><span></span><p></p></div>'), $this->stripTabs($template->output()->body));
	}

    public function testHTMLFormatWithNoWrapping() {
		$xml = '<div></div>';
		$tss = 'div { content: "test<span>inside</span>text"; format: html; }';

		$template = new \Transphporm\Builder($xml, $tss);

		$this->assertEquals($this->stripTabs('<div>test<span>inside</span>text</div>'), $template->output()->body);
	}

    /*
     * Date Formatter Tests
     */

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

    public function testFormatDateCustomEscape() {
        $xml = '<div class="created">--</div>';

        $tss = '.created {
        content: data(create_time);
        format: date "jS M Y \\a\\t h:i a";
        }';

        $data['create_time'] = '2017-05-30 10:52:00';
        $template = new Transphporm\Builder($xml, $tss);
        $this->assertEquals($this->stripTabs('<div class="created">30th May 2017 at 10:52 am</div>'), $this->stripTabs($template->output($data)->body));
    }

    /*
     * Nl2br Formatter Tests
     */

    public function testNl2brBasic() {
        $xml = "
        <div></div>
        ";

        $tss = "
        div { content: 'Test Line 1 \n Test Line 2'; format: nl2br; }
        ";

        $transphporm = new Builder($xml, $tss);

        $this->assertEquals($this->stripTabs('<div>Test Line 1 <br> Test Line 2</div>'), $this->stripTabs($transphporm->output()->body));
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

        $this->assertEquals($this->stripTabs('<div>Test Line 1 <br> Test Line 2</div>'), $this->stripTabs($transphporm->output($data)->body));
    }
}
