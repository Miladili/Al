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
		return $template;
	}

	public static function body_class( $classes ) {
		if ( is_singular( PSS_PROJECT_CPT ) ) $classes[] = 'pss-project-single';
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
