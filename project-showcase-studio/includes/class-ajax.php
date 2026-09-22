<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

class Ajax {
	public static function init() {
		add_action( 'wp_ajax_pss_filter_projects', array( __CLASS__, 'filter_projects' ) );
		add_action( 'wp_ajax_nopriv_pss_filter_projects', array( __CLASS__, 'filter_projects' ) );
	}

	public static function filter_projects() {
		check_ajax_referer( 'pss_ajax', 'nonce' );
		$settings = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : array();
		$settings = is_array( $settings ) ? $settings : array();
		$projects = self::query( $settings );
		echo RenderCards::cards( $projects, $settings );
		wp_die();
	}

	public static function query( $settings = array() ) {
		$args = array(
			'post_type' => PSS_PROJECT_CPT,
			'post_status' => 'publish',
			'posts_per_page' => isset( $settings['limit'] ) ? max( 1, min( 100, absint( $settings['limit'] ) ) ) : 12,
			's' => isset( $settings['search'] ) ? sanitize_text_field( $settings['search'] ) : '',
			'paged' => isset( $settings['page'] ) ? max( 1, absint( $settings['page'] ) ) : 1,
			'orderby' => isset( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'date',
			'order' => isset( $settings['order'] ) && 'ASC' === strtoupper( $settings['order'] ) ? 'ASC' : 'DESC',
		);
		$tax_query = array();
		$map = array( 'category' => 'pss_project_category', 'style' => 'pss_project_style', 'location' => 'pss_project_location', 'type' => 'pss_project_type' );
		foreach ( $map as $key => $taxonomy ) {
			if ( ! empty( $settings[ $key ] ) ) $tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => absint( $settings[ $key ] ) );
		}
		if ( ! empty( $settings['year'] ) ) {
			$args['meta_query'] = array( array( 'key' => '_pss_year', 'value' => absint( $settings['year'] ), 'compare' => '=' ) );
		}
		if ( $tax_query ) { $tax_query['relation'] = 'AND'; $args['tax_query'] = $tax_query; }
		return get_posts( $args );
	}
}

class RenderCards {
	private static function overlay_presets() {
		return array( 'modern', 'luxury', 'cinematic', 'overlay', 'dark', 'glass', 'magazine', 'floating', 'architectural', 'monochrome', 'fullimage', 'interactive' );
	}

	private static function meta_placement( $settings ) {
		$placement = sanitize_key( $settings['meta_placement'] ?? 'auto' );
		if ( in_array( $placement, array( 'overlay', 'below', 'none' ), true ) ) {
			return $placement;
		}
		$preset = sanitize_key( $settings['preset'] ?? 'modern' );
		return in_array( $preset, self::overlay_presets(), true ) ? 'overlay' : 'below';
	}

	public static function cards( $projects, $settings = array() ) {
		$html = '';
		$preset = sanitize_key( $settings['preset'] ?? 'modern' );
		$animation = sanitize_key( $settings['animation'] ?? 'reveal' );
		$card_data_mode = sanitize_key( $settings['card_data_mode'] ?? 'project' );
		$placement = self::meta_placement( $settings );
		$manual_fields = array_filter(
			array_map(
				function( $key ) {
					$key = trim( (string) $key );
					return 0 === strpos( $key, 'core:' ) ? 'core:' . sanitize_key( substr( $key, 5 ) ) : sanitize_key( $key );
				},
				preg_split( '/[,\n]+/', (string) ( $settings['manual_card_fields'] ?? '' ) )
			)
		);
		foreach ( $projects as $index => $project ) {
			$id = $project->ID;
			$image = get_the_post_thumbnail_url( $id, 'large' );
			if ( ! $image ) {
				$gallery = get_gallery_ids( $id );
				$image = $gallery ? wp_get_attachment_image_url( $gallery[0], 'large' ) : '';
			}
			$style = get_project_taxonomy_value( $id, 'pss_project_style' );
			if ( is_placeholder_text( $style ) ) {
				$style = '';
			}
			$url = get_permalink( $id );
			$field_keys = 'manual' === $card_data_mode ? $manual_fields : ( 'project' === $card_data_mode ? get_project_card_fields( $id ) : array( 'core:style', 'core:location', 'core:year', 'core:area' ) );
			$card_values = array();
			foreach ( $field_keys as $field_key ) {
				$data = get_project_card_field( $id, $field_key );
				$text = field_value_text( $data['value'] ?? '' );
				if ( '' === $text || is_placeholder_text( $text ) ) {
					continue;
				}
				$card_values[] = array(
					'label' => $data['label'] ?? '',
					'value' => $text,
					'key'   => $field_key,
				);
			}
			$index_no = sprintf( '%02d', ( (int) $index + 1 ) );
			$classes = 'pss-card pss-card--' . esc_attr( $preset ) . ' pss-card--anim-' . esc_attr( $animation ) . ' pss-card--meta-' . esc_attr( $placement );
			$html .= '<article class="' . $classes . '" data-pss-motion="' . esc_attr( $animation ) . '">';
			$html .= '<a class="pss-card__link" href="' . esc_url( $url ) . '">';
			$html .= '<div class="pss-card__media">';
			if ( $image ) {
				$html .= '<img loading="lazy" src="' . esc_url( $image ) . '" alt="' . esc_attr( get_the_title( $id ) ) . '">';
			}
			$html .= '<span class="pss-card__veil"></span><span class="pss-card__index">' . esc_html( $index_no ) . '</span><span class="pss-card__arrow" aria-hidden="true">↗</span>';
			if ( 'overlay' === $placement && $card_values ) {
				$html .= '<span class="pss-card__field-stack">';
				foreach ( array_slice( $card_values, 0, 3 ) as $field ) {
					$html .= '<span class="pss-card__field"><small>' . esc_html( $field['label'] ) . '</small><b>' . esc_html( $field['value'] ) . '</b></span>';
				}
				$html .= '</span>';
			}
			$html .= '</div>';
			$html .= '<div class="pss-card__body">';
			if ( 'overlay' !== $placement && $style ) {
				$html .= '<span class="pss-card__eyebrow">' . esc_html( $style ) . '</span>';
			}
			if ( ! empty( $settings['show_title'] ) || ! isset( $settings['show_title'] ) ) {
				$html .= '<h3>' . esc_html( get_the_title( $id ) ) . '</h3>';
			}
			if ( 'below' === $placement && $card_values ) {
				$html .= '<div class="pss-card__meta-list">';
				foreach ( array_slice( $card_values, 0, 4 ) as $field ) {
					$html .= '<span><small>' . esc_html( $field['label'] ) . '</small><b>' . esc_html( $field['value'] ) . '</b></span>';
				}
				$html .= '</div>';
			}
			$html .= '</div></a></article>';
		}
		return $html ? $html : '<div class="pss-empty">No projects found.</div>';
	}
}
