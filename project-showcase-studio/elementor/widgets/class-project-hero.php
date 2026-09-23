<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Hero extends Base {
	public function get_name() { return 'pss_project_hero'; }
	public function get_title() { return 'Project Hero'; }
	public function get_icon() { return 'eicon-cover-image'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Hero', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_image', array( 'label' => 'Show image', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_subtitle', array( 'label' => 'Show subtitle', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'eyebrow', array( 'label' => 'Eyebrow', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Project' ) );
		$this->add_control( 'cta', array( 'label' => 'CTA text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View project' ) );
		$this->add_control( 'manual_title', array( 'label' => 'Manual title', 'type' => \Elementor\Controls_Manager::TEXT, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'manual_subtitle', array( 'label' => 'Manual subtitle', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'manual_image', array( 'label' => 'Manual image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'manual_link', array( 'label' => 'Manual CTA link', 'type' => \Elementor\Controls_Manager::URL, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'min_height', array( 'label' => 'Min height', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 240, 'max' => 1000 ), 'vh' => array( 'min' => 30, 'max' => 100 ) ), 'default' => array( 'size' => 640, 'unit' => 'px' ), 'tablet_default' => array( 'size' => 520, 'unit' => 'px' ), 'mobile_default' => array( 'size' => 420, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .pss-project-hero' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'overlay', array( 'label' => 'Overlay', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-hero__veil' => 'background: linear-gradient(180deg, rgba(0,0,0,0) 25%, {{VALUE}} 100%);' ) ) );
		$this->add_control( 'title_color', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-hero h1' => 'color: {{VALUE}};' ) ) );
		$this->add_typography( 'title_typo', '{{WRAPPER}} .pss-project-hero h1' );
		$this->add_responsive_control( 'align', array( 'label' => 'Align', 'type' => \Elementor\Controls_Manager::CHOOSE, 'options' => array( 'left' => array( 'title' => 'Left', 'icon' => 'eicon-text-align-left' ), 'center' => array( 'title' => 'Center', 'icon' => 'eicon-text-align-center' ), 'right' => array( 'title' => 'Right', 'icon' => 'eicon-text-align-right' ) ), 'selectors' => array( '{{WRAPPER}} .pss-project-hero__content' => 'text-align: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( $this->is_manual( $s ) ) {
			$title    = $s['manual_title'] ?? '';
			$subtitle = $s['manual_subtitle'] ?? '';
			$image    = $this->media_url( $s['manual_image'] ?? array() );
			$link     = $s['manual_link']['url'] ?? '';
		} else {
			$id       = $this->project_id( $s );
			if ( ! $id ) {
				$this->empty_state( 'Project Hero', 'Choose a preview project in Single Layout settings, or switch this widget to Manual content.' );
				return;
			}
			$title    = get_the_title( $id );
			$subtitle = \PSS\get_meta( $id, '_pss_subtitle' );
			if ( ! $subtitle ) {
				$subtitle = \PSS\get_field_value( $id, 'subtitle', '' );
			}
			$image    = get_the_post_thumbnail_url( $id, 'full' );
			$link     = get_permalink( $id );
		}
		if ( ! $title && ! $image ) {
			$this->empty_state( 'Project Hero', 'This project needs a title or featured image.' );
			return;
		}
		echo '<section class="pss-project-hero">';
		if ( $image && ! empty( $s['show_image'] ) ) {
			echo '<img class="pss-project-hero__image" src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '">';
		}
		echo '<span class="pss-project-hero__veil"></span><div class="pss-project-hero__content">';
		if ( ! empty( $s['eyebrow'] ) ) {
			echo '<span class="pss-project-hero__eyebrow">' . esc_html( $s['eyebrow'] ) . '</span>';
		}
		if ( $title ) {
			echo '<h1>' . esc_html( $title ) . '</h1>';
		}
		if ( ! empty( $s['show_subtitle'] ) && $subtitle ) {
			echo '<p>' . esc_html( $subtitle ) . '</p>';
		}
		if ( ! empty( $s['cta'] ) && $link ) {
			echo '<a class="pss-project-hero__cta" href="' . esc_url( $link ) . '">' . esc_html( $s['cta'] ) . '<span>↗</span></a>';
		}
		echo '</div></section>';
	}
}
