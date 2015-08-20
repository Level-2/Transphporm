<?php
namespace CDS;
interface Hook {
	public function run(\DomElement $element);
}