<?php
namespace Transphporm;
use Transphporm\Parser\Tokenizer;
class TSSValidator {
    private $error;

    public function validate($tss) {
        $this->error = null;
        $tokens = $this->tokenize($tss);

        foreach ($tokens as $token)
            if (!$this->validateRule($token)) return false;

        return true;
    }

    public function getLastError() {
        return $this->error;
    }

    private function validateRule($token) {
        if ($token['type'] !== Tokenizer::OPEN_BRACE) return true;

        return $this->checkBraces($token) && $this->checkSemicolons($token)
            && $this->checkParenthesis($token);
    }

    private function checkBraces($token) {
        return strpos($token['string'], '{') === false;
    }

    private function checkSemicolons($braceToken) {
        $prevSemicolon = true;
        foreach ($braceToken['value'] as $token) {
            if ($token['type'] === Tokenizer::SEMI_COLON) $prevSemicolon = true;
            if ($token['type'] === Tokenizer::COLON && !$prevSemicolon) return false;
            else if ($token['type'] === Tokenizer::COLON) $prevSemicolon = false;
        }
        return true;
    }

    private function checkParenthesis($token) {
        return substr_count($token['string'], '(') === substr_count($token['string'], ')');
    }

    private function tokenize($tss) {
        if (is_file($tss)) $tss = file_get_contents($tss);
        $tss = $this->stripComments($tss, '//', "\n");
		$tss = $this->stripComments($tss, '/*', '*/');
		$tokenizer = new Tokenizer($tss);
		return $tokenizer->getTokens();
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
