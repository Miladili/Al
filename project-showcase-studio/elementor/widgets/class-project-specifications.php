<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Specifications extends Base {
	public function get_name() { return 'pss_project_specifications'; }
	public function get_title() { return 'Project Specifications'; }
	public function get_icon() { return 'eicon-table'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Specifications', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'field_keys', array(
			'label' => 'Field Keys',
			'type' => \Elementor\Controls_Manager::TEXTAREA,
			'rows' => 6,
			'placeholder' => "area\nstyle\nlocation\ncompletion_date",
			'description' => 'One project field key per line. Leave empty to use the built-in project meta.',
		) );
		$this->add_control( 'layout', array(
			'label' => 'Layout',
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'grid',
			'options' => array( 'grid' => 'Grid', 'list' => 'List', 'minimal' => 'Minimal' ),
		) );
		$this->add_control( 'animation', array(
			'label' => 'Reveal',
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'soft',
			'options' => array( 'none' => 'None', 'soft' => 'Soft Reveal', 'blur' => 'Blur Reveal', 'up' => 'Rise Up' ),
		) );
		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		$project_id = $this->project_id( $settings );
		if ( ! $project_id ) {
			$this->empty_state( 'Project Specifications', 'Choose a preview project in Single Layout settings.' );
			return;
		}
		$keys = preg_split( '/\r\n|\r|\n/', (string) ( $settings['field_keys'] ?? '' ) );
		$keys = array_values( array_filter( array_map( 'sanitize_key', $keys ) ) );
		if ( ! $keys ) {
			$keys = array( 'project_type', 'style', 'location', 'year', 'area', 'duration' );
		}
		$items = array();
		foreach ( $keys as $key ) {
			$value = \PSS\get_field_value( $project_id, $key, null );
			if ( null === $value || '' === \PSS\field_value_text( $value ) ) {
				$core = array(
					'project_type' => array( 'label' => 'Type', 'value' => \PSS\get_project_taxonomy_value( $project_id, 'pss_project_type' ) ),
					'style' => array( 'label' => 'Style', 'value' => \PSS\get_project_taxonomy_value( $project_id, 'pss_project_style' ) ),
					'location' => array( 'label' => 'Location', 'value' => \PSS\get_project_taxonomy_value( $project_id, 'pss_project_location' ) ),
					'year' => array( 'label' => 'Year', 'value' => \PSS\get_meta( $project_id, '_pss_year', '' ) ),
					'area' => array( 'label' => 'Area', 'value' => \PSS\get_meta( $project_id, '_pss_area', '' ) ),
					'duration' => array( 'label' => 'Duration', 'value' => \PSS\get_meta( $project_id, '_pss_duration', '' ) ),
				);
				if ( isset( $core[ $key ] ) ) {
					$value = $core[ $key ]['value'];
					$label = $core[ $key ]['label'];
				} else {
					$label = $key;
				}
			} else {
				$definition = \PSS\get_field_definition( $project_id, $key );
				$label = (string) ( $definition['label'] ?? ucwords( str_replace( '_', ' ', $key ) ) );
			}
		$text = \PSS\field_value_text( $value );
		if ( '' === trim( $text ) ) continue;
		$items[] = array( 'key' => $key, 'label' => $label, 'value' => $text );
	}
	if ( ! $items ) {
		$this->empty_state( 'Project Specifications', 'No specification values on this project. Add fields, or list field keys in the widget.' );
		return;
	}
	$layout = sanitize_key( $settings['layout'] ?? 'grid' );
	$animation = sanitize_key( $settings['animation'] ?? 'soft' );
	echo '<div class="pss-project-specs pss-project-specs--' . esc_attr( $layout ) . ' pss-project-specs--anim-' . esc_attr( $animation ) . '">';
	foreach ( $items as $item ) {
		echo '<div class="pss-project-spec">';
		echo '<span class="pss-project-spec__label">' . esc_html( $item['label'] ) . '</span>';
		echo '<strong class="pss-project-spec__value">' . esc_html( $item['value'] ) . '</strong>';
		echo '</div>';
	}
	echo '</div>';
	}
}
