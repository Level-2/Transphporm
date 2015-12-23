<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
class Rule {
	private $query;
	private $pseudo;
	private $depth;
	private $index;
	private $properties = [];
	private $lastRun = 0;

	const S = 1;
	const M = 60;
	const H = 3600;
	const D = 86400;


	public function __construct($query, $pseudo, $depth, $index, array $properties = []) {
		$this->query = $query;
		$this->pseudo = $pseudo;
		$this->depth = $depth;
		$this->index = $index;
		$this->properties = $properties;
	}

	public function __get($name) {
		return $this->$name;
	}

	public function __set($name, $value) {
		$this->$name = $value;
	}

	public function touch() {
		$this->lastRun = time();
	}

	private function timeFrequency($frequency, $time = null) {
		if ($time === null) $time = time();
		$num = (int) $frequency;
		$unit = strtoupper(trim(str_replace($num, '', $frequency)));
			
		$offset = $num * constant(self::class . '::' . $unit);

		if ($time > $this->lastRun + $offset) return true;
		else return false;
	}

	public function shouldRun($time = null) {
		if (isset($this->properties['update-frequency']) && $this->lastRun !== 0) {
			$frequency = $this->properties['update-frequency'];
			$static = ['always' => true, 'never' => false];
			if (isset($static[$frequency])) return $static[$frequency];
			else return $this->timeFrequency($frequency, $time);
		}
		else return true;
	}
}