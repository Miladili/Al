<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

/**
 * Elementor integration.
 *
 * Elementor is the visual layer only. Project posts are never Elementor documents.
 * Single Layouts open a linked `elementor_library` template in the real editor.
 */
class Elementor {
	private static $booted                = false;
	private static $widget_errors         = array();
	private static $dynamic_tag_errors    = array();
	private static $widgets_registered    = false;
	private static $dynamic_tags_registered = false;
	private static $assets_registered     = false;

	public static function init() {
		// Attach to official Elementor hooks immediately so registration is not
		// missed if `elementor/init` already fired or fires in an unexpected order.
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ), 5 );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ), 10 );
		add_action( 'elementor/widgets/widgets_registered', array( __CLASS__, 'register_widgets' ), 10 );
		add_action( 'elementor/dynamic_tags/register', array( __CLASS__, 'register_dynamic_tags' ), 10 );
		add_action( 'elementor/init', array( __CLASS__, 'boot' ), 20 );

		add_action( 'init', array( __CLASS__, 'exclude_post_types' ), 30 );
		add_action( 'admin_init', array( __CLASS__, 'block_unsupported_elementor_editor' ), 1 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ), 5 );
		add_action( 'elementor/frontend/after_register_styles', array( __CLASS__, 'register_assets' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'enqueue_canvas_styles' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'enqueue_canvas_styles' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( __CLASS__, 'enqueue_canvas_scripts' ) );
		add_action( 'elementor/frontend/after_enqueue_scripts', array( __CLASS__, 'enqueue_canvas_scripts' ) );

		add_filter( 'elementor/utils/is_post_support', array( __CLASS__, 'filter_post_support' ), 999, 3 );
		add_filter( 'elementor/document/urls/preview', array( __CLASS__, 'preview_url' ), 10, 2 );
		add_filter( 'elementor/document/urls/wp_preview', array( __CLASS__, 'preview_url' ), 10, 2 );

		add_action( 'admin_notices', array( __CLASS__, 'minimum_version_notice' ) );
	}

	public static function boot() {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;
		self::register_assets();
	}

	/** @deprecated Compatibility shim. */
	public static function bootstrap() {
		self::boot();
	}

	public static function allow_layout_post_type( $supported, $post_type ) {
		return $supported;
	}

	public static function exclude_post_types() {
		if ( function_exists( 'remove_post_type_support' ) ) {
			remove_post_type_support( PSS_PROJECT_CPT, 'elementor' );
			remove_post_type_support( PSS_LAYOUT_CPT, 'elementor' );
		}
	}

	public static function filter_post_support( $supported, $post_id = 0, $post_type = '' ) {
		if ( in_array( $post_type, array( PSS_PROJECT_CPT, PSS_LAYOUT_CPT ), true ) ) {
			return false;
		}
		return $supported;
	}

	/**
	 * Projects and layout manager records must never open the Elementor canvas.
	 */
	public static function block_unsupported_elementor_editor() {
		if ( ! is_admin() ) {
			return;
		}
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'elementor' !== $action ) {
			return;
		}
		$post_id = absint( $_GET['post'] ?? 0 );
		if ( ! $post_id ) {
			return;
		}
		$type = get_post_type( $post_id );
		if ( PSS_PROJECT_CPT === $type || PSS_LAYOUT_CPT === $type ) {
			wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
			exit;
		}
	}

	public static function register_assets() {
		if ( self::$assets_registered && wp_style_is( 'pss-frontend', 'registered' ) ) {
			return;
		}
		self::$assets_registered = true;
		wp_register_style( 'pss-frontend', PSS_URL . 'assets/css/frontend.css', array(), PSS_VERSION );
		wp_register_style( 'pss-elementor', PSS_URL . 'assets/css/elementor.css', array( 'pss-frontend' ), PSS_VERSION );
		wp_register_script( 'pss-frontend', PSS_URL . 'assets/js/frontend.js', array(), PSS_VERSION, true );
		if ( ! wp_scripts()->get_data( 'pss-frontend', 'data' ) ) {
			wp_localize_script(
				'pss-frontend',
				'PSSFront',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'pss_ajax' ),
				)
			);
		}
	}

	public static function enqueue_canvas_styles() {
		self::register_assets();
		wp_enqueue_style( 'pss-frontend' );
		wp_enqueue_style( 'pss-elementor' );
	}

	public static function enqueue_canvas_scripts() {
		self::register_assets();
		wp_enqueue_script( 'pss-frontend' );
	}

	public static function enqueue_editor_assets() {
		// Intentionally empty. Widget assets load through get_style_depends().
	}

	public static function register_legacy_widgets( $widgets_manager ) {
		self::register_widgets( $widgets_manager );
	}

	public static function register_dynamic_tags_legacy( $dynamic_tags_manager ) {
		self::register_dynamic_tags( $dynamic_tags_manager );
	}

	public static function register_styles() {
		self::register_assets();
	}

	public static function register_scripts() {
		self::register_assets();
	}

	public static function minimum_version_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( (string) $screen->id, 'elementor' ) ) {
			return;
		}
		if ( version_compare( ELEMENTOR_VERSION, '3.19.0', '<' ) ) {
			echo '<div class="notice notice-warning"><p><strong>Project Showcase Studio:</strong> Elementor 3.19 or newer is required for Project Showcase widgets.</p></div>';
		}
	}

	public static function register_category( $elements_manager ) {
		if ( ! is_object( $elements_manager ) || ! method_exists( $elements_manager, 'add_category' ) ) {
			return;
		}
		$elements_manager->add_category(
			'pss-projects',
			array(
				'title' => __( 'Project Showcase', 'project-showcase-studio' ),
				'icon'  => 'eicon-portfolio',
			)
		);
	}

	public static function register_widgets( $widgets_manager ) {
		if ( self::$widgets_registered || ! is_object( $widgets_manager ) ) {
			return;
		}
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->elements_manager ) ) {
			$elements_manager = \Elementor\Plugin::$instance->elements_manager;
			if ( method_exists( $elements_manager, 'add_category' ) ) {
				$categories = method_exists( $elements_manager, 'get_categories' ) ? $elements_manager->get_categories() : array();
				if ( ! isset( $categories['pss-projects'] ) ) {
					$elements_manager->add_category(
						'pss-projects',
						array(
							'title' => __( 'Project Showcase', 'project-showcase-studio' ),
							'icon'  => 'eicon-portfolio',
						)
					);
				}
			}
		}

		$register_method = method_exists( $widgets_manager, 'register' )
			? 'register'
			: ( method_exists( $widgets_manager, 'register_widget_type' ) ? 'register_widget_type' : '' );
		if ( '' === $register_method ) {
			return;
		}

		require_once PSS_PATH . 'elementor/widgets/class-base.php';

		$files = array(
			'project-title'          => 'Project_Title',
			'project-image'          => 'Project_Image',
			'project-meta'           => 'Project_Meta',
			'project-description'    => 'Project_Description',
			'project-floor-plan'     => 'Project_Floor_Plan',
			'project-gallery'        => 'Project_Gallery',
			'project-custom-fields'  => 'Project_Custom_Fields',
			'project-products'       => 'Project_Products',
			'related-projects'       => 'Related_Projects',
			'project-navigation'     => 'Project_Navigation',
			'project-before-after'   => 'Project_Before_After',
			'project-video'          => 'Project_Video',
			'project-showcase'       => 'Project_Showcase',
			'project-field'          => 'Project_Field',
			'project-hero'           => 'Project_Hero',
			'project-stats'          => 'Project_Stats',
			'project-cta'            => 'Project_CTA',
			'project-breadcrumbs'    => 'Project_Breadcrumbs',
			'project-share'          => 'Project_Share',
			'project-services'       => 'Project_Services',
			'project-materials'      => 'Project_Materials',
			'project-location'       => 'Project_Location',
			'project-inquiry'        => 'Project_Inquiry',
			'project-specifications' => 'Project_Specifications',
			'project-tags'           => 'Project_Tags',
			'project-features'       => 'Project_Features',
		);

		$successful = 0;
		foreach ( $files as $file => $class ) {
			try {
				$path = PSS_PATH . 'elementor/widgets/class-' . $file . '.php';
				if ( ! file_exists( $path ) ) {
					throw new \RuntimeException( 'Missing widget file: ' . $file );
				}
				require_once $path;
				$full = __NAMESPACE__ . '\\Elementor\\Widgets\\' . $class;
				if ( ! class_exists( $full ) ) {
					throw new \RuntimeException( 'Missing widget class: ' . $full );
				}
				$widgets_manager->{$register_method}( new $full() );
				$successful++;
			} catch ( \Throwable $e ) {
				self::$widget_errors[ $file ] = $e->getMessage();
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( '[PSS] Widget registration failed: ' . $file . ' - ' . $e->getMessage() );
				}
			}
		}

		self::$widgets_registered = $successful > 0;
	}

	public static function register_dynamic_tags( $dynamic_tags_manager ) {
		if ( self::$dynamic_tags_registered ) {
			return;
		}
		if ( ! is_object( $dynamic_tags_manager ) || ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
			return;
		}
		$register_method = method_exists( $dynamic_tags_manager, 'register' )
			? 'register'
			: ( method_exists( $dynamic_tags_manager, 'register_tag' ) ? 'register_tag' : '' );
		if ( '' === $register_method ) {
			return;
		}

		try {
			require_once PSS_PATH . 'elementor/dynamic-tags/class-project-dynamic-tags.php';
			if ( method_exists( $dynamic_tags_manager, 'register_group' ) ) {
				$dynamic_tags_manager->register_group(
					'pss-project',
					array( 'title' => esc_html__( 'Project Showcase', 'project-showcase-studio' ) )
				);
			}
			$tags = array(
				'Project_Title',
				'Project_Subtitle',
				'Project_URL',
				'Project_Image',
				'Project_Content',
				'Project_Meta',
				'Project_Field',
			);
			foreach ( $tags as $tag_class ) {
				$full = __NAMESPACE__ . '\\Elementor\\DynamicTags\\' . $tag_class;
				if ( class_exists( $full ) ) {
					$dynamic_tags_manager->{$register_method}( new $full() );
				}
			}
			self::$dynamic_tags_registered = true;
		} catch ( \Throwable $e ) {
			self::$dynamic_tag_errors[] = $e->getMessage();
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[PSS] Dynamic tag registration failed: ' . $e->getMessage() );
			}
		}
	}

	public static function preview_url( $url, $document ) {
		if ( ! is_object( $document ) ) {
			return $url;
		}

		$template_id = 0;
		if ( method_exists( $document, 'get_main_id' ) ) {
			$template_id = absint( $document->get_main_id() );
		}
		if ( ! $template_id && method_exists( $document, 'get_id' ) ) {
			$template_id = absint( $document->get_id() );
		}
		if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) ) {
			return $url;
		}

		$layout_id = absint( get_post_meta( $template_id, '_pss_manager_layout_id', true ) );
		if ( ! $layout_id || PSS_LAYOUT_CPT !== get_post_type( $layout_id ) ) {
			return $url;
		}
		$preview = absint( get_post_meta( $layout_id, '_pss_preview_project', true ) );
		if ( ! $preview || PSS_PROJECT_CPT !== get_post_type( $preview ) ) {
			return $url;
		}

		$project_url = get_permalink( $preview );
		if ( ! $project_url ) {
			return $url;
		}

		return add_query_arg(
			array(
				'pss_preview_project' => $preview,
				'pss_preview_layout'  => $layout_id,
				'elementor-preview'   => $template_id,
			),
			$project_url
		);
	}

	public static function seed_layout_content( $document_id, $style = 'modern' ) {
		$document_id = absint( $document_id );
		if ( ! $document_id ) {
			return;
		}
		$post_type = get_post_type( $document_id );
		if ( PSS_LAYOUT_CPT !== $post_type && 'elementor_library' !== $post_type ) {
			return;
		}

		require_once PSS_PATH . 'includes/class-templates.php';
		$elements = 'premium' === $style ? Templates::premium_elements() : Templates::modern_elements();

		update_post_meta( $document_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $document_id, '_elementor_template_type', 'page' );
		update_post_meta( $document_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
		update_post_meta( $document_id, '_elementor_page_settings', array() );
		update_post_meta( $document_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : PSS_VERSION );
	}

	public static function admin_error_notice() {
		if ( ( empty( self::$widget_errors ) && empty( self::$dynamic_tag_errors ) ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( (string) $screen->id, 'elementor' ) ) {
			return;
		}
		foreach ( self::$widget_errors as $slug => $message ) {
			echo '<div class="notice notice-error"><p><strong>Project Showcase Studio:</strong> Widget <code>' . esc_html( $slug ) . '</code> failed to register. ' . esc_html( $message ) . '</p></div>';
		}
		foreach ( self::$dynamic_tag_errors as $message ) {
			echo '<div class="notice notice-error"><p><strong>Project Showcase Studio:</strong> Dynamic tags failed to register. ' . esc_html( $message ) . '</p></div>';
		}
	}
}
