<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Parser;
/** Parses a .tss file into individual rules, each rule has a query e,g, `ul li` and a set of rules e.g. `display: none; bind: iteration(id);` */
class Sheet {
	private $cache;
	private $tss;
	private $rules;
	private $file;
	private $valueParser;
	private $xPath;
	private $tokenizer;
	private $filePath;
	private $import = [];

	public function __construct($tss, CssToXpath $xPath, Value $valueParser, \Transphporm\TSSCache $cache, \Transphporm\FilePath $filePath) {
		$this->cache = $cache;
		if (is_file($tss)) {
			$this->file = $tss;
			$this->rules = $this->cache->load($tss);
			$filePath->setBaseDir(dirname(realpath($tss)) . DIRECTORY_SEPARATOR);
			if (empty($this->rules)) $tss = file_get_contents($tss);
			else return;
		}
		$this->tss = $this->stripComments($tss, '//', "\n");
		$this->tss = $this->stripComments($this->tss, '/*', '*/');
		$this->tokenizer = new Tokenizer($this->tss);
		$this->tss = $this->tokenizer->getTokens();
		$this->xPath = $xPath;
		$this->valueParser = $valueParser;
		$this->filePath = $filePath;
	}

	public function parse($indexStart = 0) {
		if (!empty($this->rules)) return $this->rules['rules'];
		$rules = $this->parseTokens($indexStart);
		usort($rules, [$this, 'sortRules']);
		$this->checkError($rules);
		return $this->cache->write($this->file, $rules, $this->import);
	}

	private function parseTokens($indexStart) {
		$this->rules = [];
		$line = 1;
		foreach (new TokenFilterIterator($this->tss, [Tokenizer::WHITESPACE]) as $token) {
			if ($processing = $this->processingInstructions($token, count($this->rules)+$indexStart)) {
				$this->rules = array_merge($this->rules, $processing);
				continue;
			}
			else if ($token['type'] === Tokenizer::NEW_LINE) {
				$line++;
				continue;
			}
			else $this->addRules($token, $indexStart, $line);
		}
		return $this->rules;
	}

	private function addRules($token, $indexStart, $line) {
		$selector = $this->tss->from($token['type'], true)->to(Tokenizer::OPEN_BRACE);
		$this->tss->skip(count($selector));
		if (count($selector) === 0) return;

		$newRules = $this->cssToRules($selector, count($this->rules)+$indexStart, $this->getProperties($this->tss->current()['value']), $line);
		$this->rules = $this->writeRule($this->rules, $newRules);
	}

	private function checkError($rules) {
		if (empty($rules) && count($this->tss) > 0) throw new \Exception('No TSS rules parsed');
	}

	private function CssToRules($selector, $index, $properties, $line) {
		$parts = $selector->trim()->splitOnToken(Tokenizer::ARG);
		$rules = [];
		foreach ($parts as $part) {
			$serialized = serialize($part);
			$rules[$serialized] = new \Transphporm\Rule($this->xPath->getXpath($part), $this->xPath->getPseudo($part), $this->xPath->getDepth($part), $index++, $this->file, $line);
			$rules[$serialized]->properties = $properties;
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
		$tokens = $this->tss->from(Tokenizer::AT_SIGN, false)->to(Tokenizer::SEMI_COLON, false);
		$funcName = $tokens->from(Tokenizer::NAME, true)->read();
		$args = $this->valueParser->parseTokens($tokens->from(Tokenizer::NAME));
		$rules = $this->$funcName($args, $indexStart);

		$this->tss->skip(count($tokens)+2);

		return $rules;
	}

	private function import($args, $indexStart) {
		if ($this->file !== null) $fileName = $fileName = $this->filePath->getFilePath($args[0]);
		else $fileName = $args[0];
		$this->import[] = $fileName;
		$baseDirTemp = $this->filePath->getFilePath();
		$sheet = new Sheet($fileName, $this->xPath, $this->valueParser, $this->cache, $this->filePath);
		$this->filePath->setBaseDir($baseDirTemp);
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
