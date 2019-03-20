<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Hook;
/** Hooks into the template system, gets assigned as `ul li` or similar and `run()` is called with any elements that match */
class PropertyHook implements \Transphporm\Hook {
	private $rules;
	private $configLine;
	private $file;
    private $filePath;
	private $line;
	private $valueParser;
	private $pseudoMatcher;
	private $properties = [];
	private $functionSet;

	public function __construct(array $rules, &$configLine, $file, $line, PseudoMatcher $pseudoMatcher,
            \Transphporm\Parser\Value $valueParser, \Transphporm\FunctionSet $functionSet, \Transphporm\FilePath $filePath) {
		$this->rules = $rules;
		$this->configLine = &$configLine;
		$this->file = $file;
        $this->filePath = $filePath;
		$this->line = $line;
		$this->valueParser = $valueParser;
		$this->pseudoMatcher = $pseudoMatcher;
		$this->functionSet = $functionSet;
	}

	public function run(\Transphporm\Document $document, \DomElement $element): \Transphporm\Document {
		//Set the baseDir so that all files for this rule are relative to the file it came from
        if ($this->file !== null) $this->filePath->setBaseDir(dirname(realpath($this->file)));
		$this->functionSet->setElement($element);
		$this->configLine = $this->line;
		try {
			//Don't run if there's a pseudo element like nth-child() and this element doesn't match it
			if (!$this->pseudoMatcher->matches($element)) return $document;
			$document = $this->callProperties($document, $element);
		}
		catch (\Transphporm\RunException $e) {
			throw new \Transphporm\Exception($e, $this->file, $this->line);
		}

		return $document;
	}

	// TODO: Have all rule values parsed before running them so that things like `content-append` are not expecting tokens
	// problem with this is that anything in data changed by run properties is not shown
	// TODO: Allow `update-frequency` to be parsed before it is accessed in rule (might need to switch location of rule check)
	private function callProperties(\Transphporm\Document $document, $element): \Transphporm\Document {
		foreach ($this->rules as $name => $value) {
			$document = $this->callProperty($document, $name, $element, $this->getArgs($value));
		}
		return $document;
	}
	private function getArgs($value) {
		return $this->valueParser->parseTokens($value);
	}

	public function registerProperty($name, \Transphporm\Property $property) {
		$this->properties[$name] = $property;
	}

	private function callProperty(\Transphporm\Document $document, $name, $element, $value): \Transphporm\Document {
		if (isset($this->properties[$name])) {
			try {
				return $this->properties[$name]->run($document, $value, $element, $this->rules, $this->pseudoMatcher, $this->properties);
			}
			catch (\Exception $e) {
				if ($e instanceof \Transphporm\RunException) throw $e;
				throw new \Transphporm\RunException(\Transphporm\Exception::PROPERTY, $name, $e);
			}
		}
		return $document;
	}
}
