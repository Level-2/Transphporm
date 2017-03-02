<?php
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
