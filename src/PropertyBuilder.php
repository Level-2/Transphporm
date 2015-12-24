<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm;
/** Assigns all the basic properties repeat, comment, etc to a $builder instance */
class PropertyBuilder {
	private $builder;

	public function __construct(Builder $builder) {
		$this->builder = $builder;
	}

	//Register the basic properties, content, repeat, display and bind
	public function registerBasicProperties($data, $locale, &$headers, $formatters) {
		$formatter = new Hook\Formatter();
		$formatter->register(new Formatter\Number($locale));
		$formatter->register(new Formatter\Date($locale));
		$formatter->register(new Formatter\StringFormatter());
		
		foreach ($formatters as $format) $formatter->register($format);

		$this->builder->registerProperty('content', new Property\Content($data, $headers, $formatter));
		$this->builder->registerProperty('repeat', new Property\Repeat($data));
		$this->builder->registerProperty('display', new Property\Display);
		$this->builder->registerProperty('bind', new Property\Bind($data));
	}
}