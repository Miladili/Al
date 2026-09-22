<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Sticky extends Base {
	public function get_name() { return 'pss_project_sticky'; }
	public function get_title() { return 'Sticky Scroll Story'; }
	public function get_icon() { return 'eicon-navigator'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Story', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'layout', array( 'label' => 'Preset', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'image-left', 'options' => array( 'image-left'=>'Sticky image left', 'image-right'=>'Sticky image right' ) ) );
		$this->add_control( 'image', array( 'label' => 'Manual image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'text', array( 'label' => 'Manual text', 'type' => \Elementor\Controls_Manager::WYSIWYG, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'min_height', array( 'label' => 'Image height', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'default' => array( 'size' => 80, 'unit' => 'vh' ), 'selectors' => array( '{{WRAPPER}} .pss-sticky__media img' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_typography( 'text_typo', '{{WRAPPER}} .pss-sticky__copy' );
		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		if ( $this->is_manual( $s ) ) {
			$image = $this->media_url( $s['image'] ?? array() );
			$text  = $s['text'] ?? '';
		} else {
			$image = $this->project_image( $id, 'featured' );
			$text  = $id ? apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) : '';
		}
		if ( ! $image && ! $text ) {
			return;
		}
		echo '<div class="pss-sticky pss-sticky--' . esc_attr( sanitize_key( $s['layout'] ?? 'image-left' ) ) . '">';
		echo '<div class="pss-sticky__media">';
		if ( $image ) {
			echo '<img src="' . esc_url( $image ) . '" alt="">';
		}
		echo '</div><div class="pss-sticky__copy">' . wp_kses_post( $text ) . '</div></div>';
	}
}
