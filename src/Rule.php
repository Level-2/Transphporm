<?php
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
	const H = self::M*60;
	const D = self::H*24;


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

	public function shouldRun($time) {
		if (isset($this->properties['update-frequency']) && $this->lastRun !== 0) {
			$frequency = $this->properties['update-frequency'];
			$static = ['always' => true, 'never' => false];
			if (isset($static[$frequency])) return $static[$frequency];

			$num = (int) $frequency;
			$unit = strtoupper(trim(str_replace($num, '', $frequency)));
			
			$offset = $num * constant(self::class . '::' . $unit);

			if ($time > $this->lastRun + $offset) return true;
			else return false;

		}
		else return true;
	}
}