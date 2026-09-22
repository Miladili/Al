<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Image extends Base {
	public function get_name() { return 'pss_project_image'; }
	public function get_title() { return 'Project Image'; }
	public function get_icon() { return 'eicon-image'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Image' ) );
		$this->add_control( 'from', array( 'label' => 'Project image', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'featured', 'options' => array( 'featured' => 'Featured', 'gallery' => 'First gallery image', 'before' => 'Before', 'after' => 'After', 'floor' => 'Floor plan' ), 'condition' => array( 'content_source' => 'project' ) ) );
		$this->add_control( 'image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'image_url', array( 'label' => 'Image URL', 'type' => \Elementor\Controls_Manager::URL, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'overlay', array( 'label' => 'Overlay', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'caption', array( 'label' => 'Caption', 'type' => \Elementor\Controls_Manager::TEXT ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'radius', array( 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => array( '{{WRAPPER}} .pss-project-image' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'overlay_color', array( 'label' => 'Overlay color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-image--overlay span' => 'background: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$id  = $this->project_id( $s );
		$url = $this->is_manual( $s ) ? $this->media_url( $s['image'] ?? array(), $s['image_url']['url'] ?? '' ) : $this->project_image( $id, $s['from'] ?? 'featured' );
		if ( ! $url ) {
			return;
		}
		echo '<div class="pss-project-image' . ( ! empty( $s['overlay'] ) ? ' pss-project-image--overlay' : '' ) . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $s['caption'] ?: ( $id ? get_the_title( $id ) : '' ) ) . '">';
		if ( ! empty( $s['overlay'] ) ) {
			echo '<span></span>';
		}
		if ( ! empty( $s['caption'] ) ) {
			echo '<em class="pss-project-image__caption">' . esc_html( $s['caption'] ) . '</em>';
		}
		echo '</div>';
	}
}
