<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2017 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.2                                                             */
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


	public function testTwoValues() {
		$data = new stdclass;
		$data->foo = 'bar';

		$value = new Value($data);


		$result = $value->parse('foo bar');

		$this->assertEquals(['foo', 'bar'], $result);
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



	public function testMissingData() {
		$stub = $this->getFunctionSet([]);

        $value = new Value($stub, true, true);

        $result = $value->parse('data("novaluehere")');

        $this->assertNull($result[0]);

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

	public function testArrayAccessLookupFromFuncResult() {
		$value = new Value(new TestData);

		$result = $value->parse('getArrayObject()[0]');

		$this->assertEquals(['test1Array'], $result);
	}

	public function testNonExistantArrayLookup() {
		$value = new Value(new TestData);

		$result = $value->parse('getArray()[1]');

		$this->assertEquals([false], $result);
	}

	public function testNonExistantPropertyLookup() {
		$value = new Value(new TestData);

		$result = $value->parse('getObj().foo');

		$this->assertEquals([false], $result);
	}

	public function testArrayLookupOnObj() {

		$value = new Value(new TestData);

		$result = $value->parse('getObj()[data(test)]');

		$this->assertEquals(['foo'], $result);

	}

	public function testNullComparison() {
		$value = new Value([]);
		$result = $value->parse('foo.returnFalse()=true');
		$this->assertEquals([false], $result);
	}

    public function testReturnZero() {
        $value = new Value(new TestData);
		$result = $value->parse('data(getZero())');
		$this->assertEquals([0], $result);
    }

    public function testInComparisonTrue() {

    	$stub = $this->getMockBuilder('TestData')->setMethods(['data'])
                     ->getMock();

        $stub->expects($this->any())->method('data')->with($this->equalTo('array'))->will($this->returnValue(['one', 'two']));

    	$value = new Value($stub);

    	$result = $value->parse('"one" in data(array)');

    	$this->assertEquals($result[0], true);
    }


       public function testInComparisonFalse() {

    	$stub = $this->getMockBuilder('TestData')->setMethods(['data'])
                     ->getMock();

        $stub->expects($this->any())->method('data')->with($this->equalTo('array'))->will($this->returnValue(['one', 'two']));

    	$value = new Value($stub);

    	$result = $value->parse('"three" in data(array)');

    	$this->assertEquals($result[0], false);
    }

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

	public function getArrayObject() {
		$obj = new ArrayObject();
		$obj[0] = 'test1Array';
		return $obj;
	}

	public function getObj() {
		$obj = new \stdClass;
		$obj->test = "foo";
		return $obj;
	}

    public function getZero() {
        return 0;
    }
}

class TestBar {
	public function foo() {
		return 'bar123';
	}
}
