<?php
/**
 * Plugin Name: Project Showcase Studio
 * Description: Independent project management, custom fields, Elementor Single Project Layouts, project showcase cards, and WooCommerce product relations.
 * Version: 2.8.0
 * Author: OpenAI
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Text Domain: project-showcase-studio
 */

defined( 'ABSPATH' ) || exit;

define( 'PSS_VERSION', '2.8.0' );
define( 'PSS_FILE', __FILE__ );
define( 'PSS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PSS_URL', plugin_dir_url( __FILE__ ) );
define( 'PSS_PROJECT_CPT', 'pss_project' );
define( 'PSS_LAYOUT_CPT', 'pss_layout' );

require_once PSS_PATH . 'includes/helpers.php';
require_once PSS_PATH . 'includes/class-projects.php';
require_once PSS_PATH . 'includes/class-fields.php';
require_once PSS_PATH . 'includes/class-layouts.php';
require_once PSS_PATH . 'includes/class-render.php';
require_once PSS_PATH . 'includes/class-ajax.php';
require_once PSS_PATH . 'includes/class-woocommerce.php';
require_once PSS_PATH . 'includes/class-templates.php';
require_once PSS_PATH . 'includes/class-elementor.php';

\PSS\Projects::init();
\PSS\Fields::init();
\PSS\Layouts::init();
\PSS\Render::init();
\PSS\Ajax::init();
\PSS\WooCommerce::init();
\PSS\Elementor::init();
add_action( 'admin_notices', array( '\PSS\Elementor', 'admin_error_notice' ) );

register_activation_hook( __FILE__, array( '\PSS\Projects', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\PSS\Projects', 'deactivate' ) );
