<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

function esc_attr_json( $value ) {
	return esc_attr( wp_json_encode( $value ) );
}

function get_project_id( $explicit = 0 ) {
	if ( $explicit ) {
		return absint( $explicit );
	}
	if ( ! empty( $_GET['pss_preview_project'] ) ) {
		$preview = absint( $_GET['pss_preview_project'] );
		if ( $preview && PSS_PROJECT_CPT === get_post_type( $preview ) ) {
			return $preview;
		}
	}
	// Elementor editor requests identify the design document via ?post=ID.
	$editor_post_id = ! empty( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( $editor_post_id && 'elementor_library' === get_post_type( $editor_post_id ) ) {
		$manager_layout = absint( get_post_meta( $editor_post_id, '_pss_manager_layout_id', true ) );
		if ( $manager_layout ) {
			$preview = absint( get_post_meta( $manager_layout, '_pss_preview_project', true ) );
			if ( $preview && PSS_PROJECT_CPT === get_post_type( $preview ) ) {
				return $preview;
			}
		}
	}
	$queried_id = get_queried_object_id();
	if ( is_singular( PSS_PROJECT_CPT ) ) {
		return $queried_id;
	}
	if ( $queried_id && 'elementor_library' === get_post_type( $queried_id ) ) {
		$manager_layout = absint( get_post_meta( $queried_id, '_pss_manager_layout_id', true ) );
		if ( $manager_layout ) {
			$preview = absint( get_post_meta( $manager_layout, '_pss_preview_project', true ) );
			if ( $preview && PSS_PROJECT_CPT === get_post_type( $preview ) ) return $preview;
		}
	}
	if ( ! empty( $GLOBALS['pss_current_project_id'] ) ) {
		return absint( $GLOBALS['pss_current_project_id'] );
	}
	return 0;
}

function with_project_context( $project_id, callable $callback ) {
	$previous = $GLOBALS['pss_current_project_id'] ?? 0;
	$GLOBALS['pss_current_project_id'] = absint( $project_id );
	try {
		return call_user_func( $callback );
	} finally {
		$GLOBALS['pss_current_project_id'] = $previous;
	}
}

function get_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( absint( $post_id ), $key, true );
	return '' === $value || null === $value ? $default : $value;
}

function get_gallery_ids( $post_id ) {
	$ids = get_meta( $post_id, '_pss_gallery', array() );
	if ( ! is_array( $ids ) ) {
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $ids ) ) );
	}
	return array_values( array_filter( array_map( 'absint', $ids ) ) );
}

function get_project_taxonomy_value( $post_id, $taxonomy, $single = true ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return $single ? '' : array();
	}
	if ( $single ) {
		return $terms[0]->name;
	}
	return wp_list_pluck( $terms, 'name' );
}

function sanitize_recursive( $value ) {
	if ( is_array( $value ) ) {
		$out = array();
		foreach ( $value as $key => $item ) {
			$out[ sanitize_key( $key ) ] = sanitize_recursive( $item );
		}
		return $out;
	}
	if ( is_bool( $value ) || is_numeric( $value ) ) {
		return $value;
	}
	return wp_kses_post( (string) $value );
}

function get_layout_condition_logic( $layout_id ) {
	$logic = get_post_meta( absint( $layout_id ), '_pss_condition_logic', true );
	return in_array( $logic, array( 'all', 'any' ), true ) ? $logic : 'all';
}

function compare_project_field_condition( $project_id, $field_key, $operator, $expected ) {
	$field_key = sanitize_key( $field_key );
	if ( ! $field_key ) {
		return false;
	}
	$actual = get_field_value( $project_id, $field_key, '' );
	$operator = sanitize_key( $operator ?: 'equals' );
	$expected = is_scalar( $expected ) ? (string) $expected : '';
	if ( is_array( $actual ) ) {
		$flat = array();
		foreach ( $actual as $item ) {
			if ( is_array( $item ) ) {
				$flat[] = wp_json_encode( $item );
			} else {
				$flat[] = (string) $item;
			}
		}
		$actual_text = implode( ', ', $flat );
	} else {
		$actual_text = (string) $actual;
	}
	$lower = function( $value ) { return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value ) : strtolower( $value ); };
	$needle = $lower( $expected );
	$haystack = $lower( $actual_text );
	if ( 'empty' === $operator ) {
		return '' === trim( $actual_text );
	}
	if ( 'not_empty' === $operator ) {
		return '' !== trim( $actual_text );
	}
	$pos = function_exists( 'mb_strpos' ) ? 'mb_strpos' : 'strpos';
	if ( 'contains' === $operator ) {
		return false !== $pos( $haystack, $needle );
	}
	if ( 'not_contains' === $operator ) {
		return false === $pos( $haystack, $needle );
	}
	if ( 'greater' === $operator ) {
		return is_numeric( $actual_text ) && is_numeric( $expected ) && (float) $actual_text > (float) $expected;
	}
	if ( 'less' === $operator ) {
		return is_numeric( $actual_text ) && is_numeric( $expected ) && (float) $actual_text < (float) $expected;
	}
	if ( 'greater_equal' === $operator ) {
		return is_numeric( $actual_text ) && is_numeric( $expected ) && (float) $actual_text >= (float) $expected;
	}
	if ( 'less_equal' === $operator ) {
		return is_numeric( $actual_text ) && is_numeric( $expected ) && (float) $actual_text <= (float) $expected;
	}
	if ( is_array( $actual ) ) {
		return 'not_equals' === $operator ? ! in_array( $expected, array_map( 'strval', $actual ), true ) : in_array( $expected, array_map( 'strval', $actual ), true );
	}
	return 'not_equals' === $operator ? (string) $actual !== $expected : (string) $actual === $expected;
}

function layout_condition_matches( $condition, $project_id ) {
	$type = sanitize_key( $condition['type'] ?? 'all' );
	$value = $condition['value'] ?? '';
	if ( 'all' === $type ) {
		return true;
	}
	if ( 'project' === $type ) {
		return absint( $value ) === absint( $project_id );
	}
	if ( 'category' === $type ) {
		return has_term( absint( $value ), 'pss_project_category', $project_id );
	}
	if ( 'style' === $type ) {
		return has_term( absint( $value ), 'pss_project_style', $project_id );
	}
	if ( 'location' === $type ) {
		return has_term( absint( $value ), 'pss_project_location', $project_id );
	}
	if ( 'type' === $type ) {
		return has_term( absint( $value ), 'pss_project_type', $project_id );
	}
	if ( 'field' === $type ) {
		return compare_project_field_condition( $project_id, $condition['field_key'] ?? '', $condition['operator'] ?? 'equals', $value );
	}
	return false;
}

function layout_condition_specificity( $type ) {
	$weights = array(
		'all' => 10,
		'category' => 100,
		'location' => 120,
		'style' => 140,
		'type' => 160,
		'field' => 180,
		'project' => 1000,
	);
	return isset( $weights[ $type ] ) ? $weights[ $type ] : 0;
}

function layout_matches( $layout_id, $project_id ) {
	$conditions = get_post_meta( absint( $layout_id ), '_pss_conditions', true );
	$conditions = is_array( $conditions ) ? $conditions : array();
	$logic = get_layout_condition_logic( $layout_id );
	$include_results = array();
	$score = 0;
	$has_include = false;
	foreach ( $conditions as $condition ) {
		$mode = isset( $condition['mode'] ) && 'exclude' === $condition['mode'] ? 'exclude' : 'include';
		$hit = layout_condition_matches( $condition, $project_id );
		$type = sanitize_key( $condition['type'] ?? 'all' );
		if ( 'exclude' === $mode && $hit ) {
			return array( 'match' => false, 'priority' => 0, 'score' => 0 );
		}
		if ( 'include' === $mode ) {
			$has_include = true;
			$include_results[] = $hit;
			if ( $hit ) {
				$score += layout_condition_specificity( $type ) + absint( $condition['priority'] ?? 0 );
			}
		}
	}
	if ( ! $has_include ) {
		return array( 'match' => true, 'priority' => 0, 'score' => 1 );
	}
	$matched = 'any' === $logic ? in_array( true, $include_results, true ) : ! in_array( false, $include_results, true );
	return array( 'match' => $matched, 'priority' => $score, 'score' => $score );
}

function get_matching_layout( $project_id ) {
	$project_id = absint( $project_id );
	if ( ! $project_id ) {
		return 0;
	}
	if ( ! empty( $_GET['pss_preview_layout'] ) && current_user_can( 'edit_posts' ) ) {
		$preview_layout = absint( $_GET['pss_preview_layout'] );
		if ( $preview_layout && PSS_LAYOUT_CPT === get_post_type( $preview_layout ) && 'publish' === get_post_status( $preview_layout ) ) {
			return $preview_layout;
		}
	}
	$override = absint( get_meta( $project_id, '_pss_layout_override', 0 ) );
	if ( $override && 'publish' === get_post_status( $override ) ) {
		return $override;
	}
	$default = absint( get_option( 'pss_default_layout', 0 ) );
	$layouts = get_posts(
		array(
			'post_type'      => PSS_LAYOUT_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'fields'         => 'ids',
		)
	);
	$best_id = 0;
	$best_score = -1;
	foreach ( $layouts as $layout_id ) {
		$match = layout_matches( $layout_id, $project_id );
		if ( ! $match['match'] ) {
			continue;
		}
		if ( $match['score'] > $best_score ) {
			$best_id = $layout_id;
			$best_score = $match['score'];
		}
	}
	return $best_id ? $best_id : $default;
}

function get_field_definitions( $project_id = 0 ) {
	$global = get_option( 'pss_field_definitions', array() );
	$global = is_array( $global ) ? $global : array();
	if ( ! $project_id ) {
		return $global;
	}
	$local = get_post_meta( absint( $project_id ), '_pss_local_field_definitions', true );
	$local = is_array( $local ) ? $local : array();
	$map = array();
	foreach ( $global as $field ) {
		$key = sanitize_key( $field['key'] ?? '' );
		if ( $key ) { $map[ $key ] = $field; }
	}
	foreach ( $local as $field ) {
		$key = sanitize_key( $field['key'] ?? '' );
		if ( ! $key ) { continue; }
		if ( 'global' === sanitize_key( $field['source'] ?? '' ) && isset( $map[ $key ] ) ) {
			// A project row can reference a reusable field without cloning its
			// definition forever. Global label/type/options stay authoritative.
			$map[ $key ]['source'] = 'global';
		} else {
			$map[ $key ] = $field;
		}
	}
	return array_values( $map );
}

function get_project_local_field_definitions( $project_id ) {
	$value = get_post_meta( absint( $project_id ), '_pss_local_field_definitions', true );
	return is_array( $value ) ? $value : array();
}

function get_project_local_field_values( $project_id ) {
	$value = get_post_meta( absint( $project_id ), '_pss_local_field_values', true );
	return is_array( $value ) ? $value : array();
}

function get_field_definition( $project_id, $key ) {
	$key = sanitize_key( $key );
	foreach ( get_field_definitions( $project_id ) as $field ) {
		if ( sanitize_key( $field['key'] ?? '' ) === $key ) {
			return $field;
		}
	}
	if ( 0 === strpos( $key, 'core_' ) ) {
		$core_key = substr( $key, 5 );
		$core = get_project_core_field_library();
		if ( isset( $core[ $core_key ] ) ) {
			return array_merge( $core[ $core_key ], array( 'key' => $key, 'source' => 'core' ) );
		}
	}
	return array();
}

function get_field_value( $project_id, $key, $default = '' ) {
	$key = sanitize_key( $key );
	$local = get_project_local_field_values( $project_id );
	if ( array_key_exists( $key, $local ) ) {
		$value = $local[ $key ];
		return ( '' === $value || null === $value ) ? $default : $value;
	}
	if ( 0 === strpos( $key, 'core_' ) ) {
		$core_key = substr( $key, 5 );
		$core = get_project_core_field_library();
		if ( isset( $core[ $core_key ]['meta_key'] ) ) {
			$value = get_post_meta( $project_id, $core[ $core_key ]['meta_key'], true );
			return ( '' === $value || null === $value ) ? $default : $value;
		}
	}
	$value = get_post_meta( $project_id, '_pss_field_' . $key, true );
	return ( '' === $value || null === $value ) ? $default : $value;
}

function field_value_text( $value ) {
	if ( is_array( $value ) ) {
		$out = array();
		foreach ( $value as $item ) {
			$text = field_value_text( $item );
			if ( '' !== $text ) { $out[] = $text; }
		}
		return implode( ' · ', $out );
	}
	if ( is_bool( $value ) ) { return $value ? 'Yes' : 'No'; }
	return trim( wp_strip_all_tags( (string) $value ) );
}

function get_field_library_map() {
	$map = array();
	foreach ( get_field_definitions() as $field ) {
		$key = sanitize_key( $field['key'] ?? '' );
		if ( $key ) { $map[ $key ] = $field; }
	}
	return $map;
}

function get_project_core_field_library() {
	return array(
		'subtitle' => array( 'label' => 'Subtitle', 'type' => 'text', 'meta_key' => '_pss_subtitle', 'description' => 'Optional project subtitle.' ),
		'year' => array( 'label' => 'Year', 'type' => 'number', 'meta_key' => '_pss_year', 'description' => 'Project year.' ),
		'area' => array( 'label' => 'Area', 'type' => 'text', 'meta_key' => '_pss_area', 'description' => 'Area / size, e.g. 42 m².' ),
		'duration' => array( 'label' => 'Duration', 'type' => 'text', 'meta_key' => '_pss_duration', 'description' => 'Project duration.' ),
		'designer' => array( 'label' => 'Designer', 'type' => 'text', 'meta_key' => '_pss_designer', 'description' => 'Designer name.' ),
		'architect' => array( 'label' => 'Architect', 'type' => 'text', 'meta_key' => '_pss_architect', 'description' => 'Architect name.' ),
		'client' => array( 'label' => 'Client', 'type' => 'text', 'meta_key' => '_pss_client', 'description' => 'Client name.' ),
		'team' => array( 'label' => 'Team', 'type' => 'textarea', 'meta_key' => '_pss_team', 'description' => 'Team members / collaborators.' ),
		'services' => array( 'label' => 'Services', 'type' => 'textarea', 'meta_key' => '_pss_services', 'description' => 'Services delivered on this project.' ),
		'materials' => array( 'label' => 'Materials', 'type' => 'textarea', 'meta_key' => '_pss_materials', 'description' => 'Main materials used.' ),
		'colors' => array( 'label' => 'Colors', 'type' => 'text', 'meta_key' => '_pss_colors', 'description' => 'Primary colors / palette.' ),
		'features' => array( 'label' => 'Features', 'type' => 'textarea', 'meta_key' => '_pss_features', 'description' => 'Key project features.' ),
	);
}

function get_project_field_library_options() {
	$options = array();
	foreach ( get_field_definitions() as $field ) {
		$key = sanitize_key( $field['key'] ?? '' );
		if ( ! $key ) { continue; }
		$options[ 'global:' . $key ] = array(
			'source' => 'global',
			'key' => $key,
			'record_key' => $key,
			'label' => (string) ( $field['label'] ?? $key ),
			'type' => sanitize_key( $field['type'] ?? 'text' ),
			'options' => array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $field['options'] ?? array() ) ) ) ),
			'subfields' => is_array( $field['subfields'] ?? null ) ? $field['subfields'] : array(),
			'description' => (string) ( $field['description'] ?? '' ),
		);
	}
	foreach ( get_project_core_field_library() as $key => $field ) {
		$options[ 'core:' . $key ] = array(
			'source' => 'core',
			'key' => $key,
			'record_key' => 'core_' . sanitize_key( $key ),
			'label' => (string) ( $field['label'] ?? $key ),
			'type' => sanitize_key( $field['type'] ?? 'text' ),
			'options' => array(),
			'subfields' => array(),
			'description' => (string) ( $field['description'] ?? '' ),
			'meta_key' => (string) ( $field['meta_key'] ?? '' ),
		);
	}
	return $options;
}

function sanitize_card_field_key( $key ) {
	$key = strtolower( trim( (string) $key ) );
	if ( 0 === strpos( $key, 'core:' ) ) {
		return 'core:' . sanitize_key( substr( $key, 5 ) );
	}
	return sanitize_key( $key );
}

function get_project_card_fields( $project_id ) {
	$fields = get_post_meta( absint( $project_id ), '_pss_card_fields', true );
	if ( ! is_array( $fields ) || empty( $fields ) ) {
		return array( 'core:style', 'core:location', 'core:year', 'core:area' );
	}
	return array_values( array_filter( array_map( __NAMESPACE__ . '\\sanitize_card_field_key', $fields ) ) );
}

function get_project_card_field( $project_id, $key ) {
	$key = sanitize_card_field_key( $key );
	$core = array(
		'core:title' => array( 'label' => 'Title', 'value' => get_the_title( $project_id ) ),
		'core:subtitle' => array( 'label' => 'Subtitle', 'value' => get_meta( $project_id, '_pss_subtitle' ) ),
		'core:style' => array( 'label' => 'Style', 'value' => get_project_taxonomy_value( $project_id, 'pss_project_style' ) ),
		'core:location' => array( 'label' => 'Location', 'value' => get_project_taxonomy_value( $project_id, 'pss_project_location' ) ),
		'core:type' => array( 'label' => 'Project Type', 'value' => get_project_taxonomy_value( $project_id, 'pss_project_type' ) ),
		'core:category' => array( 'label' => 'Category', 'value' => get_project_taxonomy_value( $project_id, 'pss_project_category' ) ),
		'core:year' => array( 'label' => 'Year', 'value' => get_meta( $project_id, '_pss_year' ) ),
		'core:area' => array( 'label' => 'Area', 'value' => get_meta( $project_id, '_pss_area' ) ),
		'core:duration' => array( 'label' => 'Duration', 'value' => get_meta( $project_id, '_pss_duration' ) ),
		'core:designer' => array( 'label' => 'Designer', 'value' => get_meta( $project_id, '_pss_designer' ) ),
		'core:architect' => array( 'label' => 'Architect', 'value' => get_meta( $project_id, '_pss_architect' ) ),
		'core:client' => array( 'label' => 'Client', 'value' => get_meta( $project_id, '_pss_client' ) ),
	);
	if ( isset( $core[ $key ] ) ) { return $core[ $key ]; }
	$field = get_field_definition( $project_id, $key );
	if ( empty( $field ) ) { return array( 'label' => '', 'value' => '' ); }
	return array(
		'label' => (string) ( $field['label'] ?? $key ),
		'value' => get_field_value( $project_id, $key, '' ),
		'type' => sanitize_key( $field['type'] ?? 'text' ),
		'definition' => $field,
	);
}

function render_field_value( $value, $type, $field = array() ) {
	if ( 'toggle' === $type ) {
		return ! empty( $value ) ? '<span class="pss-badge pss-badge--on">Yes</span>' : '<span class="pss-badge">No</span>';
	}
	if ( 'image' === $type && $value ) {
		$id = absint( $value );
		$url = wp_get_attachment_image_url( $id, 'large' );
		return $url ? '<img class="pss-field-image" src="' . esc_url( $url ) . '" alt="">' : '';
	}
	if ( 'gallery' === $type && is_array( $value ) ) {
		$html = '<div class="pss-field-gallery">';
		foreach ( $value as $id ) {
			$url = wp_get_attachment_image_url( absint( $id ), 'medium_large' );
			if ( $url ) $html .= '<img src="' . esc_url( $url ) . '" alt="">';
		}
		return $html . '</div>';
	}
	$subfields = is_array( $field['subfields'] ?? null ) ? $field['subfields'] : array();
	if ( 'group' === $type && is_array( $value ) ) {
		$labels = array();
		foreach ( $subfields as $subfield ) $labels[ sanitize_key( $subfield['key'] ?? '' ) ] = $subfield['label'] ?? ( $subfield['key'] ?? '' );
		$html = '<div class="pss-structured-group">';
		foreach ( $value as $key => $item ) {
			if ( '' === $item || array() === $item ) continue;
			$label = $labels[ sanitize_key( $key ) ] ?? $key;
			$html .= '<div class="pss-structured-group__item"><span>' . esc_html( $label ) . '</span><strong>' . esc_html( is_scalar( $item ) ? (string) $item : wp_json_encode( $item ) ) . '</strong></div>';
		}
		return $html . '</div>';
	}
	if ( 'repeater' === $type && is_array( $value ) ) {
		$labels = array();
		foreach ( $subfields as $subfield ) $labels[ sanitize_key( $subfield['key'] ?? '' ) ] = $subfield['label'] ?? ( $subfield['key'] ?? '' );
		$html = '<div class="pss-structured-repeater">';
		foreach ( $value as $index => $row ) {
			$html .= '<div class="pss-structured-repeater__row"><span class="pss-structured-repeater__index">' . esc_html( sprintf( '%02d', $index + 1 ) ) . '</span><div>';
			foreach ( (array) $row as $key => $item ) {
				if ( '' === $item || array() === $item ) continue;
				$html .= '<small>' . esc_html( $labels[ sanitize_key( $key ) ] ?? $key ) . '</small><strong>' . esc_html( is_scalar( $item ) ? (string) $item : wp_json_encode( $item ) ) . '</strong>';
			}
			$html .= '</div></div>';
		}
		return $html . '</div>';
	}
	if ( 'table' === $type && is_array( $value ) ) {
		$html = '<div class="pss-table-wrap"><table><thead><tr>';
		foreach ( $subfields as $column ) $html .= '<th>' . esc_html( $column['label'] ?? $column['key'] ?? '' ) . '</th>';
		$html .= '</tr></thead><tbody>';
		foreach ( $value as $row ) {
			$html .= '<tr>';
			foreach ( $subfields as $column ) {
				$key = sanitize_key( $column['key'] ?? '' );
				$cell = is_array( $row ) ? ( $row[ $key ] ?? '' ) : '';
				$html .= '<td>' . esc_html( is_scalar( $cell ) ? (string) $cell : wp_json_encode( $cell ) ) . '</td>';
			}
			$html .= '</tr>';
		}
		return $html . '</tbody></table></div>';
	}
	if ( 'icon_value' === $type && is_array( $value ) ) {
		$html = '<div class="pss-icon-list">';
		foreach ( $value as $row ) {
			$icon = $row['icon'] ?? '•'; $title = $row['title'] ?? ''; $val = $row['value'] ?? '';
			$html .= '<div class="pss-icon-list__item"><span class="pss-icon-list__icon">' . esc_html( $icon ) . '</span><div><strong>' . esc_html( $title ) . '</strong><span>' . esc_html( $val ) . '</span></div></div>';
		}
		return $html . '</div>';
	}
	if ( is_array( $value ) ) $value = implode( ', ', array_map( 'strval', $value ) );
	return esc_html( (string) $value );
}
