<?php
/*
Plugin Name: Quick Audio Embed
Plugin URI: http://www.coswellproductions.com/wordpress/wordpress-plugins/
Description: Turn any MP3 link on a page into an MP3 player, using Google Reader's SWF player.
Version: 0.1
Author: John Bintz
Author URI: http://www.coswellproductions.com/wordpress/

Copyright 2009 John Bintz  (email : john@coswellproductions.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
class QuickAudioEmbed {
	// @codeCoverageIgnoreStart
	function init() {
		$qae = new QuickAudioEmbed();
		$qae->load();
		add_filter('the_content', array(&$qae, 'the_content'));
		add_action('admin_menu', array(&$qae, 'admin_menu'));

		if (isset($_REQUEST['qae'])) {
			if (is_array($_REQUEST['qae'])) {
				if (isset($_REQUEST['qae']['_nonce'])) {
					if (wp_verify_nonce($_REQUEST['qae']['_nonce'], 'quick-audio-embed')) {
						$qae->save($_REQUEST['qae']);
					}
				}
			}
		}
	}

	function plugins_loaded() {
		if (version_compare(PHP_VERSION, '5.0.0') === 1) {
			add_action('init', array('QuickAudioEmbed', 'init'));
		} else {
			add_action('admin_notices', array('QuickAudioEmbed', 'admin_notices'));
		}
	}

	function admin_notices() {
		deactivate_plugins(plugin_basename(__FILE__)); ?>
		<div class="updated fade"><p><?php _e('You need to be running at least PHP 5 to use Quick Audio Embed. Plugin <strong>not</strong> activated.') ?></p></div>
	<?php }

	function __construct() {
		$this->settings = array(
			'dimensions' => '300x50'
		);
	}
	// @codeCoverageIgnoreEnd

	function load() {
		$options = get_option('quick-audio-embed-settings');
		if (is_array($options)) {
			$this->settings = array_merge($this->settings, $options);
		}
	}

	function save($info) {
		foreach ($info as $key => $value) {
			if (method_exists($this, "_save_${key}")) {
				$result = $this->{"_save_${key}"}($value);
				if (is_null($result)) {
					unset($info[$key]);
				} else {
					$info[$key] = $result;
				}
			}
		}

		$this->settings = $info;
		update_option('quick-audio-embed-settings', $info);
	}

	function _save_dimensions($value) {
		if (is_string($value)) {
			if (count($result = explode('x', $value)) === 2) {
				$new_result = array();
				foreach ($result as $v) {
					if (is_numeric($v)) {
						$new_result[] = $v;
					} else {
						return null;
					}
				}
				return implode('x', array_map('intval', $new_result));
			}
		}
		return null;
	}

	// @codeCoverageIgnoreStart
	function admin_menu() {
		add_settings_section('quick-audio-embed', __('Quick Audio Embed', 'quick-audio-embed'), array(&$this, 'media_settings'), 'media');

		add_settings_field('qae-dimensions', __('Dimensions', 'quick-audio-embed'), array(&$this, 'qae_dimensions'), 'media', 'quick-audio-embed');
	}

	function media_settings() { ?>
		<p><em><strong>Quick Audio Embed</strong> takes any linked-to MP3 file and wraps it in Google Reader's Audio Player.</em></p>
		<input type="hidden" name="qae[_nonce]" value="<?php echo esc_attr(wp_create_nonce('quick-audio-embed')) ?>" />
	<?php }

	function qae_dimensions() { ?>
		<input type="text" name="qae[dimensions]" value="<?php echo esc_attr($this->settings['dimensions']) ?>" />
		<p>
			<em>The dimensions of the player as <strong>width</strong>x<strong>height</strong>: <code>300x50</code></em>
		</p>
	<?php }
	// @codeCoverageIgnoreEnd

	function the_content($content = '') {
		return preg_replace_callback('#<a[^\>]+href="(?P<url>[^\"]+\.mp3)"[^\>]*>.*</a>#mis', array(&$this, '_the_content_callback'), $content);
	}

	function _the_content_callback($matches) {
		$dimensions = explode('x', $this->settings['dimensions']);

		if (preg_match('#rel="(?P<rel>[^\"]+)"#', $matches[0], $rel_match) > 0) {
			foreach (explode(',', $rel_match['rel']) as $part) {
				$values = explode('=', $part);
				$key = array_shift($values);
				$values = implode('=', $values);
				switch ($key) {
					case 'noembed':
				}
			}
		}

		return sprintf('
		  <embed type="application/x-shockwave-flash"
		         src="http://www.google.com/reader/ui/3247397568-audio-player.swf?audioUrl=%s&amp;play=true"
		         width="%d"
		         height="%d"
		         allowscriptaccess="never"
		         quality="best"
		         bgcolor="#ffffff"
		         wmode="window"
		         flashvars="playerMode=embedded" />
		', esc_url($matches['url']), esc_attr($dimensions[0]), esc_attr($dimensions[1]));
	}
}

add_action('plugins_loaded', array('QuickAudioEmbed', 'plugins_loaded'));
