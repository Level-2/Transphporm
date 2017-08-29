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
        $splitTokens = $braceToken['value']->splitOnToken(Tokenizer::COLON);
        array_shift($splitTokens); array_pop($splitTokens);
        foreach ($splitTokens as $tokens)
            if (!in_array(Tokenizer::SEMI_COLON, array_column(iterator_to_array($tokens), 'type'))) return false;

        return true;
    }

    private function checkParenthesis($token) {
        return substr_count($token['string'], '(') === substr_count($token['string'], ')');
    }

    private function tokenize($tss) {
        if (is_file($tss)) $tss = file_get_contents($tss);
        return (new Parser\Tokenizer($tss))->getTokens();
    }
}
