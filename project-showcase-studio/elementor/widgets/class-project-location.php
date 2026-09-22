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
		$this->add_control( 'field_key', array(
			'label' => 'Custom Field Key (optional)',
			'type' => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'location',
			'description' => 'Leave empty to use the Project Location taxonomy.',
		) );
		$this->add_control( 'style', array(
			'label' => 'Style',
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'clean',
			'options' => array( 'clean' => 'Clean', 'editorial' => 'Editorial', 'minimal' => 'Minimal' ),
		) );
		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		$project_id = $this->project_id( $settings );
		if ( ! $project_id ) return;
		$key = sanitize_key( (string) ( $settings['field_key'] ?? '' ) );
		$value = $key ? \PSS\get_field_value( $project_id, $key, '' ) : \PSS\get_project_taxonomy_value( $project_id, 'pss_project_location' );
		$text = \PSS\field_value_text( $value );
		if ( '' === trim( $text ) ) return;
		$style = sanitize_key( $settings['style'] ?? 'clean' );
		echo '<div class="pss-project-simple-widget pss-project-location pss-project-location--' . esc_attr( $style ) . '">';
		echo '<span class="pss-project-simple-widget__label">Location</span>';
		echo '<div class="pss-project-simple-widget__value">' . esc_html( $text ) . '</div>';
		echo '</div>';
	}
}
