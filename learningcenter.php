<?php
/**
 * Plugin Name: LearningCenter
 * Plugin URI: https://megastaradvisors.com/
 * Description: LMS toolkit.
 * Version: 1.0.0
 * Author: Brandon Nourse
 * Author URI: https://megastaradvisors.com
 * Text Domain: learningcenter
 * Domain Path: /i18n/languages/
 * Requires at least: 5.2
 * Requires PHP: 7.0
 *
 * @package learningcenter
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'LC_PLUGIN_FILE' ) ) {
	define( 'LC_PLUGIN_FILE', __FILE__ );
}

// Include the main LearningCenter class.
if ( ! class_exists( 'LearningCenter', false ) ) {
	include_once dirname( LC_PLUGIN_FILE ) . '/includes/class-learningcenter.php';
}

/**
 * Returns the main instance of LC.
 *
 * @since  1.0
 * @return LearningCenter
 */
function LC() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return LearningCenter::instance();
}

// Global for backwards compatibility.
$GLOBALS['learningcenter'] = LC();