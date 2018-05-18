<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm;
class Rule {
	private $query;
	private $pseudo;
	private $depth;
	private $index;
    private $file;
	private $line;
	private $properties = [];
	private $lastRun = 0;

	const S = 1;
	const M = 60;
	const H = 3600;
	const D = 86400;


	public function __construct($query, $pseudo, $depth, $index, $file, $line, array $properties = []) {
		$this->query = $query;
		$this->pseudo = $pseudo;
		$this->depth = $depth;
		$this->index = $index;
        $this->file = $file;
		$this->line = $line;
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

		$offset = $this->getUpdateFrequency($frequency);

		if ($time > $this->lastRun + $offset) return true;
		else return false;
	}

	public function shouldRun($time = null) {
		if (isset($this->properties['update-frequency']) && $this->lastRun !== 0) {
			$frequency = $this->properties['update-frequency']->read();
			$static = ['always' => true, 'never' => false];
			if (isset($static[$frequency])) return $static[$frequency];
			else return $this->timeFrequency($frequency, $time);
		}
		else return true;
	}

	public function getUpdateFrequency($frequency = null) {
		if ($frequency === null) {
			$frequency = isset($this->properties['update-frequency']) ? $this->properties['update-frequency']->read() : false;
		}

		if (empty($frequency)) return 0;
		else return $this->calcUpdateFrequency($frequency);
	}

	private function calcUpdateFrequency($frequency) {
		$num = (int) $frequency;
		$unit = strtoupper(trim(str_replace($num, '', $frequency)));
		if ($frequency == 'always') return 0;
		else if ($frequency == 'never') return self::D*3650; //Not quite never, in 10 years will cause issues on 32 bit PHP builds re 2038 problem

		return $num * constant(self::class . '::' . $unit);
	}
}
