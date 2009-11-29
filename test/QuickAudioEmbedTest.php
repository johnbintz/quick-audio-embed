<?php

require_once('PHPUnit/Framework.php');
require_once('MockPress/mockpress.php');
require_once(dirname(__FILE__) . '/../quick-audio-embed.php');

class QuickAudioEmbedTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		_reset_wp();
		$this->qae = new QuickAudioEmbed();
	}

	function providerTestSaveDimensions() {
		return array(
			array('', null),
			array(false, null),
			array(array(), null),
			array('350x', null),
			array('x350', null),
			array('350xx350', null),
			array('350x350', '350x350'),
			array('350.1x350.1', '350x350'),
		);
	}

	/**
	 * @dataProvider providerTestSaveDimensions
	 */
	function testSaveDimensions($input, $expected_output) {
		$this->assertEquals($expected_output, $this->qae->_save_dimensions($input));
	}

	function testSave() {
		$qae = $this->getMock('QuickAudioEmbed', array('_save_test', '_save_test2', '_save_test3'));
		$qae->expects($this->once())->method('_save_test')->with('test');
		$qae->expects($this->never())->method('_save_test2');
		$qae->expects($this->once())->method('_save_test3')->with('test3')->will($this->returnValue('test4'));

		$qae->save(array('test' => 'test', 'test3' => 'test3', 'test5' => 'test5'));

		$this->assertEquals(array('test3' => 'test4', 'test5' => 'test5'), get_option('quick-audio-embed-settings'));
		$this->assertEquals(array('test3' => 'test4', 'test5' => 'test5'), $qae->settings);
	}
}
