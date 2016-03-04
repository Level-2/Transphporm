<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Module;
/** Module for loading a formatter with a locale */
class Format implements \Transphporm\Module {
	private $locale;

	public function __construct($locale = null) {
		$this->locale = $locale;
	}

	private function getLocale() {
		if (is_array($this->locale)) return $this->locale;
		else if (strlen($this->locale) > 0) return json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '../Formatter' . DIRECTORY_SEPARATOR . 'Locale' . DIRECTORY_SEPARATOR . $this->locale . '.json'), true);
		else return json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '../Formatter' . DIRECTORY_SEPARATOR . 'Locale' . DIRECTORY_SEPARATOR . 'enGB.json'), true);
	}

	public function load(\Transphporm\Config $config) {
		$locale = $this->getLocale();
		$config->registerFormatter(new \Transphporm\Formatter\Number($locale));
		$config->registerFormatter(new \Transphporm\Formatter\Date($locale));
		$config->registerFormatter(new \Transphporm\Formatter\StringFormatter());
	}
}
