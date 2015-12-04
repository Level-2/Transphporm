<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
use Transphporm\Builder;
class CacheTest extends PHPUnit_Framework_TestCase {

	private function writeFile($name, $contents) {
		if (file_get_contents($name) != $contents) {
			file_put_contents($name, $contents)	;
		}
	}
	private function makeTss($tss) {
		$this->writeFile(__DIR__ . DIRECTORY_SEPARATOR . 'temp.tss', $tss);
		return __DIR__ . DIRECTORY_SEPARATOR . 'temp.tss';
	}

	private function makeXml($xml) {
		$this->writeFile(__DIR__ . DIRECTORY_SEPARATOR . 'temp.xml', $xml);
		return __DIR__ . DIRECTORY_SEPARATOR . 'temp.xml';	
	}

	private function createFiles($frequency) {
		$xml = $this->makeXML('
				<div>test</div>
		');

		$css = $this->makeTss('div {content: data(getRand); update-frequency: ' . $frequency . ';}');

		return [$xml, $css];
	}

	private function buildTemplate($frequency, $cache) {
		list($xml, $css) = $this->createFiles($frequency);

		$template = new Builder($xml, $css);
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


		$o3 = $this->buildTemplate('10m', $cache)->output($random, false, $date->format('U'))->body;

		//The random nummber should now be refreshed and the contents changed
		$this->assertNotEquals($o3, $o1);
		$this->assertEquals(2, $random->getCount());

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