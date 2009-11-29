<?php

require_once('PHPUnit/Framework.php');
require_once('MockPress/mockpress.php');
require_once(dirname(__FILE__) . '/../quick-audio-embed.php');

class QuickAudioEmbedTest extends PHPUnit_Framework_TestCase {
	function setUp() { _reset_wp(); }
}
