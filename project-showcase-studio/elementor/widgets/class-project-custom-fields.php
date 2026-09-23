<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Custom_Fields extends Base {
	public function get_name() { return 'pss_project_custom_fields'; }
	public function get_title() { return 'Project Custom Fields'; }
	public function get_icon() { return 'eicon-form-horizontal'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Display' ) );
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'grid', 'options' => array( 'grid' => 'Grid', 'list' => 'List', 'cards' => 'Cards' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 6, 'selectors' => array( '{{WRAPPER}} .pss-custom-fields--grid, {{WRAPPER}} .pss-custom-fields--cards' => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ) ) );
		$this->add_control( 'show_labels', array( 'label' => 'Show labels', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'include_keys', array( 'label' => 'Only these field keys (optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'area, bedrooms, status' ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-custom-fields' );
		$this->add_text_style( 'label', 'Labels', '{{WRAPPER}} .pss-custom-field__label' );
		$this->add_text_style( 'value', 'Values', '{{WRAPPER}} .pss-custom-field__value' );
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		if ( ! $id ) {
			$this->empty_state( 'Project Custom Fields', 'Choose a preview project in Single Layout settings.' );
			return;
		}
		$defs = \PSS\get_field_definitions( $id );
		if ( ! $defs ) {
			$this->empty_state( 'Project Custom Fields', 'This project has no extra fields yet. Add fields on the project editor.' );
			return;
		}
		$only = array_filter( array_map( 'sanitize_key', preg_split( '/[,\\s]+/', (string) ( $s['include_keys'] ?? '' ) ) ) );
		echo '<div class="pss-custom-fields pss-custom-fields--' . esc_attr( $s['layout'] ?? 'grid' ) . '">';
		foreach ( $defs as $field ) {
			$key = sanitize_key( $field['key'] ?? '' );
			if ( $only && ! in_array( $key, $only, true ) ) { continue; }
			$value = \PSS\get_field_value( $id, $key, '' );
			if ( '' === $value || array() === $value ) { continue; }
			echo '<div class="pss-custom-field">';
			if ( ! empty( $s['show_labels'] ) ) {
				echo '<span class="pss-custom-field__label">' . esc_html( $field['label'] ?? $key ) . '</span>';
			}
			echo '<div class="pss-custom-field__value">' . \PSS\render_field_value( $value, $field['type'] ?? 'text', $field ) . '</div></div>';
		}
		echo '</div>';
	}
}
