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
		$this->add_control( 'label', array( 'label' => 'Button label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Ask about this project' ) );
		$this->add_control( 'link_type', array( 'label' => 'Link', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'permalink', 'options' => array( 'permalink' => 'Project URL', 'custom' => 'Custom URL', 'mailto' => 'Email' ) ) );
		$this->add_control( 'custom_url', array( 'label' => 'Custom URL', 'type' => \Elementor\Controls_Manager::URL, 'condition' => array( 'link_type' => 'custom' ) ) );
		$this->add_control( 'email', array( 'label' => 'Email', 'type' => \Elementor\Controls_Manager::TEXT, 'condition' => array( 'link_type' => 'mailto' ) ) );
		$this->add_control( 'style', array( 'label' => 'Style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'pill', 'options' => array( 'pill' => 'Pill', 'editorial' => 'Editorial', 'minimal' => 'Minimal' ) ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-inquiry a' );
		$this->add_text_style( 'btn', 'Button text', '{{WRAPPER}} .pss-project-inquiry a' );
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		$type = sanitize_key( $s['link_type'] ?? 'permalink' );
		if ( 'custom' === $type ) {
			$url = $s['custom_url']['url'] ?? '';
		} elseif ( 'mailto' === $type ) {
			$email = sanitize_email( $s['email'] ?? '' );
			$url   = $email ? 'mailto:' . $email : '';
		} else {
			$url = $id ? get_permalink( $id ) : '';
		}
		if ( ! $url ) { return; }
		$label = sanitize_text_field( $s['label'] ?? 'Ask about this project' );
		$style = sanitize_key( $s['style'] ?? 'pill' );
		$blank = ( 'custom' === $type && ! empty( $s['custom_url']['is_external'] ) ) ? ' target="_blank" rel="noopener"' : '';
		echo '<div class="pss-project-inquiry pss-project-inquiry--' . esc_attr( $style ) . '"><a href="' . esc_url( $url ) . '"' . $blank . '>' . esc_html( $label ) . ' <span aria-hidden="true">↗</span></a></div>';
	}
}
