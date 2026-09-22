<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Reveal extends Base {
	public function get_name() { return 'pss_project_reveal'; }
	public function get_title() { return 'Project Image Reveal'; }
	public function get_icon() { return 'eicon-animation'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Reveal' ) );
		$this->add_control( 'image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'direction', array( 'label' => 'Direction', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'up', 'options' => array( 'up' => 'Up', 'left' => 'Left', 'right' => 'Right', 'fade' => 'Fade' ) ) );
		$this->add_control( 'caption', array( 'label' => 'Caption', 'type' => \Elementor\Controls_Manager::TEXT ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'min_height', array( 'label' => 'Height', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 180, 'max' => 900 ), 'vh' => array( 'min' => 20, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .pss-reveal' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$id  = $this->project_id( $s );
		$url = $this->is_manual( $s ) ? $this->media_url( $s['image'] ?? array() ) : $this->project_image( $id );
		if ( ! $url ) {
			return;
		}
		echo '<figure class="pss-reveal pss-reveal--' . esc_attr( $s['direction'] ?? 'up' ) . '"><img src="' . esc_url( $url ) . '" alt="">';
		if ( ! empty( $s['caption'] ) ) {
			echo '<figcaption>' . esc_html( $s['caption'] ) . '</figcaption>';
		}
		echo '</figure>';
	}
}
