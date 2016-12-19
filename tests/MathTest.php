<?php
use Transphporm\Builder;

class MathTest extends PHPUnit_Framework_TestCase {
    private $xml = "<div></div>";

    private function resultOfTest($val) {
        return "<div>" . $val . "</div>";
    }

    public function testAddFunc() {
        $tss = "div { content: add(2, 4); }";

        $template = new Builder($this->xml, $tss);
        $this->assertEquals($this->resultOfTest(6), $template->output()->body);
    }

    public function testSubtractFunc() {
        $tss = "div { content: sub(2, 4); }";

        $template = new Builder($this->xml, $tss);
        $this->assertEquals($this->resultOfTest(-2), $template->output()->body);
    }

    public function testMultiplyFunc() {
        $tss = "div { content: mult(2, 4); }";

        $template = new Builder($this->xml, $tss);
        $this->assertEquals($this->resultOfTest(8), $template->output()->body);
    }

    public function testDivideFunc() {
        $tss = "div { content: div(2, 4); }";

        $template = new Builder($this->xml, $tss);
        $this->assertEquals($this->resultOfTest(0.5), $template->output()->body);
    }

}
