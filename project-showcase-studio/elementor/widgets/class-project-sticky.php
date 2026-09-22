<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Sticky extends Base {
	public function get_name() { return 'pss_project_sticky'; }
	public function get_title() { return 'Sticky Scroll Story'; }
	public function get_icon() { return 'eicon-navigator'; }
	public function get_script_depends() { return array( 'pss-gsap', 'pss-scrolltrigger', 'pss-frontend' ); }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Story', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'layout', array( 'label' => 'Preset', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'image-left', 'options' => array( 'image-left'=>'Sticky image left', 'image-right'=>'Sticky image right' ) ) );
		$this->add_control( 'image', array( 'label' => 'Manual image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'text', array( 'label' => 'Manual text', 'type' => \Elementor\Controls_Manager::WYSIWYG, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'show_progress', array( 'label' => 'Progress', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'mobile_stack', array( 'label' => 'Stack on mobile', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		if ( class_exists( '\\Elementor\\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'step_title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'step_text', array( 'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
			$repeater->add_control( 'step_image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA ) );
			$this->add_control( 'steps', array( 'label' => 'Steps', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ step_title }}}', 'prevent_empty' => false ) );
		}
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'min_height', array( 'label' => 'Image height', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'default' => array( 'size' => 80, 'unit' => 'vh' ), 'selectors' => array( '{{WRAPPER}} .pss-sticky__media img, {{WRAPPER}} .pss-sticky__frame' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'progress_color', array( 'label' => 'Progress', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-sticky__progress span' => 'background: {{VALUE}};' ) ) );
		$this->add_typography( 'text_typo', '{{WRAPPER}} .pss-sticky__copy' );
		$this->end_controls_section();
		$this->add_motion_vars( '{{WRAPPER}} .pss-sticky' );
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		$steps = array();
		foreach ( (array) ( $s['steps'] ?? array() ) as $row ) {
			$img = $this->media_url( $row['step_image'] ?? array() );
			$title = sanitize_text_field( $row['step_title'] ?? '' );
			$text  = wp_kses_post( $row['step_text'] ?? '' );
			if ( ! $img && ! $title && ! $text ) { continue; }
			$steps[] = array( 'image' => $img, 'title' => $title, 'text' => $text );
		}
		if ( ! $steps ) {
			if ( $this->is_manual( $s ) ) {
				$image = $this->media_url( $s['image'] ?? array() );
				$text  = $s['text'] ?? '';
			} else {
				$image = $this->project_image( $id, 'featured' );
				$text  = $id ? apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) : '';
			}
			if ( ! $image && ! $text ) { return; }
			$steps[] = array( 'image' => $image, 'title' => '', 'text' => $text );
		}
		$stack = ! empty( $s['mobile_stack'] ) ? ' pss-sticky--mobile-stack' : '';
		echo '<div class="pss-sticky pss-sticky--' . esc_attr( sanitize_key( $s['layout'] ?? 'image-left' ) ) . $stack . '" data-pss-sticky="1">';
		if ( ! empty( $s['show_progress'] ) ) {
			echo '<div class="pss-sticky__progress" aria-hidden="true"><span></span></div>';
		}
		echo '<div class="pss-sticky__media">';
		foreach ( $steps as $i => $step ) {
			if ( empty( $step['image'] ) ) { continue; }
			echo '<img class="pss-sticky__frame' . ( 0 === $i ? ' is-active' : '' ) . '" src="' . esc_url( $step['image'] ) . '" alt="' . esc_attr( $step['title'] ) . '" data-step="' . esc_attr( $i ) . '">';
		}
		echo '</div><div class="pss-sticky__copy">';
		foreach ( $steps as $i => $step ) {
			echo '<article class="pss-sticky__step" data-step="' . esc_attr( $i ) . '">';
			if ( $step['title'] ) { echo '<h3>' . esc_html( $step['title'] ) . '</h3>'; }
			if ( $step['text'] ) { echo '<div>' . wp_kses_post( $step['text'] ) . '</div>'; }
			echo '</article>';
		}
		echo '</div></div>';
	}
}
