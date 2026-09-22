<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Slider extends Base {
	public function get_name() { return 'pss_project_slider'; }
	public function get_title() { return 'Project Slider'; }
	public function get_icon() { return 'eicon-slides'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Slider' ) );
		$this->add_control( 'limit', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 6, 'min' => 2, 'max' => 16 ) );
		$this->add_control( 'preset', array( 'label' => 'Card style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'cinematic', 'options' => array( 'modern' => 'Modern', 'luxury' => 'Luxury', 'cinematic' => 'Cinematic', 'minimal' => 'Minimal', 'overlay' => 'Overlay' ) ) );
		$this->add_responsive_control( 'visible', array( 'label' => 'Visible slides', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 1.2, 'tablet_default' => 1, 'mobile_default' => 1, 'min' => 1, 'max' => 4, 'step' => 0.1 ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$posts = \PSS\Ajax::query( array( 'limit' => absint( $s['limit'] ?? 6 ) ) );
		if ( ! $posts ) {
			return;
		}
		$settings = array(
			'preset'         => sanitize_key( $s['preset'] ?? 'cinematic' ),
			'animation'      => 'reveal',
			'meta_placement' => 'overlay',
			'show_title'     => true,
		);
		echo '<div class="pss-slider" style="--pss-visible:' . esc_attr( $s['visible'] ?? 1.2 ) . '"><div class="pss-slider__track">' . \PSS\RenderCards::cards( $posts, $settings ) . '</div></div>';
	}
}
