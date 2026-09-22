<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Materials extends Base {
	public function get_name() { return 'pss_project_materials'; }
	public function get_title() { return 'Project Materials'; }
	public function get_icon() { return 'eicon-posts-ticker'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Content', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'field_key', array(
			'label' => 'Custom Field Key (optional)',
			'type' => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'materials',
			'description' => 'Leave empty to use the built-in Project Materials field.',
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
		$value = $key ? \PSS\get_field_value( $project_id, $key, '' ) : \PSS\get_meta( $project_id, '_pss_materials', '' );
		$text = \PSS\field_value_text( $value );
		if ( '' === trim( $text ) ) return;
		$style = sanitize_key( $settings['style'] ?? 'clean' );
		echo '<div class="pss-project-simple-widget pss-project-materials pss-project-materials--' . esc_attr( $style ) . '">';
		echo '<span class="pss-project-simple-widget__label">Materials</span>';
		echo '<div class="pss-project-simple-widget__value">' . wp_kses_post( nl2br( esc_html( $text ) ) ) . '</div>';
		echo '</div>';
	}
}
