<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

class Render {
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function template_include( $template ) {
		if ( is_admin() || wp_doing_ajax() ) {
			return $template;
		}
		if ( is_singular( PSS_PROJECT_CPT ) ) {
			$file = PSS_PATH . 'templates/single-pss_project.php';
			if ( file_exists( $file ) ) return $file;
		}
		if ( is_post_type_archive( PSS_PROJECT_CPT ) || is_tax( array( 'pss_project_category', 'pss_project_style', 'pss_project_location', 'pss_project_type' ) ) ) {
			$file = PSS_PATH . 'templates/archive-pss_project.php';
			if ( file_exists( $file ) ) return $file;
		}
		return $template;
	}

	public static function body_class( $classes ) {
		if ( is_singular( PSS_PROJECT_CPT ) ) $classes[] = 'pss-project-single';
		if ( is_post_type_archive( PSS_PROJECT_CPT ) || is_tax( array( 'pss_project_category', 'pss_project_style', 'pss_project_location', 'pss_project_type' ) ) ) {
			$classes[] = 'pss-project-archive';
		}
		return $classes;
	}

	public static function assets() {
		\PSS\Elementor::register_assets();
		if ( ! is_singular( PSS_PROJECT_CPT ) && ! is_post_type_archive( PSS_PROJECT_CPT ) ) {
			return;
		}
		wp_enqueue_style( 'pss-frontend' );
		wp_enqueue_script( 'pss-frontend' );
	}

	public static function render_single() {
		try {
			self::render_single_inner();
		} catch ( \Throwable $e ) {
			error_log( '[PSS] Single project render: ' . $e->getMessage() );
			echo '<div class="pss-single-shell"><p>This project layout could not be rendered.</p></div>';
		}
	}

	private static function render_single_inner() {
		$project_id = get_the_ID();
		if ( ! $project_id || PSS_PROJECT_CPT !== get_post_type( $project_id ) ) {
			return;
		}
		$layout_id = get_matching_layout( $project_id );
		$library_id = $layout_id ? \PSS\Layouts::get_elementor_template_id( $layout_id ) : 0;
		?>
		<div class="pss-single-shell">
			<?php
			if ( $layout_id && $library_id && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
				global $post;
				$original_post = $post;
				$project_post = get_post( $project_id );
				$content = with_project_context( $project_id, function() use ( $layout_id, $library_id, $project_post, &$post ) {
					if ( $project_post ) {
						$post = $project_post;
						setup_postdata( $post );
					}
					return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $library_id, true );
				} );
				wp_reset_postdata();
				$post = $original_post;
				if ( $content ) {
					echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor returns rendered markup.
				} else {
					self::fallback( $project_id );
				}
			} else {
				self::fallback( $project_id );
			}
			?>
		</div>
		<?php
	}

	public static function render_archive() {
		try {
			self::render_archive_inner();
		} catch ( \Throwable $e ) {
			error_log( '[PSS] Archive render: ' . $e->getMessage() );
			echo '<div class="pss-archive-shell"><p>Projects could not be listed.</p></div>';
		}
	}

	private static function render_archive_inner() {
		$layout_id  = absint( get_option( 'pss_archive_layout', 0 ) );
		$library_id = $layout_id ? Layouts::get_elementor_template_id( $layout_id ) : 0;
		echo '<div class="pss-archive-shell">';
		if ( $layout_id && $library_id && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			$content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $library_id, true );
			if ( $content ) {
				echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';
				return;
			}
		}
		$term  = get_queried_object();
		$title = ( $term && isset( $term->name ) ) ? $term->name : __( 'Projects', 'project-showcase-studio' );
		echo '<header class="pss-archive-fallback"><h1>' . esc_html( $title ) . '</h1></header>';
		$posts = Ajax::query( array( 'limit' => 12 ) );
		echo '<div class="pss-showcase pss-showcase--grid"><div class="pss-showcase__grid">' . RenderCards::cards( $posts, array( 'preset' => 'modern', 'animation' => 'reveal', 'show_title' => true, 'show_image' => true ) ) . '</div></div>';
		echo '</div>';
	}

	public static function fallback( $project_id ) {
		$title = get_the_title( $project_id );
		$image = get_the_post_thumbnail_url( $project_id, 'full' );
		$gallery = get_gallery_ids( $project_id );
		?>
		<section class="pss-fallback">
			<?php if ( $image ) : ?><div class="pss-fallback__hero"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>"><div class="pss-fallback__hero-copy"><span>Project</span><h1><?php echo esc_html( $title ); ?></h1></div></div><?php endif; ?>
			<div class="pss-fallback__content">
				<?php echo apply_filters( 'the_content', get_post_field( 'post_content', $project_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( $gallery ) : ?><div class="pss-fallback__gallery"><?php foreach ( $gallery as $id ) : $url = wp_get_attachment_image_url( $id, 'large' ); if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><img src="<?php echo esc_url( $url ); ?>" alt=""></a><?php endif; endforeach; ?></div><?php endif; ?>
			</div>
		</section>
		<?php
	}
}
