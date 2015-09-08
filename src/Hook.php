<?php
namespace Transphporm;
interface Hook {
	public function run(\DomElement $element);
}