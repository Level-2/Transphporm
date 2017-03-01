<?php
namespace Transphporm\Formatter;
class HTMLFormatter {
    private $templateFunction;

    public function __construct(\Transphporm\TSSFunction\Template $templateFunction) {
        $this->templateFunction = $templateFunction;
    }

    public function html($val) {
    	if ($val[0] != '<') {
    		$val = '<div>' . $val . '</div>';
	    }
		return $this->templateFunction->run([$val]);
	}

	public function debug($val) {
		ob_start();
		var_dump($val);
		return $this->html('<pre>' . ob_get_clean() . '</pre>');
	}
}
