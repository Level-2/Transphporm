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

		$tokenizer = new \Transphporm\Parser\Tokenizer($rules['format']);
		$tokens = $tokenizer->getTokens();

		$functionName = $tokens[0]['value'];
		$options = [];
		for ($i = 1; $i < count($tokens); $i++) $options[] = $tokens[$i]['value'];

		return $this->processFormat($options, $functionName, $value);		
	}

	private function processFormat($format, $functionName, $value) {
		foreach ($value as &$val) {
			foreach ($this->formatters as $formatter) {
				if (is_callable([$formatter, $functionName])) {
					$val = call_user_func_array([$formatter, $functionName], array_merge([$val], $format));
				}
			}
		}
		return $value;
	}
}