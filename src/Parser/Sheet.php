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
	private $rules;
	private $file;
	private $baseDir;
	private $valueParser;
	private $xPath;
	private $tokenizer;
	private $import = [];

	public function __construct($tss, $templatePrefix, &$baseDir, CssToXpath $xPath, Value $valueParser, \Transphporm\Cache $cache) {
		$this->cache = $cache;
		$this->prefix = $templatePrefix;
		$this->baseDir = &$baseDir;
		if (is_file($tss)) {
			$this->file = $tss;
			$this->rules = $this->getRulesFromCache($tss, $templatePrefix);
			$baseDir = dirname(realpath($tss)) . DIRECTORY_SEPARATOR;
			if (empty($this->rules)) $tss = file_get_contents($tss);
			else return;
		}
		$this->tss = $this->stripComments($tss, '//', "\n");
		$this->tss = $this->stripComments($this->tss, '/*', '*/');
		$this->tokenizer = new Tokenizer($this->tss);
		$this->tss = $this->tokenizer->getTokens();
		$this->xPath = $xPath;
		$this->valueParser = $valueParser;
	}

	private function getRulesFromCache() {
		//The cache for the key: the filename and template prefix
		//Each template may have a different prefix which changes the parsed TSS,
		//Because of this the cache needs to be generated for each template prefix.
		$key = $this->getCacheKey($this->file);
		//Try to load the cached rules, if not set in the cache (or expired) parse the supplied sheet
		$rules = $this->cache->load($key, filemtime($this->file));
		if ($rules) {
			foreach ($rules['import'] as $file) {
				if (!$this->cache->load($this->getCacheKey($file), filemtime($file))) return false;
			}
		}
		return $rules;
	}

	private function getCacheKey($file) {
		return $file . $this->prefix . dirname(realpath($file)) . DIRECTORY_SEPARATOR;
	}

	public function parse($indexStart = 0) {
		if (!empty($this->rules)) return $this->rules['rules'];
		$rules = $this->parseTokens($indexStart);
		usort($rules, [$this, 'sortRules']);
		$this->checkError($rules);
		if (!empty($this->file)) $this->cache->write($this->getCacheKey($this->file), ['rules' => $rules, 'import' => $this->import]);
		return $rules;
	}

	private function parseTokens($indexStart) {
		$rules = [];
		$line = 1;
		foreach (new TokenFilterIterator($this->tss, [Tokenizer::WHITESPACE]) as $token) {
			if ($processing = $this->processingInstructions($token, count($rules)+$indexStart)) {
				$this->tss->skip($processing['skip']+1);
				$rules = array_merge($rules, $processing['rules']);
				continue;
			}
			else if ($token['type'] === Tokenizer::NEW_LINE) {
				$line++;
				continue;
			}
			$selector = $this->tss->from($token['type'], true)->to(Tokenizer::OPEN_BRACE);
			$this->tss->skip(count($selector));
			if (count($selector) === 0) break;

			$newRules = $this->cssToRules($selector, count($rules)+$indexStart, $this->getProperties($this->tss->current()['value']), $line);
			$rules = $this->writeRule($rules, $newRules);
		}
		return $rules;
	}

	private function checkError($rules) {
		if (empty($rules) && count($this->tss) > 0) throw new \Exception('No TSS rules parsed');
	}

	private function CssToRules($selector, $index, $properties, $line) {
		$parts = $selector->trim()->splitOnToken(Tokenizer::ARG);
		$rules = [];
		foreach ($parts as $part) {
			$part = $part->trim();
			$rules[$this->tokenizer->serialize($part)] = new \Transphporm\Rule($this->xPath->getXpath($part), $this->xPath->getPseudo($part), $this->xPath->getDepth($part), $index++, $this->file, $line);
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
		$tokens = $this->tss->from(Tokenizer::AT_SIGN, false)->to(Tokenizer::SEMI_COLON, false);
		$funcName = $tokens->from(Tokenizer::NAME, true)->read();
		$args = $this->valueParser->parseTokens($tokens->from(Tokenizer::NAME));
		$rules = $this->$funcName($args, $indexStart);

		return ['skip' => count($tokens)+1, 'rules' => $rules];
	}

	private function import($args, $indexStart) {
		if ($this->file !== null) $fileName = dirname(realpath($this->file)) . DIRECTORY_SEPARATOR . $args[0];
		else $fileName = $args[0];
		$this->import[] = $fileName;
		$sheet = new Sheet($fileName, $this->prefix, $this->baseDir, $this->xPath, $this->valueParser, $this->cache);
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
