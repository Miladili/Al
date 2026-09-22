<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Features extends Base {
	public function get_name() { return 'pss_project_features'; }
	public function get_title() { return 'Project Features'; }
	public function get_icon() { return 'eicon-bullet-list'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Features', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'field_key', array( 'label' => 'Custom Field Key (optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'features' ) );
		$this->add_control( 'style', array( 'label' => 'Style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'check', 'options' => array( 'check' => 'Check List', 'chips' => 'Chips', 'editorial' => 'Editorial' ) ) );
		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		$project_id = $this->project_id( $settings );
		if ( ! $project_id ) return;
		$key = sanitize_key( (string) ( $settings['field_key'] ?? '' ) );
		$value = $key ? \PSS\get_field_value( $project_id, $key, array() ) : \PSS\get_meta( $project_id, '_pss_features', '' );
		if ( is_string( $value ) ) {
			$value = preg_split( '/\r\n|\r|\n|,/', $value );
		}
		if ( ! is_array( $value ) ) $value = array( $value );
		$items = array();
		foreach ( $value as $item ) {
			$text = \PSS\field_value_text( $item );
			if ( '' !== trim( $text ) ) $items[] = $text;
		}
		if ( ! $items ) return;
		$style = sanitize_key( $settings['style'] ?? 'check' );
		echo '<ul class="pss-project-features pss-project-features--' . esc_attr( $style ) . '">';
		foreach ( $items as $item ) echo '<li><span aria-hidden="true">' . ( 'check' === $style ? '✓' : '•' ) . '</span><span>' . esc_html( $item ) . '</span></li>';
		echo '</ul>';
	}
}
