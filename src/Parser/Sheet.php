<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
/** Parses a .tss file into individual rules, each rule has a query e,g, `ul li` and a set of rules e.g. `display: none; bind: iteration(id);` */
class Sheet {
	private $tss;
	private $baseDir;
	private $valueParser;
	private $xPath;
	private $tokenizer; 

	public function __construct($tss, $baseDir, CssToXpath $xPath, Value $valueParser) {
		$this->tss = $this->stripComments($tss, '//', "\n");
		$this->tss = $this->stripComments($this->tss, '/*', '*/');
		$this->tokenizer = new Tokenizer($this->tss);
		$this->tss = $this->tokenizer->getTokens();
		$this->baseDir = $baseDir;
		$this->xPath = $xPath;
		$this->valueParser = $valueParser;
	}

	public function parse($indexStart = 0) {
		$rules = [];

		foreach (new TokenFilterIterator($this->tss, [Tokenizer::WHITESPACE]) as $token) {
			if ($processing = $this->processingInstructions($token, count($rules)+$indexStart)) {
				$this->tss->skip($processing['skip']+1);
				$rules = array_merge($rules, $processing['rules']);
				continue;
			}
			$selector = $this->tss->from($token['type'], true)->to(Tokenizer::OPEN_BRACE);
			$this->tss->skip(count($selector));
			if (!$this->tss->valid() || count($selector) === 0) break;

			$newRules = $this->cssToRules($selector, count($rules)+$indexStart, $this->getProperties($this->tss->current()['value']));
			$rules = $this->writeRule($rules, $newRules);
		}
		usort($rules, [$this, 'sortRules']);
		if (empty($rules) && count($this->tss) > 0) throw new \Exception("No TSS rules parsed");
		return $rules;
	}

	private function CssToRules($selector, $index, $properties) {
		$parts = $selector->trim()->splitOnToken(Tokenizer::ARG);
		$rules = [];
		foreach ($parts as $part) {
			$part = $part->trim();
			$rules[$this->tokenizer->serialize($part)] = new \Transphporm\Rule($this->xPath->getXpath($part), $this->xPath->getPseudo($part), $this->xPath->getDepth($part), $this->baseDir, $index++);
			$rules[$this->tokenizer->serialize($part)]->properties = $properties;
		}
		return $rules;
	}

	private function writeRule($rules, $newRules) {
		foreach ($newRules as $selector => $newRule) {

			if (isset($rules[$selector])) {
				$newRule->properties = array_merge($rules[$selector]->properties, $newRule->properties);
			}
			$rules[$selector] = $newRule;
		}

		return $rules;
	}

	private function processingInstructions($token, $indexStart) {
		if ($token['type'] !== Tokenizer::AT_SIGN) return false;
		$tokens = $this->tss->from(Tokenizer::AT_SIGN, false)->to(Tokenizer::SEMI_COLON, false)->getTokens();
		$funcName = array_shift($tokens)['value'];
		$args = $this->valueParser->parseTokens($tokens);
		$rules = $this->$funcName($args, $indexStart);

		return ['skip' => count($tokens)+1, 'rules' => $rules];
	}

	private function import($args, $indexStart) {
		$fileName = $args[0];
		$sheet = new Sheet(file_get_contents($this->baseDir . $fileName), dirname(realpath($this->baseDir . $fileName)) . DIRECTORY_SEPARATOR, $this->xPath, $this->valueParser);
		return $sheet->parse($indexStart);
	}

	private function sortRules($a, $b) {
		//If they have the same depth, compare on index
		if ($a->depth === $b->depth) return $a->index < $b->index ? -1 : 1;

		return ($a->depth < $b->depth) ? -1 : 1;
	}

	private function stripComments($str, $open, $close) {
		$pos = 0;
		while (($pos = strpos($str, $open, $pos)) !== false) {
			$end = strpos($str, $close, $pos);
			if ($end === false) break;
			$str = substr_replace($str, '', $pos, $end-$pos+strlen($close));
		}

		return $str;
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
