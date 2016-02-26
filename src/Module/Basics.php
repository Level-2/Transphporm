<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Module;
/** Assigns all the basic properties repeat, comment, etc to a $builder instance */
class Basics implements \Transphporm\Module {


	public function load(\Transphporm\FeatureSet $featureSet) {
		$data = $featureSet->getData();
		$headers = &$featureSet->getHeaders();

		$featureSet->registerProperty('content', new \Transphporm\Property\Content($data, $headers, $featureSet->getFormatter()));
		$featureSet->registerProperty('repeat', new \Transphporm\Property\Repeat($data));
		$featureSet->registerProperty('display', new \Transphporm\Property\Display);
		$featureSet->registerProperty('bind', new \Transphporm\Property\Bind($data));
	}
}