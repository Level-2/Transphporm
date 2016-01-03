<?php
namespace Transphporm;
class FeatureSet {
	private $properties = [];
	private $pseudo = [];
	private $data; 
	private $headers;
	private $formatter; 
	
	public function __construct(Hook\DataFunction $data, Hook\Formatter $formatter, &$headers) {
		$this->data = $data;
		$this->formatter = $formatter;
		$this->headers = &$headers;
	}

	public function getData() {
		return $this->data;
	}

	public function getFormatter() {
		return $this->formatter;
	}

	public function &getHeaders() {
		return $this->headers;
	}

	public function registerFormatter($formatter) {
		$this->formatter->register($formatter);
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