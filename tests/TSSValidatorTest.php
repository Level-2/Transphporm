<?php
use Transphporm\TSSValidator;
class TSSValidatorTest extends PHPUnit_Framework_TestCase {

    public function testMissingClosingBrace() {
        $tss = "
            div { content: 'Test1';
            span { content: 'Test2'; }
        ";

        $validator = new TSSValidator();

        $this->assertFalse($validator->validate($tss));
    }

    public function testMissingSemicolon() {
        $tss = "
            div {
                content: 'Test1'
                content-mode: replace;
            }
        ";

        $validator = new TSSValidator();

        $this->assertFalse($validator->validate($tss));
    }

    public function testMissingParenthesis() {
        $tss = "
            div {
                content: data(attr(test);
            }
        ";

        $validator = new TSSValidator();

        $this->assertFalse($validator->validate($tss));
    }

    public function testValidTSS() {
        $tss = "
            div { content: data(attr(test)); }
            span { content: 'Test2'; }

        ";

        $validator = new TSSValidator();

        $this->assertTrue($validator->validate($tss));
    }

}
