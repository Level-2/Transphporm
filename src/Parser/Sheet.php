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
		$this->baseDir = $baseDir;
		$this->xPath = $xPath;
		$this->valueParser = $valueParser;
	}

	public function parse($pos = 0, $rules = [], $indexStart = 0) {
		while ($next = strpos($this->tss, '{', $pos)) {
			if ($processing = $this->processingInstructions($this->tss, $pos, $next, count($rules)+$indexStart)) {
				$pos = $processing['endPos']+1;
				$rules = array_merge($rules, $processing['rules']);
			}

			$selector = trim(substr($this->tss, $pos, $next-$pos));
			$pos =  strpos($this->tss, '}', $next)+1;
			$newRules = $this->cssToRules($selector, count($rules)+$indexStart, $this->getProperties(trim(substr($this->tss, $next+1, $pos-2-$next))));
			$rules = $this->writeRule($rules, $newRules);
		}
		//there may be processing instructions at the end
		if ($processing = $this->processingInstructions($this->tss, $pos, strlen($this->tss), count($rules)+$indexStart)) $rules = array_merge($rules, $processing['rules']);
		usort($rules, [$this, 'sortRules']);
		if (empty($rules) && !empty($this->tss)) throw new \Exception("No TSS rules parsed");
		return $rules;
	}

	private function CssToRules($selector, $index, $properties) {
		$parts = explode(',', $selector);
		$rules = [];
		foreach ($parts as $part) {
			$rules[$part] = new \Transphporm\Rule($this->xPath->getXpath($part), $this->xPath->getPseudo($part), $this->xPath->getDepth($part), $this->baseDir, $index++);
			$rules[$part]->properties = $properties;
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

	private function processingInstructions($tss, $pos, $next, $indexStart) {
		$rules = [];
		while (($atPos = strpos($tss, '@', $pos)) !== false) {
			if ($atPos  <= (int) $next) {
				$spacePos = strpos($tss, ' ', $atPos);
				$funcName = substr($tss, $atPos+1, $spacePos-$atPos-1);
				$pos = strpos($tss, ';', $spacePos);
				$args = substr($tss, $spacePos+1, $pos-$spacePos-1);
				$rules = array_merge($rules, $this->$funcName($args, $indexStart));
			}
			else {
				break;
			}
		}

		return empty($rules) ? false : ['endPos' => $pos, 'rules' => $rules];
	}

	private function import($args, $indexStart) {
		if (is_file(trim($args,'\'" '))) $fileName = trim($args,'\'" ');
		else $fileName = $this->valueParser->parse($args)[0];
		$sheet = new Sheet(file_get_contents($this->baseDir . $fileName), dirname(realpath($this->baseDir . $fileName)) . DIRECTORY_SEPARATOR, $this->xPath, $this->valueParser);
		return $sheet->parse(0, [], $indexStart);
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

	private function getProperties($str) {
		$tokenizer = new Tokenizer($str);
		$tokens = $tokenizer->getTokens();

		$rules = [];
		$i = 0;
		foreach ($tokens as $token) {
			if ($token['type'] === Tokenizer::SEMI_COLON) $i++;
			else $rules[$i][] = $token;
		}

		$return = [];
		foreach ($rules as $rule) {
			if ($rule[1]['type'] === Tokenizer::COLON) $return[$rule[0]['value']] = array_slice($rule, 2);
		}

		return $return;
	}
}
