<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Hook;
/** Handles format: foo bar properties in the stylesheet */
class Formatter {
	private $formatters = [];

	public function register($formatter) {
		$this->formatters[] = $formatter;
	}

	public function format($value, $rules) {
		if (!isset($rules['format'])) return $value;
		$tokens = $rules['format'];

		$functionName = $tokens->from(\Transphporm\Parser\Tokenizer::NAME, true)->read();

		$options = [];
		foreach (new \Transphporm\Parser\TokenFilterIterator($tokens->from(\Transphporm\Parser\Tokenizer::NAME),
					[\Transphporm\Parser\Tokenizer::WHITESPACE]) as $token) {
			$options[] = $token['value'];
		}

		try {
			return $this->processFormat($options, $functionName, $value);
		}
		catch (\Exception $e) {
			throw new \Transphporm\RunException(\Transphporm\Exception::FORMATTER, $functionName, $e);
		}
	}

	//TODO: Abstract all error reporting externally with a method for turning it on/off
	private function assert($condition, $error) {
		if (!$condition) throw new \Exception($error);
	}

	private function processFormat($format, $functionName, $value) {
		$functionExists = false;
		foreach ($value as &$val) {
			foreach ($this->formatters as $formatter) {
				if (is_callable([$formatter, $functionName])) {
					$val = call_user_func_array([$formatter, $functionName], array_merge([$val], $format));
					$functionExists = true;
				}
			}
		}

		$this->assert($functionExists, "Formatter '$functionName' does not exist");
		return $value;
	}
}
