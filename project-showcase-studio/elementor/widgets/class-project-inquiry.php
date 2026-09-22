<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Inquiry extends Base {
	public function get_name() { return 'pss_project_inquiry'; }
	public function get_title() { return 'Project Inquiry CTA'; }
	public function get_icon() { return 'eicon-envelope'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Content', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'label', array( 'label' => 'Button Label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Ask about this project' ) );
		$this->add_control( 'style', array(
			'label' => 'Style',
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'pill',
			'options' => array( 'pill' => 'Pill', 'editorial' => 'Editorial', 'minimal' => 'Minimal' ),
		) );
		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		$project_id = $this->project_id( $settings );
		if ( ! $project_id ) return;
		$url = get_permalink( $project_id );
		if ( ! $url ) return;
		$label = sanitize_text_field( $settings['label'] ?? 'Ask about this project' );
		$style = sanitize_key( $settings['style'] ?? 'pill' );
		echo '<div class="pss-project-inquiry pss-project-inquiry--' . esc_attr( $style ) . '"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . ' <span aria-hidden="true">↗</span></a></div>';
	}
}
