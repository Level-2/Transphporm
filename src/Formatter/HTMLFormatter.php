<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
namespace Transphporm\Formatter;
class HTMLFormatter {
    private $templateFunction;

    public function __construct(\Transphporm\TSSFunction\Template $templateFunction) {
        $this->templateFunction = $templateFunction;
    }

    public function html($val) {
		return $this->templateFunction->run(['<template>' . $val . '</template>']);
	}

	public function debug($val) {
		ob_start();
		var_dump($val);
		return $this->html('<pre>' . ob_get_clean() . '</pre>');
	}
}
