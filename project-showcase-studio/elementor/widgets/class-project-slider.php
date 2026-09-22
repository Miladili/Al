<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Slider extends Base {
	public function get_name() { return 'pss_project_slider'; }
	public function get_title() { return 'Project Slider'; }
	public function get_icon() { return 'eicon-slides'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Slider', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'content_source', array( 'label' => 'Source', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'project', 'options' => array( 'project' => 'Dynamic projects', 'manual' => 'Manual project list' ) ) );
		$this->add_control( 'limit', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 8, 'min' => 2, 'max' => 24, 'condition' => array( 'content_source' => 'project' ) ) );
		if ( class_exists( '\\Elementor\\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'project_id', array( 'label' => 'Project', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => $this->safe_projects(), 'label_block' => true ) );
			$this->add_control( 'manual_projects', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => 'Project', 'condition' => array( 'content_source' => 'manual' ), 'prevent_empty' => false ) );
		}
		$this->add_control( 'preset', array( 'label' => 'Card composition', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'cinematic', 'options' => array( 'cinematic'=>'Cinematic', 'luxury'=>'Luxury', 'minimal'=>'Minimal', 'overlay'=>'Overlay', 'fullscreen'=>'Fullscreen', 'editorial'=>'Editorial', 'magazine'=>'Magazine' ) ) );
		$this->add_control( 'show_title', array( 'label' => 'Show title', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'title_placement', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'overlay', 'options' => array( 'overlay'=>'On image', 'below'=>'Below', 'hidden'=>'Hidden' ) ) );
		$this->add_control( 'meta_placement', array( 'label' => 'Metadata', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'overlay', 'options' => array( 'overlay'=>'On image', 'below'=>'Below', 'hidden'=>'Hidden' ) ) );
		$this->add_control( 'cta_placement', array( 'label' => 'CTA', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'overlay', 'options' => array( 'hidden'=>'Hidden', 'overlay'=>'On image', 'below'=>'Below' ) ) );
		$this->add_control( 'cta_label', array( 'label' => 'CTA label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View project' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout', array( 'label' => 'Layout', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'full_width', array( 'label' => 'Full width / edge to edge', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_responsive_control( 'content_width', array(
			'label' => 'Content width', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( '%', 'px' ), 'range' => array( '%' => array( 'min' => 40, 'max' => 100 ), 'px' => array( 'min' => 480, 'max' => 1600 ) ),
			'default' => array( 'unit' => '%', 'size' => 100 ),
			'selectors' => array( '{{WRAPPER}} .pss-slider__shell' => 'max-width: {{SIZE}}{{UNIT}};' ),
			'condition' => array( 'full_width!' => 'yes' ),
		) );
		$this->add_responsive_control( 'visible', array(
			'label' => 'Slides per view', 'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 1.25, 'tablet_default' => 1.1, 'mobile_default' => 1, 'min' => 1, 'max' => 5, 'step' => 0.05,
			'selectors' => array( '{{WRAPPER}} .pss-slider' => '--pss-slides: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'gap', array(
			'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'default' => array( 'size' => 18, 'unit' => 'px' ),
			'tablet_default' => array( 'size' => 14, 'unit' => 'px' ),
			'mobile_default' => array( 'size' => 10, 'unit' => 'px' ),
			'selectors' => array( '{{WRAPPER}} .pss-slider' => '--pss-gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'image_ratio', array(
			'label' => 'Image ratio', 'type' => \Elementor\Controls_Manager::SELECT,
			'default' => '16 / 9', 'tablet_default' => '16 / 10', 'mobile_default' => '4 / 5',
			'options' => array( '16 / 9'=>'16:9', '16 / 10'=>'16:10', '4 / 5'=>'4:5', '1 / 1'=>'1:1', '3 / 2'=>'3:2' ),
			'selectors' => array( '{{WRAPPER}} .pss-card__media' => 'aspect-ratio: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'image_height', array(
			'label' => 'Image height', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 180, 'max' => 900 ), 'vh' => array( 'min' => 30, 'max' => 100 ) ),
			'selectors' => array( '{{WRAPPER}} .pss-card__media' => 'height: {{SIZE}}{{UNIT}}; aspect-ratio: auto;' ),
		) );
		$this->add_control( 'center', array( 'label' => 'Center mode', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'peek', array( 'label' => 'Peek next slide', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'direction', array( 'label' => 'Direction', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'horizontal', 'options' => array( 'horizontal'=>'Horizontal', 'vertical'=>'Vertical' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'motion', array( 'label' => 'Motion', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'autoplay', array( 'label' => 'Autoplay', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'speed_ms', array( 'label' => 'Autoplay speed (ms)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 4200, 'min' => 1200, 'max' => 12000 ) );
		$this->add_control( 'pause_hover', array( 'label' => 'Pause on hover', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'loop', array( 'label' => 'Infinite loop', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'arrows', array( 'label' => 'Arrows', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'dots', array( 'label' => 'Dots', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'progress', array( 'label' => 'Progress bar', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'drag', array( 'label' => 'Drag / swipe', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'keyboard', array( 'label' => 'Keyboard', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'wheel', array( 'label' => 'Mouse wheel', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'transition', array( 'label' => 'Transition', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'slide', 'options' => array( 'slide'=>'Slide', 'fade'=>'Fade', 'scale'=>'Scale', 'coverflow'=>'Coverflow' ) ) );
		$this->add_control( 'duration', array( 'label' => 'Transition speed (ms)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 620, 'min' => 120, 'max' => 1600 ) );
		$this->add_control( 'easing', array( 'label' => 'Easing', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'smooth', 'options' => array( 'linear'=>'Linear', 'smooth'=>'Smooth', 'cinematic'=>'Cinematic' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'arrow_color', array( 'label' => 'Arrows', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-slider__arrow' => 'color: {{VALUE}}; border-color: {{VALUE}};' ) ) );
		$this->add_control( 'dot_color', array( 'label' => 'Dots', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-slider__dot' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'progress_color', array( 'label' => 'Progress', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-slider__progress span' => 'background: {{VALUE}};' ) ) );
		$this->add_typography( 'title_typo', '{{WRAPPER}} .pss-card__title' );
		$this->end_controls_section();
		$this->add_motion_vars( '{{WRAPPER}} .pss-slider' );
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		if ( 'manual' === ( $s['content_source'] ?? '' ) ) {
			$posts = array();
			foreach ( (array) ( $s['manual_projects'] ?? array() ) as $row ) {
				$id = absint( $row['project_id'] ?? 0 );
				if ( $id ) { $posts[] = get_post( $id ); }
			}
			$posts = array_filter( $posts );
		} else {
			$posts = \PSS\Ajax::query( array( 'limit' => absint( $s['limit'] ?? 8 ) ) );
		}
		if ( ! $posts ) {
			return;
		}
		$settings = array(
			'preset'          => sanitize_key( $s['preset'] ?? 'cinematic' ),
			'animation'       => 'none',
			'show_title'      => ! empty( $s['show_title'] ),
			'title_placement' => sanitize_key( $s['title_placement'] ?? 'overlay' ),
			'meta_placement'  => sanitize_key( $s['meta_placement'] ?? 'overlay' ),
			'cta_placement'   => sanitize_key( $s['cta_placement'] ?? 'overlay' ),
			'cta_label'       => (string) ( $s['cta_label'] ?? 'View project' ),
			'index_placement' => 'overlay',
		);
		$cfg = array(
			'autoplay'   => ! empty( $s['autoplay'] ),
			'speed'      => absint( $s['speed_ms'] ?? 4200 ),
			'pause'      => ! empty( $s['pause_hover'] ),
			'loop'       => ! empty( $s['loop'] ),
			'drag'       => ! empty( $s['drag'] ),
			'keyboard'   => ! empty( $s['keyboard'] ),
			'center'     => ! empty( $s['center'] ),
			'transition' => sanitize_key( $s['transition'] ?? 'slide' ),
			'duration'   => absint( $s['duration'] ?? 620 ),
			'easing'     => sanitize_key( $s['easing'] ?? 'smooth' ),
			'direction'  => sanitize_key( $s['direction'] ?? 'horizontal' ),
			'wheel'      => ! empty( $s['wheel'] ),
		);
		$bleed = ! empty( $s['full_width'] ) ? ' pss-slider--bleed' : '';
		$peek  = ! empty( $s['peek'] ) ? ' pss-slider--peek' : '';
		echo '<div class="pss-slider pss-slider--' . esc_attr( $cfg['direction'] ) . ' pss-slider--' . esc_attr( $cfg['transition'] ) . $bleed . $peek . '" data-pss-slider="' . \PSS\esc_attr_json( $cfg ) . '" style="--pss-slides:' . esc_attr( $s['visible'] ?? 1.25 ) . ';--pss-dur:' . esc_attr( $cfg['duration'] ) . 'ms">';
		echo '<div class="pss-slider__shell">';
		if ( ! empty( $s['progress'] ) ) {
			echo '<div class="pss-slider__progress"><span></span></div>';
		}
		echo '<div class="pss-slider__viewport"><div class="pss-slider__track">' . \PSS\RenderCards::cards( $posts, $settings ) . '</div></div>';
		if ( ! empty( $s['arrows'] ) ) {
			echo '<button type="button" class="pss-slider__arrow pss-slider__arrow--prev" aria-label="Previous">←</button>';
			echo '<button type="button" class="pss-slider__arrow pss-slider__arrow--next" aria-label="Next">→</button>';
		}
		if ( ! empty( $s['dots'] ) ) {
			echo '<div class="pss-slider__dots"></div>';
		}
		echo '</div></div>';
	}

	private function safe_projects() {
		$out = array();
		try {
			foreach ( get_posts( array( 'post_type' => \PSS_PROJECT_CPT, 'post_status' => 'publish', 'posts_per_page' => 80, 'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids' ) ) as $id ) {
				$out[ absint( $id ) ] = get_the_title( $id );
			}
		} catch ( \Throwable $e ) {
			return array();
		}
		return $out;
	}
}
