<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Parser;
/** Parses a .tss file into individual rules, each rule has a query e,g, `ul li` and a set of rules e.g. `display: none; bind: iteration(id);` */
class Sheet {
	private $tss;
	private $xPath;
	private $valueParser;
	private $sheetLoader;
	private $file;
	private $rules;

	public function __construct($tss, CssToXpath $xPath, Value $valueParser, \Transphporm\FilePath $filePath, \Transphporm\SheetLoader\SheetLoader $sheetLoader, $file = null) {
		$this->xPath = $xPath;
		$this->valueParser = $valueParser;
		$this->filePath = $filePath;
		$this->sheetLoader = $sheetLoader;
		$this->file = $file;
		$this->tss = (new Tokenizer($tss))->getTokens();
	}

	public function parse($indexStart = 0) {
		if (!empty($this->rules)) return $this->rules['rules'];
		$rules = $this->parseTokens($indexStart);
		$this->checkError($rules);
		return $rules;
	}

	private function parseTokens($indexStart) {
		$this->rules = [];
		foreach (new TokenFilterIterator($this->tss, [Tokenizer::WHITESPACE]) as $token) {
			if ($processing = $this->processingInstructions($token, count($this->rules)+$indexStart)) {
				$this->rules = array_merge($this->rules, $processing);
			}
			else if (!in_array($token['type'], [Tokenizer::NEW_LINE, Tokenizer::AT_SIGN])) $this->addRules($token, $indexStart++);
		}

		return $this->rules;
	}

	private function addRules($token, $indexStart) {
		$selector = $this->tss->from($token['type'], true)->to(Tokenizer::OPEN_BRACE);

		$this->tss->skip(count($selector));
		if (count($selector) === 0) return;
		$newRules = $this->cssToRules($selector, count($this->rules)+$indexStart, $this->getProperties($this->tss->current()['value']), $token['line']);
		$this->rules = $this->writeRule($this->rules, $newRules);
	}

	private function checkError($rules) {
		if (empty($rules) && count($this->tss) > 0) throw new \Exception('No TSS rules parsed');
	}

	private function CssToRules($selector, $index, $properties, $line) {
		$parts = $selector->trim()->splitOnToken(Tokenizer::ARG);
		$rules = [];
		foreach ($parts as $part) {
			$serialized = serialize($part->removeLine());
			$rules[$serialized] = new \Transphporm\Rule($this->xPath->getXpath($part), $this->xPath->getPseudo($part), $this->xPath->getDepth($part), $index, $this->file, $line);
			$rules[$serialized]->properties = $properties;
		}
		return $rules;
	}

	private function writeRule($rules, $newRules) {
		foreach ($newRules as $selector => $newRule) {
			if (isset($rules[$selector])) {
				$newRule->properties = array_merge($rules[$selector]->properties, $newRule->properties);
				$newRule->index = $rules[$selector]->index;
			}
			$rules[$selector] = $newRule;
		}

		return $rules;
	}

	private function processingInstructions($token, $indexStart) {
		if ($token['type'] !== Tokenizer::AT_SIGN) return false;
		$tokens = $this->tss->from(Tokenizer::AT_SIGN, false)->to(Tokenizer::SEMI_COLON, false);
		$funcName = $tokens->from(Tokenizer::NAME, true)->read();
		$funcToks = $tokens->from(Tokenizer::NAME);
		$args = $this->valueParser->parseTokens($funcToks);
		$rules = $this->$funcName($args, $indexStart, $funcToks);
		$this->tss->skip(count($tokens)+2);

		return $rules;
	}

	private function import($args, $indexStart, $tokens) {
		$fileName = $this->filePath->getFilePath($args[0]);
		$this->sheetLoader->addImport($fileName);

		$tssFile = new \Transphporm\SheetLoader\TSSString(file_get_contents($fileName), $this->filePath);
		return $tssFile->getRules($this->xPath, $this->valueParser, $this->sheetLoader, $indexStart);
	}


	private function getProperties($tokens) {
        $rules = $tokens->splitOnToken(Tokenizer::SEMI_COLON);

        $return = [];
        foreach ($rules as $rule) {
            $name = $rule->from(Tokenizer::NAME, true)->to(Tokenizer::COLON)->read();
            $return[$name] = $rule->from(Tokenizer::COLON)->trim();
        }

        return $return;
    }
}
