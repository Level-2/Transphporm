<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         0.9                                                             */
use Transphporm\Parser\Value;
use Transphporm\FunctionSet;
use Transphporm\Hook\ElementData;

class ValueParserTest extends PHPUnit_Framework_TestCase {


	public function testBasicString() {
		$value = new Value(new stdclass);

		$result = $value->parse('"test one"');

		$this->assertEquals(['test one'], $result);
	}

	public function testBasicName() {
		$data = new stdclass;
		$data->foo = 'bar';

		$value = new Value($data);


		$result = $value->parse('foo');

		$this->assertEquals(['foo'], $result);
	}

/*
	public function testBasicConditional() {
		$data = new stdclass;
		$data->foo = 'bar';

		$value = new Value($data);


		$result = $value->parse('foo="bar"');

		$this->assertEquals([true], $result);
	}
*/

	public function testMultipleValues() {
		$value = new Value(new stdclass);

		$result = $value->parse('"foo", "bar"');

		$this->assertEquals(['foo', 'bar'], $result);
	}

	public function testStringEscape() {
		$value = new Value(new stdclass);

		$result = $value->parse('"foo\"bar"');
		$this->assertEquals(['foo"bar'], $result);

	}


	public function testPlusConcat() {
		$value = new Value(new stdclass);

		$result = $value->parse('"foo" + "bar"');

		$this->assertEquals(['foobar'], $result);
	}


	public function testPlusConcat2() {
		$value = new Value(new stdclass);

		$result = $value->parse('"foo" + "bar" + "baz"');

		$this->assertEquals(['foobarbaz'], $result);
	}


	public function testMultipleAndConcat() {
		$value = new Value(new stdclass);

		$result = $value->parse('"foo" + "bar" + "baz", "one" + "two"');

		$this->assertEquals(['foobarbaz', 'onetwo'], $result);
	}


	private function getDataStub() {
		$stub = $this->getMockBuilder('TestData')->setMethods(['data', 'func2'])
                     ->getMock();

        $stub->expects($this->any())->method('data')->with($this->equalTo('foo'))->will($this->returnValue('bar'));

		$subStub = $this->getMockBuilder('TestData')->setMethods(['someMethod'])
                     ->getMock();

        return $stub;
	}

	public function testFunctionCallBasic() {

		$stub = $this->getDataStub();

        $value = new Value($stub, false);

        $result = $value->parse('data(foo)');

        $this->assertEquals(['bar'], $result);
	}

	public function testFunctionConcat1() {
		$stub = $this->getDataStub();

        $value = new Value($stub, false);

        $result = $value->parse('data(foo) + "foo"');

        $this->assertEquals(['barfoo'], $result);
	}

	public function testFunctionConcatMulti() {
		$stub = $this->getDataStub();

        $value = new Value($stub, false);

        $result = $value->parse('data(foo) + "foo", data(foo)');

        $this->assertEquals(['barfoo', 'bar'], $result);
	}


	public function testNestedValueCall() {

        $value = new Value(new TestData, false);

        $result = $value->parse('one(two("a"))');

        $this->assertEquals(['test!aone'], $result);
	}


	public function testFunctionCallWithConcat() {

        $value = new Value(new TestData, false);

        $result = $value->parse('two("a" + "b")');

        $this->assertEquals(['test!ab'], $result);
	}

	public function testFunctionTraverse() {
		$value = new Value(new TestData, false);

		$result = $value->parse('getBar().foo()');

        $this->assertEquals(['bar123'], $result);
	}

	public function testProperty() {
		$data = new stdclass;
		$data->user = new stdclass;
		$data->user->name = 'foo';

		//Enable auto-lookup so user can be used instead of data(user).name
		$value = new Value($data, true);

		$result = $value->parse('user.name');

		$this->assertEquals(['foo'], $result);
	}

	public function testNonExistantProperty() {
		$data = new stdclass;
		$data->user = new stdclass;
		$data->user->name = 'foo';

		//Enable auto-lookup so user can be used instead of data(user).name
		$value = new Value($data, true);

		$result = $value->parse('user.info');

		$this->assertEquals([false], $result);
	}

	public function testNonExistantIndex() {
		$data = new stdclass;
		$data->user = [];
		$data->user['name'] = 'foo';

		//Enable auto-lookup so user can be used instead of data(user).name
		$value = new Value($data, true);

		$result = $value->parse('user["info"]');

		$this->assertEquals([false], $result);
	}

	public function testTwoArgs() {
		$value = new Value(new TestData);

		$result = $value->parse('2, 3');

		$this->assertEquals(['2', '3'], $result);
	}


	public function testConditionalExpression() {
		$value = new Value(new TestData);

		$result = $value->parse('data(foo)="foo"');

		$this->assertEquals([true], $result);
	}

	public function testConditionalExpressionFalse() {
		$value = new Value(new TestData);

		$result = $value->parse('data(foo)="bar"');

		$this->assertEquals([false], $result);
	}

	public function testConditionalExpressionNot() {
		$value = new Value(new TestData);

		$result = $value->parse('data(foo)!="bar"');

		$this->assertEquals([true], $result);
	}

	public function testConditionalExpressionNotTrue() {
		$value = new Value(new TestData);

		$result = $value->parse('data(foo)!="foo"');

		$this->assertEquals([false], $result);
	}

	private function getFunctionSet($data) {
		$elementData = new ElementData(new \SplObjectStorage(), $data);
		$functionSet = new FunctionSet($elementData);

		$functionSet->addFunction('data', new \Transphporm\TSSFunction\Data($elementData, $functionSet));
		return $functionSet;

	}


	public function testConditionalAutoLookup() {
		$value = new Value(new TestData, true);

		$result = $value->parse('foo="bar"');

		$this->assertEquals([true], $result);
	}
/*
	public function testConditionalDataFunction() {
		$value = new Value($this->getFunctionSet(new TestData), true);

		$data = $value->parse('data[foo="bar"]');

		$this->assertEquals([true], $data);

	}
*/
	public function testArrayLookup() {
		$data = ["anArray" => [
			'one' => 'a',
			'two' => 'two'
		]];

		$value = new Value($data, true);

		$result = $value->parse('anArray["one"]');

		$this->assertEquals(['a'], $result);

	}

	public function testArrayLookupFromFuncResult() {
		$value = new Value(new TestData);

		$result = $value->parse('getArray()[0]');

		$this->assertEquals(['test1Array'], $result);
	}

	//public fucntion testNested
}

class TestData {

	public $foo = 'bar';

	public function one($arg) {
		return $arg.'one';
	}

	public function two($arg) {
		return 'test!' . $arg;
	}

	public function getBar() {
		return new TestBar();
	}

	public function data($a) {
		return $a;
	}

	public function getArray() {
		return [
			'test1Array'
		];
	}
}

class TestBar {
	public function foo() {
		return 'bar123';
	}
}
