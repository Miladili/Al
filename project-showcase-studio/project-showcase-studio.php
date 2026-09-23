<?php
/**
 * Plugin Name: Project Showcase Studio
 * Description: Independent project management, custom fields, Elementor Single Project Layouts, project showcase cards, and WooCommerce product relations.
 * Version: 3.2.0
 * Author: OpenAI
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Text Domain: project-showcase-studio
 */

defined( 'ABSPATH' ) || exit;

define( 'PSS_VERSION', '3.2.0' );
define( 'PSS_FILE', __FILE__ );
define( 'PSS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PSS_URL', plugin_dir_url( __FILE__ ) );
define( 'PSS_PROJECT_CPT', 'pss_project' );
define( 'PSS_LAYOUT_CPT', 'pss_layout' );

/**
 * Load a PHP file without turning the whole site into a critical error.
 *
 * @param string $relative Path under the plugin directory.
 * @return bool
 */
function pss_require( $relative ) {
	$path = PSS_PATH . ltrim( $relative, '/' );
	if ( ! is_readable( $path ) ) {
		return false;
	}
	try {
		require_once $path;
		return true;
	} catch ( \Throwable $e ) {
		error_log( '[PSS] Failed to load ' . $relative . ': ' . $e->getMessage() );
		return false;
	}
}

pss_require( 'includes/helpers.php' );
pss_require( 'includes/class-projects.php' );
pss_require( 'includes/class-fields.php' );
pss_require( 'includes/class-layouts.php' );
pss_require( 'includes/class-render.php' );
pss_require( 'includes/class-ajax.php' );
pss_require( 'includes/class-woocommerce.php' );
pss_require( 'includes/class-elementor.php' );

foreach ( array( 'Projects', 'Fields', 'Layouts', 'Render', 'Ajax', 'WooCommerce', 'Elementor' ) as $pss_class ) {
	$pss_fqcn = 'PSS\\' . $pss_class;
	try {
		if ( class_exists( $pss_fqcn ) && method_exists( $pss_fqcn, 'init' ) ) {
			$pss_fqcn::init();
		}
	} catch ( \Throwable $e ) {
		error_log( '[PSS] ' . $pss_class . '::init failed: ' . $e->getMessage() );
	}
}

if ( class_exists( '\\PSS\\Elementor' ) ) {
	add_action( 'admin_notices', array( '\\PSS\\Elementor', 'admin_error_notice' ) );
}

/**
 * @return void
 */
function pss_activate() {
	try {
		if ( class_exists( '\\PSS\\Projects' ) ) {
			\PSS\Projects::activate();
		}
	} catch ( \Throwable $e ) {
		error_log( '[PSS] Activation failed: ' . $e->getMessage() );
	}
}

/**
 * @return void
 */
function pss_deactivate() {
	try {
		if ( class_exists( '\\PSS\\Projects' ) ) {
			\PSS\Projects::deactivate();
		}
	} catch ( \Throwable $e ) {
		error_log( '[PSS] Deactivation failed: ' . $e->getMessage() );
	}
}

register_activation_hook( __FILE__, 'pss_activate' );
register_deactivation_hook( __FILE__, 'pss_deactivate' );
