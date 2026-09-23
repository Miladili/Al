<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Location extends Base {
	public function get_name() { return 'pss_project_location'; }
	public function get_title() { return 'Project Location'; }
	public function get_icon() { return 'eicon-google-maps'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Content', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'field_key', array( 'label' => 'Custom field key (optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'location', 'description' => 'Leave empty to use the Project Location taxonomy or a map field.' ) );
		$this->add_control( 'manual_text', array( 'label' => 'Manual location', 'type' => \Elementor\Controls_Manager::TEXT, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'show_map', array( 'label' => 'Show map embed when coordinates exist', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'style', array( 'label' => 'Style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'clean', 'options' => array( 'clean' => 'Clean', 'editorial' => 'Editorial', 'minimal' => 'Minimal' ) ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-location' );
		$this->add_text_style( 'loc', 'Text', '{{WRAPPER}} .pss-project-simple-widget__value' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$map      = array();
		if ( $this->is_manual( $settings ) ) {
			$text = (string) ( $settings['manual_text'] ?? '' );
		} else {
			$project_id = $this->project_id( $settings );
			if ( ! $project_id ) { return; }
			$key   = sanitize_key( (string) ( $settings['field_key'] ?? '' ) );
			$value = $key ? \PSS\get_field_value( $project_id, $key, '' ) : \PSS\get_project_taxonomy_value( $project_id, 'pss_project_location' );
			if ( is_array( $value ) ) {
				$map  = $value;
				$text = \PSS\field_value_text( $value['address'] ?? $value );
			} else {
				$text = \PSS\field_value_text( $value );
			}
		}
		if ( '' === trim( $text ) && empty( $map['lat'] ) ) {
			$this->empty_state( 'Project Location', 'Set a Location taxonomy term or a map field on the project.' );
			return;
		}
		$style = sanitize_key( $settings['style'] ?? 'clean' );
		echo '<div class="pss-project-simple-widget pss-project-location pss-project-location--' . esc_attr( $style ) . '">';
		echo '<span class="pss-project-simple-widget__label">Location</span>';
		if ( $text ) {
			echo '<div class="pss-project-simple-widget__value">' . esc_html( $text ) . '</div>';
		}
		if ( ! empty( $settings['show_map'] ) && ! empty( $map['lat'] ) && ! empty( $map['lng'] ) ) {
			$q = rawurlencode( trim( $map['lat'] . ',' . $map['lng'] ) );
			echo '<iframe class="pss-project-location__map" src="https://maps.google.com/maps?q=' . $q . '&z=14&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Project location"></iframe>';
		}
		echo '</div>';
	}
}
