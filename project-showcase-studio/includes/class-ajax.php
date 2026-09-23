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
		$settings = is_array( $settings ) ? $settings : array();
		$limit    = isset( $settings['limit'] ) ? max( 1, min( 100, absint( $settings['limit'] ) ) ) : 12;
		$args     = array(
			'post_type'      => PSS_PROJECT_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			's'              => isset( $settings['search'] ) ? sanitize_text_field( $settings['search'] ) : '',
			'paged'          => isset( $settings['page'] ) ? max( 1, absint( $settings['page'] ) ) : 1,
			'orderby'        => isset( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'date',
			'order'          => isset( $settings['order'] ) && 'ASC' === strtoupper( $settings['order'] ) ? 'ASC' : 'DESC',
		);
		$query_type = sanitize_key( $settings['query_type'] ?? 'latest' );
		if ( 'featured' === $query_type ) {
			$args['meta_query'][] = array( 'key' => '_pss_featured', 'value' => '1' );
		}
		if ( ! empty( $settings['ids'] ) ) {
			$ids = is_array( $settings['ids'] ) ? $settings['ids'] : explode( ',', (string) $settings['ids'] );
			$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
			if ( $ids ) {
				$args['post__in'] = $ids;
				$args['orderby']  = 'post__in';
			}
		}
		if ( 'related' === $query_type ) {
			$related_id = absint( $settings['related_id'] ?? get_project_id() );
			if ( $related_id ) {
				$args['post__not_in'] = array( $related_id );
				$taxq                 = array( 'relation' => 'OR' );
				foreach ( array( 'pss_project_category', 'pss_project_style', 'pss_project_type' ) as $tax ) {
					$terms = wp_get_post_terms( $related_id, $tax, array( 'fields' => 'ids' ) );
					if ( ! is_wp_error( $terms ) && $terms ) {
						$taxq[] = array( 'taxonomy' => $tax, 'field' => 'term_id', 'terms' => $terms );
					}
				}
				if ( count( $taxq ) > 1 ) {
					$args['tax_query'] = $taxq;
				}
			}
		}
		$tax_query = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
		$map       = array( 'category' => 'pss_project_category', 'style' => 'pss_project_style', 'location' => 'pss_project_location', 'type' => 'pss_project_type' );
		$extra     = array();
		foreach ( $map as $key => $taxonomy ) {
			if ( ! empty( $settings[ $key ] ) ) {
				$extra[] = array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => absint( $settings[ $key ] ) );
			}
		}
		if ( $extra ) {
			$extra['relation'] = 'AND';
			$args['tax_query'] = $tax_query ? array( 'relation' => 'AND', $tax_query, $extra ) : $extra;
		}
		$meta = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
		if ( ! empty( $settings['year'] ) ) {
			$meta[] = array( 'key' => '_pss_year', 'value' => absint( $settings['year'] ), 'compare' => '=' );
		}
		if ( $meta ) {
			$args['meta_query'] = $meta;
		}
		return get_posts( $args );
	}
}

class RenderCards {
	private static function composition( $preset ) {
		$map = array(
			'editorial' => 'split', 'luxury' => 'caption-below', 'cinematic' => 'fullscreen',
			'architectural' => 'index-split', 'magazine' => 'caption-side', 'bento' => 'bento',
			'asymmetric' => 'split-reverse', 'fullimage' => 'fullscreen', 'fullscreen' => 'fullscreen',
			'floating' => 'float-card', 'stacked' => 'stack', 'overlapping' => 'overlap',
			'split' => 'split', 'dossier' => 'dossier', 'minimal' => 'caption-below',
			'interactive' => 'hover-reveal', 'perspective' => 'perspective', '3d' => 'perspective',
			'overlay' => 'fullscreen', 'classic' => 'caption-below', 'hover_reveal' => 'hover-reveal',
			'flip' => 'flip', 'follow' => 'follow', 'expanding' => 'expanding', 'magnetic' => 'magnetic',
			'caption' => 'caption-below', 'story' => 'story', 'modern' => 'stack',
		);
		$preset = sanitize_key( $preset );
		return isset( $map[ $preset ] ) ? $map[ $preset ] : 'stack';
	}

	private static function slot( $settings, $key, $auto ) {
		$value = sanitize_key( $settings[ $key ] ?? 'auto' );
		$ok    = array( 'overlay', 'top', 'bottom', 'below', 'floating', 'hidden', 'none', 'auto', 'hover' );
		if ( ! in_array( $value, $ok, true ) || 'auto' === $value ) {
			return $auto;
		}
		return 'none' === $value ? 'hidden' : $value;
	}

	public static function cards( $projects, $settings = array() ) {
		$html           = '';
		$preset         = sanitize_key( $settings['preset'] ?? 'modern' );
		$animation      = sanitize_key( $settings['animation'] ?? 'reveal' );
		$card_data_mode = sanitize_key( $settings['card_data_mode'] ?? 'project' );
		$composition    = self::composition( $preset );
		$title_place    = self::slot( $settings, 'title_placement', in_array( $composition, array( 'fullscreen', 'hover-reveal', 'float-card', 'flip' ), true ) ? 'overlay' : 'below' );
		$meta_place     = self::slot( $settings, 'meta_placement', in_array( $composition, array( 'fullscreen', 'hover-reveal' ), true ) ? 'overlay' : 'below' );
		$index_place    = self::slot( $settings, 'index_placement', 'overlay' );
		$cta_place      = self::slot( $settings, 'cta_placement', 'hidden' );
		$sub_place      = self::slot( $settings, 'subtitle_placement', 'hidden' );
		$show_title     = ! isset( $settings['show_title'] ) || ! empty( $settings['show_title'] );
		$show_image     = ! isset( $settings['show_image'] ) || ! empty( $settings['show_image'] );
		$show_subtitle  = ! empty( $settings['show_subtitle'] );
		$show_badge     = ! empty( $settings['show_badge'] );
		if ( ! $show_title ) { $title_place = 'hidden'; }
		$cta_label = sanitize_text_field( $settings['cta_label'] ?? 'View project' );

		$manual_fields = array_filter( array_map( function( $key ) {
			$key = trim( (string) $key );
			return 0 === strpos( $key, 'core:' ) ? 'core:' . sanitize_key( substr( $key, 5 ) ) : sanitize_key( $key );
		}, preg_split( '/[,\n]+/', (string) ( $settings['manual_card_fields'] ?? '' ) ) ) );

		foreach ( $projects as $index => $project ) {
			$id    = is_object( $project ) ? absint( $project->ID ) : absint( $project );
			$image = $id ? get_the_post_thumbnail_url( $id, 'large' ) : '';
			if ( ! $image && $id ) {
				$gallery = get_gallery_ids( $id );
				$image   = $gallery ? wp_get_attachment_image_url( $gallery[0], 'large' ) : '';
			}
			$url        = $id ? get_permalink( $id ) : '#';
			$title      = $id ? get_the_title( $id ) : '';
			$subtitle   = $id ? (string) get_meta( $id, '_pss_subtitle' ) : '';
			$field_keys = 'manual' === $card_data_mode ? $manual_fields : ( 'project' === $card_data_mode ? get_project_card_fields( $id ) : array( 'core:style', 'core:location', 'core:year', 'core:area' ) );
			$card_values = array();
			foreach ( $field_keys as $field_key ) {
				$data = get_project_card_field( $id, $field_key );
				$text = field_value_text( $data['value'] ?? '' );
				if ( '' === $text || is_placeholder_text( $text ) ) { continue; }
				$card_values[] = array( 'label' => $data['label'] ?? '', 'value' => $text, 'key' => $field_key );
			}
			$index_no = sprintf( '%02d', ( (int) $index + 1 ) );
			$classes  = 'pss-card pss-card--' . esc_attr( $preset ) . ' pss-card--comp-' . esc_attr( $composition ) . ' pss-card--anim-' . esc_attr( $animation );
			$classes .= ' pss-card--title-' . esc_attr( $title_place ) . ' pss-card--meta-' . esc_attr( $meta_place );

			$title_html = ( 'hidden' === $title_place ) ? '' : '<h3 class="pss-card__title">' . esc_html( $title ) . '</h3>';
			$sub_html   = ( $show_subtitle && $subtitle && 'hidden' !== $sub_place ) ? '<p class="pss-card__subtitle">' . esc_html( $subtitle ) . '</p>' : '';
			$meta_html  = '';
			if ( 'hidden' !== $meta_place && $card_values ) {
				$meta_html = '<div class="pss-card__meta-list">';
				foreach ( array_slice( $card_values, 0, 4 ) as $field ) {
					$meta_html .= '<span><small>' . esc_html( $field['label'] ) . '</small><b>' . esc_html( $field['value'] ) . '</b></span>';
				}
				$meta_html .= '</div>';
			}
			$index_html = ( 'hidden' === $index_place ) ? '' : '<span class="pss-card__index">' . esc_html( $index_no ) . '</span>';
			$cta_html   = ( 'hidden' === $cta_place ) ? '' : '<span class="pss-card__cta">' . esc_html( $cta_label ) . '</span>';
			$badge_html = $show_badge ? '<span class="pss-card__badge">' . esc_html( $index_no ) . '</span>' : '';

			$overlay = $below = $floating = $top = $hover = '';
			foreach ( array( array( $title_place, $title_html ), array( $meta_place, $meta_html ), array( $cta_place, $cta_html ), array( $sub_place, $sub_html ) ) as $pair ) {
				list( $place, $chunk ) = $pair;
				if ( ! $chunk ) { continue; }
				if ( in_array( $place, array( 'overlay', 'bottom' ), true ) ) { $overlay .= $chunk; }
				elseif ( 'top' === $place ) { $top .= $chunk; }
				elseif ( 'below' === $place ) { $below .= $chunk; }
				elseif ( 'floating' === $place ) { $floating .= $chunk; }
				elseif ( 'hover' === $place ) { $hover .= $chunk; }
			}

			$media = '<div class="pss-card__media">';
			if ( $show_image && $image ) {
				$media .= '<img loading="lazy" src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '">';
			}
			$media .= '<span class="pss-card__veil"></span>';
			if ( 'overlay' === $index_place ) { $media .= $index_html; }
			$media .= $badge_html . '<span class="pss-card__arrow" aria-hidden="true">↗</span>';
			if ( $top ) { $media .= '<div class="pss-card__overlay pss-card__overlay--top">' . $top . '</div>'; }
			if ( $overlay ) { $media .= '<div class="pss-card__overlay pss-card__overlay--bottom">' . $overlay . '</div>'; }
			if ( $hover ) { $media .= '<div class="pss-card__overlay pss-card__overlay--hover">' . $hover . '</div>'; }
			$media .= '</div>';

			$body = '';
			if ( $below || ( 'below' === $index_place && $index_html ) ) {
				$body  = '<div class="pss-card__body">';
				if ( 'below' === $index_place ) { $body .= $index_html; }
				$body .= $below . '</div>';
			}
			$float = $floating ? '<div class="pss-card__float">' . $floating . '</div>' : '';

			$html .= '<article class="' . $classes . '" data-pss-motion="' . esc_attr( $animation ) . '" data-comp="' . esc_attr( $composition ) . '">';
			$html .= '<a class="pss-card__link" href="' . esc_url( $url ) . '">';
			if ( 'flip' === $composition ) {
				$html .= '<div class="pss-card__flip"><div class="pss-card__flip-front">' . $media . '</div><div class="pss-card__flip-back">' . $title_html . $sub_html . $meta_html . $cta_html . '</div></div>';
			} elseif ( 'expanding' === $composition ) {
				$html .= $media . '<div class="pss-card__expand">' . $title_html . $sub_html . $meta_html . $cta_html . '</div>';
			} elseif ( 'follow' === $composition ) {
				$html .= '<div class="pss-card__follow" data-pss-follow="1">' . $media . '</div>' . $body . $float;
			} elseif ( 'split' === $composition || 'split-reverse' === $composition || 'index-split' === $composition || 'caption-side' === $composition ) {
				if ( 'index-split' === $composition ) {
					$html .= '<span class="pss-card__giant-index">' . esc_html( $index_no ) . '</span>';
				}
				$html .= $media . $body . $float;
			} elseif ( 'dossier' === $composition ) {
				$html .= '<div class="pss-card__dossier-head">' . ( $index_html ?: '<span class="pss-card__index">' . esc_html( $index_no ) . '</span>' ) . $body . '</div>' . $media . $float;
			} elseif ( 'story' === $composition ) {
				$html .= '<div class="pss-card__story">' . $media . '<div class="pss-card__story-copy">' . $title_html . $sub_html . $meta_html . $cta_html . '</div></div>';
			} else {
				$html .= $media . $body . $float;
			}
			$html .= '</a></article>';
		}
		return $html ? $html : '<div class="pss-empty">No projects found.</div>';
	}
}
