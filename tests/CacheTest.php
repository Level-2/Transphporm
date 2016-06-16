<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
use Transphporm\Builder;
class CacheTest extends PHPUnit_Framework_TestCase {

	private function stripTabs($str) {
		return trim(str_replace(["\t", "\n", "\r"], '', $str));
	}

	private function writeFile($name, $contents) {
		if (file_get_contents($name) != $contents) {
			file_put_contents($name, $contents)	;
		}
	}
	private function makeTss($tss) {
		$this->writeFile(__DIR__ . DIRECTORY_SEPARATOR . 'temp.tss', $tss);
		return __DIR__ . DIRECTORY_SEPARATOR . 'temp.tss';
	}

	private function makeTssTwo($tss) {
		$this->writeFile(__DIR__ . DIRECTORY_SEPARATOR . 'temptwo.tss', $tss);
		return __DIR__ . DIRECTORY_SEPARATOR . 'temptwo.tss';
	}

	private function makeXml($xml) {
		$this->writeFile(__DIR__ . DIRECTORY_SEPARATOR . 'temp.xml', $xml);
		return __DIR__ . DIRECTORY_SEPARATOR . 'temp.xml';
	}

	private function createFiles($frequency) {
		$xml = $this->makeXML('
				<div>test</div>
		');

		$css = $this->makeTss('div {content: data(getRand()); update-frequency: ' . $frequency . ';}');

		return [$xml, $css];
	}

	private function buildTemplate($frequency, $cache, $time = null) {
		list($xml, $css) = $this->createFiles($frequency);

		$template = new Builder($xml, $css);
		if ($time) $template->setTime($time);
		$template->setCache($cache);


		return  $template;
	}

	public function testCacheWrite() {
				$cache = new \ArrayObject;
		$random = new RandomGenerator;

				$o1 = $this->buildTemplate('never', $cache)->output($random)->body;

	}

	public function testCacheBasic() {

		$cache = new \ArrayObject;
		$random = new RandomGenerator;

		$o1 = $this->buildTemplate('never', $cache)->output($random)->body;
		$o2 = $this->buildTemplate('never', $cache)->output($random)->body;

		// If the cache is working, the content should not be updated the second time
		$this->assertEquals($o1, $o2);

		//And the getRand function on the random class should only be called once
		$this->assertEquals(1, $random->getCount());

	}


	public function testCacheMinutes() {

		$cache = new \ArrayObject;
		$random = new RandomGenerator;

		$o1 = $this->buildTemplate('10m', $cache)->output($random)->body;
		$o2 = $this->buildTemplate('10m', $cache)->output($random)->body;


		// If the cache is working, the content should not be updated the second time
		$this->assertEquals($o1, $o2);

		//And the getRand function on the random class should only be called once
		$this->assertEquals(1, $random->getCount());


		//advance the clock 11 minutes so the cache is expired
		$date = new \DateTime();
		$date->modify('+11 minutes');


		$o3 = $this->buildTemplate('10m', $cache, $date->format('U'))->output($random, false)->body;

		//The random nummber should now be refreshed and the contents changed
		$this->assertNotEquals($o3, $o1);
		$this->assertEquals(2, $random->getCount());

	}

	public function testCacheDisplay() {
		$xml = $this->makeXml('<div>
			<span>Test</span>
			</div>');

		$tss = $this->makeTss('
			span {display: block; update-frequency: 10m}
			span:data[show=false] { display:none; update-frequency: 10m }');

		$cache = new \ArrayObject();

		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);

		//Hide the span
		$o1 = $template->output(['show' => false])->body;

		$this->assertFalse(strpos($o1, '<span>'));


		//The span should still be hidden even if the data has changed due to the cache
		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);

		$o1 = $template->output(['show' => true])->body;

		$this->assertFalse(strpos($o1, '<span>'));


		//Expire the cache by advancing time 10 mintes
		$date = new \DateTime();
		$date->modify('+11 minutes');

		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);
		$template->setTime($date->format('U'));

		$o1 = $template->output(['show' => true])->body;

		//This time the span should be visible
		$this->assertTrue((bool) strpos($o1, '<span>'));
	}

	public function testCacheWithAttribute() {
		$xml = $this->makeXml('<div>
			<span data-hide="1">Test1</span>
			<span data-hide="2">Test2</span>
			</div>');

		$tss = $this->makeTss('
			span {display: block; }
			span[data-hide=data(show)] { display:none; }');

		$cache = new \ArrayObject();

		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);

		$expectedOutput = $this->stripTabs('<div><span data-hide="2">Test2</span></div>');
		$this->assertEquals($this->stripTabs($template->output(['hide' => 1])->body), $expectedOutput);

		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);

		$expectedOutput = $this->stripTabs('<div><span data-hide="1">Test1</span></div>');

		// Run output again
		$this->assertEquals($this->stripTabs($template->output(['hide' => 2])->body), $expectedOutput);
	}

	public function testCacheWithImport() {
		$xml = $this->makeXml('<div>
			<span>Test1</span>
			</div>');

		$tss = $this->makeTss('
			main { content: "Test1"; }
			@import "temp2.tss";');

		$cache = new \ArrayObject();

		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);

		$expectedOutput = $this->stripTabs('<div></div>');
		$this->assertEquals($this->stripTabs($template->output()->body), $expectedOutput);

		// Run output again
		$this->assertEquals($this->stripTabs($template->output()->body), $expectedOutput);

	}

	public function testCacheRepeat() {
		$data = ['One', 'Two', 'Three'];

		$tss = $this->makeTss('li { repeat: data(); content: iteration(); update-frequency: 10m}');

		$xml = $this->makeXml('<ul>
			<li>List item</li>
		</ul>');

		$cache = new \ArrayObject();
		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);

		$expectedOutput = $this->stripTabs('<ul>
			<li>One</li>
			<li>Two</li>
			<li>Three</li>
		</ul>');
		$this->assertEquals($this->stripTabs($template->output($data)->body), $expectedOutput);

		//Now update the data:
		$data = ['Four', 'Five', 'Six'];
		//And rebuild the template
		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);

		//The template should be cached and the new data not shown
		$this->assertEquals($this->stripTabs($template->output($data)->body), $expectedOutput);


		//Tick the clock so the cache expires
		$date = new \DateTime();
		$date->modify('+11 minutes');

		//And rebuild the template

		//Now update the data:
		$data = ['Four', 'Five', 'Six'];
		//And rebuild the template
		$template = new \Transphporm\Builder($xml, $tss);
		$template->setCache($cache);
		$template->setTime($date->format('U'));

		//The output should now reflect the new data

		$expectedOutput = $this->stripTabs('<ul>
			<li>Four</li>
			<li>Five</li>
			<li>Six</li>
		</ul>');
		$this->assertEquals($this->stripTabs($template->output($data)->body), $expectedOutput);

	}

}

class RandomGenerator {
	private $count = 0;

	public function getRand() {
		$this->count++;
		return rand();
	}

	public function getCount() {
		return $this->count;
	}
}
