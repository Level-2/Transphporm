<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/** Hooks into the template system, gets assigned as `ul li` or similar and `run()` is called with any elements that match */
class PropertyHook implements \Transphporm\Hook {
	private $rules;
	private $baaseDir;
	private $file;
	private $valueParser;
	private $pseudoMatcher;
	private $properties = [];
	private $functionSet;

	public function __construct(array $rules, &$baseDir, $file, PseudoMatcher $pseudoMatcher, \Transphporm\Parser\Value $valueParser, \Transphporm\FunctionSet $functionSet) {
		$this->rules = $rules;
		$this->baseDir = $baseDir;
		$this->file = $file;
		$this->valueParser = $valueParser;
		$this->pseudoMatcher = $pseudoMatcher;
		$this->functionSet = $functionSet;
	}

	public function run(\DomElement $element) {
		$this->functionSet->setElement($element);
		$this->origBaseDir = dirname(realpath($this->file)) . DIRECTORY_SEPARATOR;
		//Don't run if there's a pseudo element like nth-child() and this element doesn't match it
		try {
			if (!$this->pseudoMatcher->matches($element)) return;

			// TODO: Have all rule values parsed before running them so that things like `content-append` are not expecting tokens
			// problem with this is that anything in data changed by run properties is not shown
			// TODO: Allow `update-frequency` to be parsed before it is accessed in rule (might need to switch location of rule check)

			foreach ($this->rules as $name => $value) {

					$result = $this->callProperty($name, $element, $this->getArgs($value));
				if ($result === false) break;
			}
		}
		catch (\Transphporm\RunException $e) {
			throw new \Transphporm\Exception($e, $this->file, '');
		}
	}

	private function getArgs($value) {
		return $this->valueParser->parseTokens($value);
	}

	public function registerProperty($name, \Transphporm\Property $property) {
		$this->properties[$name] = $property;
	}

	private function callProperty($name, $element, $value) {
		if (isset($this->properties[$name])) {
			try {
				return $this->properties[$name]->run($value, $element, $this->rules, $this->pseudoMatcher, $this->properties);
			}
			catch (\Exception $e) {
				if ($e instanceof \Transphporm\RunException) throw $e;
				throw new \Transphporm\RunException(\Transphporm\Exception::PROPERTY, $name, $e);
			}
		}
	}
}
