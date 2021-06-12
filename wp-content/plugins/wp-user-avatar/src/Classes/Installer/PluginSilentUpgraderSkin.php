<?php

namespace ProfilePress\Core\Classes\Installer;

/** \WP_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

/**
 * Class PluginSilentUpgraderSkin.
 *
 * @internal Please do not use this class outside of core ProfilePress development. May be removed at any time.
 */
class PluginSilentUpgraderSkin extends \WP_Upgrader_Skin {

	/**
	 * Empty out the header of its HTML content and only check to see if it has
	 * been performed or not.
	 */
	public function header() {
	}

	/**
	 * Empty out the footer of its HTML contents.
	 */
	public function footer() {
	}

	/**
	 * Instead of outputting HTML for errors, just return them.
	 * Ajax request will just ignore it.
	 *
	 * @param array $errors Array of errors with the install process.
	 *
	 * @return array
	 */
	public function error( $errors ) {
		return $errors;
	}

	/**
	 * Empty out JavaScript output that calls function to decrement the update counts.
	 *
	 * @param string $type Type of update count to decrement.
	 */
	public function decrement_update_count( $type ) {
	}
}
