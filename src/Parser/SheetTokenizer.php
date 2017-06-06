<?php
namespace Transphporm\Parser;
class SheetTokenizer {
    private $tokenizer;

    public function __construct($tss) {
        $tss = $this->stripComments($tss, '//', "\n");
		$tss = $this->stripComments($tss, '/*', '*/');
		$this->tokenizer = new Tokenizer($tss);
    }

    public function getTokens() {
		return $this->tokenizer->getTokens();
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
}
