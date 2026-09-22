<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Testimonials extends Base {
	public function get_name() { return 'pss_project_testimonials'; }
	public function get_title() { return 'Project Testimonials'; }
	public function get_icon() { return 'eicon-testimonial'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Quotes' ) );
		if ( class_exists( '\Elementor\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'quote', array( 'label' => 'Quote', 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
			$repeater->add_control( 'name', array( 'label' => 'Name', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'role', array( 'label' => 'Role', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'image', array( 'label' => 'Portrait', 'type' => \Elementor\Controls_Manager::MEDIA ) );
			$this->add_control( 'items', array( 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ name }}}' ) );
		}
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_typography( 'quote_typo', '{{WRAPPER}} .pss-quote blockquote' );
		$this->add_control( 'color', array( 'label' => 'Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-quote' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$items = (array) ( $this->get_settings_for_display()['items'] ?? array() );
		if ( ! $items ) {
			return;
		}
		echo '<div class="pss-quotes">';
		foreach ( $items as $item ) {
			echo '<figure class="pss-quote">';
			$url = $this->media_url( $item['image'] ?? array() );
			if ( $url ) {
				echo '<img src="' . esc_url( $url ) . '" alt="">';
			}
			echo '<blockquote>' . esc_html( $item['quote'] ?? '' ) . '</blockquote><figcaption><strong>' . esc_html( $item['name'] ?? '' ) . '</strong> ' . esc_html( $item['role'] ?? '' ) . '</figcaption></figure>';
		}
		echo '</div>';
	}
}
