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

	public function __construct($tss, $baseDir, CssToXpath $xPath, Value $valueParser) {
		$this->tss = $this->stripComments($tss, '//', "\n");
		$this->tss = $this->stripComments($this->tss, '/*', '*/');
		$tokenizer = new Tokenizer($this->tss);
		$this->tss = $tokenizer->getTokens();
		$this->baseDir = $baseDir;
		$this->xPath = $xPath;
		$this->valueParser = $valueParser;
	}

	public function parse($indexStart = 0) {
		$rules = [];
		$numOfTokens = count($this->tss);
		for ($i = 0; isset($this->tss[$i]) && $i <= $numOfTokens; $i++) {
			if ($this->tss[$i]['type'] === Tokenizer::WHITESPACE) continue;
			if ($processing = $this->processingInstructions($i, count($rules)+$indexStart)) {
				$i = $processing['endPos']+1;
				$rules = array_merge($rules, $processing['rules']);
				continue;
			}
			$tokens = array_slice($this->tss, $i);
			$selector = $this->splitOnToken($tokens, Tokenizer::OPEN_BRACE)[0];
			$i += count($selector);
			if ($selector[count($selector)-1]['type'] === Tokenizer::WHITESPACE) array_pop($selector);
			if (!isset($this->tss[$i])) break;

			$newRules = $this->cssToRules($selector, count($rules)+$indexStart, $this->getProperties($this->tss[$i]['value']));
			$rules = $this->writeRule($rules, $newRules);
		}
		usort($rules, [$this, 'sortRules']);
		if (empty($rules) && !empty($this->tss)) throw new \Exception("No TSS rules parsed");
		return $rules;
	}

	private function CssToRules($selector, $index, $properties) {
		$parts = $this->splitOnToken($selector, Tokenizer::ARG);
		$rules = [];
		foreach ($parts as $part) {
			$tokenCss = $part;
			$rules[json_encode($part)] = new \Transphporm\Rule($this->xPath->getXpath($tokenCss), $this->xPath->getPseudo($tokenCss), $this->xPath->getDepth($tokenCss), $this->baseDir, $index++);
			$rules[json_encode($part)]->properties = $properties;
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

	private function processingInstructions($key, $indexStart) {
		if (isset($this->tss[$key]) && $this->tss[$key]['type'] !== Tokenizer::AT_SIGN) return false;
		$rules = [];
		$tokens = array_slice($this->tss, $key+1);
		$tokens = $this->splitOnToken($tokens, Tokenizer::SEMI_COLON)[0];
		$pos = $key+count($tokens)+1;
		$funcName = array_shift($tokens)['value'];
		$args = $this->valueParser->parseTokens($tokens);
		$rules = array_merge($rules, $this->$funcName($args, $indexStart));
		return ['endPos' => $pos, 'rules' => $rules];
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

	private function splitOnToken($tokens, $splitOn) {
		$splitTokens = [];
		$i = 0;
		foreach ($tokens as $token) {
			if ($token['type'] === $splitOn) $i++;
			else $splitTokens[$i][] = $token;
		}
		return $splitTokens;
	}

	private function removeWhitespace($tokens) {
		$newTokens = [];
		foreach ($tokens as $token) {
			if ($token['type'] !== Tokenizer::WHITESPACE) $newTokens[] = $token;
		}
		return $newTokens;
	}

	private function getProperties($tokens) {
		$rules = $this->splitOnToken($tokens, Tokenizer::SEMI_COLON);
		$return = [];
		foreach ($rules as $rule) {
			$rule = $this->removeWhitespace($rule);
			if (isset($rule[1]) && $rule[1]['type'] === Tokenizer::COLON) $return[$rule[0]['value']] = array_slice($rule, 2);
		}

		return $return;
	}
}
