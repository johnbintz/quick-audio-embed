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

	function providerTestLoad() {
		return array(
			array(false, array('test' => 'test')),
			array(array(), array('test' => 'test')),
			array(array('test' => 'test2'), array('test' => 'test2')),
			array(array('test2' => 'test2'), array('test' => 'test', 'test2' => 'test2')),
		);
	}

	/**
	 * @dataProvider providerTestLoad
	 */
	function testLoad($options, $expected_settings) {
		$this->qae->settings = array('test' => 'test');
		update_option('quick-audio-embed-settings', $options);

		$this->qae->load();

		$this->assertEquals($expected_settings, $this->qae->settings);
	}

	function providerTestTheContent() {
		return array(
			array('', null),
			array('<a href="test"></a>', null),
			array('<a href="mp3"></a>', null),
			array('<a href=".mp3"></a>', null),
			array('<a href="test.mp3"></a>', array(0 => '<a href="test.mp3"></a>', 1 => 'test.mp3', 'url' => 'test.mp3')),
			array('<a rel="test" href="test.mp3"></a>', array(0 => '<a rel="test" href="test.mp3"></a>', 1 => 'test.mp3', 'url' => 'test.mp3')),
		);
	}

	/**
	 * @dataProvider providerTestTheContent
	 */
	function testTheContent($content, $expected_call) {
		$qae = $this->getMock('QuickAudioEmbed', array('_the_content_callback'));

		if (is_null($expected_call)) {
			$qae->expects($this->never())->method('_the_content_callback');
		} else {
			$qae->expects($this->once())->method('_the_content_callback')->with($expected_call);
		}

		$qae->the_content($content);
	}
}
