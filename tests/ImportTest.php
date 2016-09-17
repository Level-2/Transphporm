<?php
class ImportTest extends PHPUnit_Framework_TestCase {

	private function stripTabs($str) {
		return trim(str_replace(["\t", "\n", "\r"], '', $str));
	}

    public function testImport() {
		$template = '
			<div>Test</div>
		';

		$file = __DIR__ . DIRECTORY_SEPARATOR . 'import.tss';
		$file = str_replace('\\', '/', $file);
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
		$file = str_replace('\\', '/', $file);
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
		$file = str_replace('\\', '/', $file);
		$file2 = str_replace('\\', '/', $file2);
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
		$file = str_replace('\\', '/', $file);
		$file2 = str_replace('\\', '/', $file2);
		$tss = "
			span {content: 'test1';}
			@import '$file';
			@import '$file2';
			h1 {content: 'h1';}

		";

		$template = new \Transphporm\Builder($template, $tss);

		$this->assertEquals($this->stripTabs('<span>bar</span><div>foo</div><h1>h1</h1>'), $this->stripTabs($template->output()->body));

	}

	public function testImportInImportedFile() {
		$template = new \Transphporm\Builder("tests/test.xml", "tests/other/otherImport.tss");

		$this->assertEquals('<!DOCTYPE html><html><body>test</body></html>', $this->stripTabs($template->output()->body));
	}

	public function testBaseDirChangeWithImport() {
		$template = new \Transphporm\Builder("tests/test.xml", "tests/other/templateFromImport.tss");

		$this->assertEquals('<!DOCTYPE html><html><body><p>foo</p></body></html>', $this->stripTabs($template->output()->body));
	}
}
