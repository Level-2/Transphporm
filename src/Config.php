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
	private $filePath;
	private $formatter;
	private $line = 0;
	private $elementData;
	private $xPath;
	private $valueParser;

	public function __construct(Functionset $functionSet, Parser\Value $valueParser, Hook\ElementData $elementData, Hook\Formatter $formatter, Parser\CssToXpath $xPath, FilePath $filePath, &$headers) {
		$this->formatter = $formatter;
		$this->headers = &$headers;
		$this->filePath = $filePath;
		$this->functionSet = $functionSet;
		$this->elementData = $elementData;
		$this->xPath = $xPath;
		$this->valueParser = $valueParser;
	}

	public function getFormatter() {
		return $this->formatter;
	}

	public function &getHeaders() {
		return $this->headers;
	}

	public function getFilePath() {
		return $this->filePath;
	}

	public function &getLine() {
		return $this->line;
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

	public function getCssToXpath() {
		return $this->xPath;
	}

	public function getValueParser() {
		return $this->valueParser;
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
		$pseudoMatcher = new Hook\PseudoMatcher($pseudo, $this->valueParser);
		foreach ($this->pseudo as $pseudoFunction) $pseudoMatcher->registerFunction(clone $pseudoFunction);
		return $pseudoMatcher;
	}

}
