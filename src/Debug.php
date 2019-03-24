<?php
namespace Transphporm;
class Debug {
    private $template;
	private $tss;
    private $filePath;
    private $validator;

    public function __construct($template, $tss = '') {
        $this->filePath = new FilePath();
        $this->validator = new TSSValidator();
		$this->template = $template;
        if (is_file($tss)) {
			$this->filePath->addPath(dirname(realpath($tss)));
			$tss = file_get_contents($tss);
		}
		$this->tss = (new SheetTokenizer($tss))->getTokens();
	}

    public function output($data = null, $document = false) {
        if (!$this->validator->validate($this->tss)) return false;

        return true;
    }

    private function checkFileRefrences($tss) {
        foreach (new TokenFilterIterator($this->tss, [Tokenizer::WHITESPACE]) as $token) {
            if (!$this->checkImportFile($token)) return false;
        }
        return true;
    }

    private function checkImportFile($token) {
        if ($token['type'] !== Tokenizer::AT_SIGN) return false;
        $tokens = $this->tss->from(Tokenizer::AT_SIGN, false)->to(Tokenizer::SEMI_COLON, false);
		$funcName = $tokens->from(Tokenizer::NAME, true)->read();
		$args = $this->valueParser->parseTokens($tokens->from(Tokenizer::NAME));
        return is_file($args[0]);
    }
}
