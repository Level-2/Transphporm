<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
class Config {
	private $properties = [];
	private $pseudo = [];
	private $functionSet; 
	private $headers;
	private $formatter; 
	private $baseDir;
	private $elementData;
	
	public function __construct(Functionset $functionSet, Hook\ElementData $elementData, Hook\Formatter $formatter, &$headers, &$baseDir) {
		$this->formatter = $formatter;
		$this->headers = &$headers;
		$this->baseDir = &$baseDir;
		$this->functionSet = $functionSet;
		$this->elementData = $elementData;
	}

	public function getFormatter() {
		return $this->formatter;
	}

	public function &getHeaders() {
		return $this->headers;
	}

	public function &getBaseDir() {
		return $this->baseDir;
	}

	public function registerFormatter($formatter) {
		$this->formatter->register($formatter);
	}

	public function getFunctionSet() {
		return $this->functionSet;
	}

	public function getElementData() {
		return $this->elementData;
	}

	public function registerProperty($name, Property $property) {
		$this->properties[$name] = $property;
	}

	public function registerPseudo(Pseudo $pseudo) {
		$this->pseudo[] = $pseudo;
	}

	public function loadProperties(Hook\PropertyHook $hook) {
		foreach ($this->properties as $name => $property) $hook->registerProperty($name, $property);
	}

	public function createPseudoMatcher($pseudo) {
		$pseudoMatcher = new Hook\PseudoMatcher($pseudo);
		foreach ($this->pseudo as $pseudoFunction) $pseudoMatcher->registerFunction($pseudoFunction);
		return $pseudoMatcher;
	}
}