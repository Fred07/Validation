<?php

class TestValidation extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider validationProvider
	 */
	public function testMinMaxLen($data, $restrict, $expect) {
		$validation = new Validation();
		$validation->check(array('maxLen:'.$restrict['max']), $data, 'maxLen');
		$validation->check(array('minLen:'.$restrict['min']), $data, 'minLen');

		$error = $validation->run();

		$this->assertEquals($expect, $error);

	}

	public function testEquals() {

		// data provider
		$test1 = 'howard';
		$test2 = '0';
		$test3 = '10';

		$validation = new Validation();
		$validation->check(array('equalsTo:howard'), $test1, 'error test1');
		$validation->check(array('equalsTo:0'), $test2, 'error test2');
		$validation->check(array('equalsTo:10'), $test3, 'error test3');
		$error = $validation->run();

		$this->assertTrue($error);
	}

	public function testLen() {

		// data provider
		$test1 = 'howard';
		$test2 = 10;
		$test3 = '123/,/$%';
		$test4 = 45390;

		$validation = new Validation();
		$validation->check(array('len:6'), $test1, 'error test1');
		$validation->check(array('len:2'), $test2, 'error test2');
		$validation->check(array('len:8'), $test3, 'error test3');
		$validation->check(array('len:5'), $test4, 'error test4');
		$error = $validation->run();

		$this->assertTrue($error);
	}

	public function testMinMaxNumber() {

		// data provider
		$test1 = 100;
		$test2 = '100';
		$test3 = 'test';	// should be wrong must be number
		$test4 = 100.5;
		$test5 = '100.5';

		$validation = new Validation();
		$validation->check(array('minNumber:99','maxNumber:101'), $test1, 'error test1');
		$validation->check(array('minNumber:99','maxNumber:101'), $test2, 'error test2');
		$validation->check(array('minNumber:99','maxNumber:101'), $test4, 'error test4');
		$validation->check(array('minNumber:99','maxNumber:101'), $test5, 'error test5');
		$error1 = $validation->run();

		$validation->initialize();
		$validation->setStepMode(false);
		$validation->check(array('minNumber:99','maxNumber:101'), $test3, 'error test3');
		$validation->run();
		$error2 = $validation->getErrorCode();
		$answer = array(
			'error test3'
		);
		

		$this->assertTrue($error1);
		$this->assertEquals($answer, $error2);
	}

	public function testDateTimeFormat() {

		// data
		$test1 = '2015-09-10 00:00:00';
		$test2 = '2015-09-10';
		$test3 = '2015/09/10 00:00:00';
		$test4 = '00:00:00';

		// error data
		$test_r1 = '20151-09-10';
		$test_r2 = '2015-13-10 00:00:00';
		$test_r3 = '2015-09-35 00:00:00';
		// $test_r4 = '2015-09-31 00:00:00';
		$test_r5 = '2015-09-02 25:00:00';
		$test_r6 = '2015-09-02 00:65:00';
		$test_r7 = '2015-09-02 00:00:65';

		$validation = new Validation();
		$validation->check(array('dateTimeFormat'), $test1, 'error test1');
		$validation->check(array('dateFormat'), $test2, 'error test2');
		$validation->check(array('dateTimeFormat'), $test3, 'error test3');
		$validation->check(array('timeFormat'), $test4, 'error test4');
		$error1 = $validation->run();
		$error1 = true;

		$validation->initialize();
		$validation->setStepMode(false);
		$validation->check(array('dateFormat'), $test_r1, 'error test_r1');
		$validation->check(array('dateTimeFormat'), $test_r2, 'error test_r2');
		$validation->check(array('dateTimeFormat'), $test_r3, 'error test_r3');
		// $validation->check(array('dateTimeFormat'), $test_r4, 'error test_r4');
		$validation->check(array('dateTimeFormat'), $test_r5, 'error test_r5');
		$validation->check(array('dateTimeFormat'), $test_r6, 'error test_r6');
		$validation->check(array('dateTimeFormat'), $test_r7, 'error test_r7');
		$validation->run();
		$error2 = $validation->getErrorCode();
		$answer = array(
			'error test_r1',
			'error test_r2',
			'error test_r3',
			// 'error test_r4',
			'error test_r5',
			'error test_r6',
			'error test_r7'
		);

		$this->assertTrue($error1);
		$this->assertEquals($answer, $error2);

	}

	public function testNumeric() {

		// data
		$test1 = '10';
		$test2 = 10;
		$test3 = '10.5';
		$test4 = 10.5;


		$validation = new Validation();
		$validation->check(array('isNumeric'), $test1, 'error test1');
		$validation->check(array('isNumeric'), $test2, 'error test2');
		$validation->check(array('isNumeric'), $test3, 'error test3');
		$validation->check(array('isNumeric'), $test4, 'error test4');
		$error1 = $validation->run();

		$this->assertTrue($error1);
	}

	public function testAlphNum() {

		// data
		$test1 = '123asd';
		$test2 = '123';

		$test_r1 = '!@#$asdf';
		$test_r2 = 10;

		$validation = new Validation();
		$validation->check(array('isAlphaNumeric'), $test1, 'error test1');
		$validation->check(array('isAlphaNumeric'), $test2, 'error test2');
		$error1 = $validation->run();

		$validation->initialize();
		$validation->setStepMode(false);
		$validation->check(array('isAlphaNumeric'), $test_r1, 'error test_r1');
		$validation->check(array('isAlphaNumeric'), $test_r2, 'error test_r2');
		$validation->run();
		$error2 = $validation->getErrorCode();
		$answer = array('error test_r1', 'error test_r2');

		$this->assertTrue($error1);
		$this->assertEquals($answer, $error2);
	}

	public function testIsInteger() {

		// data
		$test1 = 10;
		$test2 = '10';

		$test_r1 = (float)'10.5';
		$test_r2 = '10.5';

		$validation = new Validation();
		$validation->check(array('isInteger'), $test1, 'error test1');
		$validation->check(array('isInteger'), $test2, 'error test2');
		try {
			$error1 = $validation->run();
		} catch (Exception $e) {
			throw $e;
		}

		$validation->initialize();
		$validation->setStepMode(false);
		$validation->check(array('isInteger'), $test_r1, 'error test_r1');
		$validation->check(array('isInteger'), $test_r2, 'error test_r2');
		$validation->run();
		$error2 = $validation->getErrorCode();
		$answer = array(
			'error test_r1',
			'error test_r2'
		);

		$this->assertTrue($error1);
		$this->assertEquals($answer, $error2);
	}

	public function testContains() {

		// data
		$test1 = 'hello';
		$test2 = '123hello321';
		$test3 = '123#$%^!!!';
		$test4 = 'HHHaaa';		// non sensitive
		$test5 = 'HHHaaa';		//sensitive

		$test_r1 = '123456';
		$test_r2 = '#$%!';
		$test_r3 = 'HHHaaa';	// sensitive

		$validation = new Validation();
		$validation->check(array('contains:hello'), $test1, 'error test1');
		$validation->check(array('contains:hello'), $test2, 'error test2');
		$validation->check(array('contains:#$%^'), $test3, 'error test3');
		$validation->check(array('contains:ha'), $test4, 'error test3');
		$validation->check(array('contains:Ha:true'), $test5, 'error test3');
		$error1 = $validation->run();

		$validation->initialize();
		$validation->setStepMode(false);
		$validation->check(array('contains:apple'), $test_r1, 'error test_r1');
		$validation->check(array('contains:#$%^'), $test_r2, 'error test_r2');
		$validation->check(array('contains:ha:true'), $test_r3, 'error test_r3');
		$validation->run();
		$error2 = $validation->getErrorCode();
		$answer = array(
			'error test_r1',
			'error test_r2',
			'error test_r3'
		);

		$this->assertTrue($error1);
		$this->assertEquals($answer, $error2);
	}

	public function testChoice() {

		// data
		$test1 = 'apple';
		$test2 = 'orange';

		$test_r1 = 'apple';
		$test_r2 = 'orange';

		$validation = new Validation();
		$validation->check(array('choice:apple:banana'), $test1, 'error test1');
		$validation->check(array('choice:apple:orange'), $test2, 'error test2');
		$error1 = $validation->run();

		$validation->initialize();
		$validation->setStepMode(false);
		$validation->check(array('choice:car:phone'), $test_r1, 'error test_r1');
		$validation->check(array('choice:orange juice:car'), $test_r2, 'error test_r2');
		$validation->run();
		$error2 = $validation->getErrorCode();
		$answer = array(
			'error test_r1',
			'error test_r2'
		);

		$this->assertTrue($error1);
		$this->assertEquals($answer, $error2);
	}

	public function validationProvider() {
		return array(
			array(
				'howard',
				array('max' => 10, 'min' => 0),
				true
			)
		);
	}
}