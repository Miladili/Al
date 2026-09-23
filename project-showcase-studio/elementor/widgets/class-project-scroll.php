<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Scroll extends Base {
	public function get_name() {
		return 'pss_project_scroll';
	}
	public function get_title() {
		return 'Project Horizontal Scroll';
	}
	public function get_icon() {
		return 'eicon-slider-push';
	}
	public function get_script_depends() {
		return array( 'pss-gsap', 'pss-scrolltrigger', 'pss-frontend' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => 'Projects', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'content_source', array( 'label' => 'Source', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'project', 'options' => array( 'project' => 'Dynamic projects', 'manual' => 'Manual project list' ) ) );
		$this->add_control( 'limit', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 6, 'min' => 2, 'max' => 16, 'condition' => array( 'content_source' => 'project' ) ) );
		if ( class_exists( '\\Elementor\\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'project_id', array( 'label' => 'Project', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => $this->safe_projects(), 'label_block' => true ) );
			$this->add_control( 'manual_projects', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => 'Project', 'condition' => array( 'content_source' => 'manual' ), 'prevent_empty' => false ) );
		}
		$this->add_control( 'orderby', array( 'label' => 'Order By', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'date', 'options' => array( 'date'=>'Date', 'title'=>'Title', 'modified'=>'Modified', 'menu_order'=>'Menu Order' ) ) );
		$this->add_control( 'order', array( 'label' => 'Order', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC'=>'Descending', 'ASC'=>'Ascending' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => 'Eyebrow', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Selected work' ) );
		$this->add_control( 'cta_label', array( 'label' => 'Panel CTA', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View project' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'motion', array( 'label' => 'Scroll motion', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'direction', array( 'label' => 'Direction', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'ltr', 'options' => array( 'ltr' => 'Left to right', 'rtl' => 'Right to left' ) ) );
		$this->add_control( 'easing', array( 'label' => 'Easing', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'smooth', 'options' => array( 'linear' => 'Linear', 'smooth' => 'Smooth', 'cinematic' => 'Cinematic' ) ) );
		$this->add_control( 'speed', array( 'label' => 'Speed', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 1, 'min' => 0.4, 'max' => 2.4, 'step' => 0.1 ) );
		$this->add_control( 'pin_duration', array( 'label' => 'Pin duration', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 1, 'min' => 0.6, 'max' => 3, 'step' => 0.1, 'description' => 'Higher values keep the section pinned longer while panels travel horizontally.' ) );
		$this->add_control( 'scroll_distance', array( 'label' => 'Extra scroll distance (vh)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 180, 'min' => 80, 'max' => 420 ) );
		$this->add_control( 'image_behavior', array( 'label' => 'Image behavior', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'zoom', 'options' => array( 'none' => 'None', 'zoom' => 'Slow zoom', 'pan' => 'Pan', 'kenburns' => 'Ken Burns' ) ) );
		$this->add_control( 'text_animation', array( 'label' => 'Text animation', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'rise', 'options' => array( 'none' => 'None', 'rise' => 'Rise', 'fade' => 'Fade', 'mask' => 'Mask' ) ) );
		$this->add_control( 'transition_style', array( 'label' => 'Transition style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'slide', 'options' => array( 'slide' => 'Slide', 'overlap' => 'Overlap', 'scale' => 'Scale' ) ) );
		$this->add_control( 'mobile_behavior', array( 'label' => 'Mobile behavior', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'stack', 'options' => array( 'stack' => 'Stack vertically', 'swipe' => 'Keep horizontal swipe' ) ) );
		$this->add_control( 'snap', array( 'label' => 'Snap to panels', 'type' => \Elementor\Controls_Manager::SWITCHER, 'description' => 'Ease toward the nearest panel while scrolling, similar to a GSAP snap.' ) );
		$this->add_control( 'scrub', array( 'label' => 'Scrub smoothness', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0.18, 'min' => 0, 'max' => 0.6, 'step' => 0.02, 'description' => '0 is locked to scroll. Higher values lag the track like GSAP scrub.' ) );
		$this->add_control( 'reduced_motion', array( 'label' => 'Honor reduced motion', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_progress', array( 'label' => 'Progress bar', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout', array( 'label' => 'Panel layout', 'tab' => $this->layout_tab() ) );
		$this->add_responsive_control( 'visible_panels', array(
			'label' => 'Visible panels', 'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 1.35, 'tablet_default' => 1.15, 'mobile_default' => 1, 'min' => 1, 'max' => 3, 'step' => 0.05,
			'selectors' => array( '{{WRAPPER}} .pss-scroll' => '--pss-visible: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'panel_width', array(
			'label' => 'Panel width', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( '%', 'vw', 'px' ),
			'range' => array( '%' => array( 'min' => 40, 'max' => 100 ), 'vw' => array( 'min' => 40, 'max' => 100 ), 'px' => array( 'min' => 260, 'max' => 1400 ) ),
			'default' => array( 'unit' => '%', 'size' => 78 ),
			'tablet_default' => array( 'unit' => '%', 'size' => 88 ),
			'mobile_default' => array( 'unit' => '%', 'size' => 100 ),
			'selectors' => array( '{{WRAPPER}} .pss-scroll__panel' => 'flex-basis: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'spacing', array(
			'label' => 'Spacing', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'default' => array( 'size' => 28, 'unit' => 'px' ),
			'selectors' => array( '{{WRAPPER}} .pss-scroll__track' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'section_height', array(
			'label' => 'Pinned height', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'vh', 'px' ),
			'range' => array( 'vh' => array( 'min' => 60, 'max' => 100 ), 'px' => array( 'min' => 420, 'max' => 1200 ) ),
			'default' => array( 'unit' => 'vh', 'size' => 100 ),
			'selectors' => array( '{{WRAPPER}} .pss-scroll__pin' => 'min-height: {{SIZE}}{{UNIT}};' ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'panel_bg', array( 'label' => 'Panel background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-scroll__panel' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'title_color', array( 'label' => 'Title color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-scroll__copy h2' => 'color: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'title_size', array(
			'label' => 'Title size', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 22, 'max' => 96 ) ),
			'selectors' => array( '{{WRAPPER}} .pss-scroll__copy h2' => 'font-size: {{SIZE}}{{UNIT}};' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'manual' === ( $s['content_source'] ?? '' ) ) {
			$posts = array();
			foreach ( (array) ( $s['manual_projects'] ?? array() ) as $row ) {
				$id = absint( $row['project_id'] ?? 0 );
				if ( $id ) { $posts[] = get_post( $id ); }
			}
			$posts = array_filter( $posts );
		} else {
			$posts = \PSS\Ajax::query( array(
				'limit'   => absint( $s['limit'] ?? 6 ),
				'orderby' => sanitize_key( $s['orderby'] ?? 'date' ),
				'order'   => sanitize_key( $s['order'] ?? 'DESC' ),
			) );
		}
		if ( ! $posts ) {
			return;
		}
		$cfg = array(
			'direction'       => sanitize_key( $s['direction'] ?? 'ltr' ),
			'easing'          => sanitize_key( $s['easing'] ?? 'smooth' ),
			'speed'           => (float) ( $s['speed'] ?? 1 ),
			'pin'             => (float) ( $s['pin_duration'] ?? 1 ),
			'distance'        => absint( $s['scroll_distance'] ?? 180 ),
			'mobile'          => sanitize_key( $s['mobile_behavior'] ?? 'stack' ),
			'snap'            => ! empty( $s['snap'] ),
			'scrub'           => (float) ( $s['scrub'] ?? 0.18 ),
			'reduced'         => ! empty( $s['reduced_motion'] ),
		);
		echo '<div class="pss-scroll pss-scroll--' . esc_attr( $cfg['easing'] ) . ' pss-scroll--img-' . esc_attr( sanitize_key( $s['image_behavior'] ?? 'zoom' ) ) . ' pss-scroll--text-' . esc_attr( sanitize_key( $s['text_animation'] ?? 'rise' ) ) . ' pss-scroll--' . esc_attr( sanitize_key( $s['transition_style'] ?? 'slide' ) ) . ' pss-scroll--mobile-' . esc_attr( $cfg['mobile'] ) . '" data-pss-scroll="' . \PSS\esc_attr_json( $cfg ) . '">';
		echo '<div class="pss-scroll__pin">';
		if ( ! empty( $s['show_progress'] ) ) {
			echo '<div class="pss-scroll__progress" aria-hidden="true"><span></span></div>';
		}
		if ( ! empty( $s['eyebrow'] ) ) {
			echo '<span class="pss-scroll__kicker">' . esc_html( $s['eyebrow'] ) . '</span>';
		}
		echo '<div class="pss-scroll__viewport"><div class="pss-scroll__track">';
		foreach ( $posts as $i => $project ) {
			$id    = $project->ID;
			$image = get_the_post_thumbnail_url( $id, 'full' );
			if ( ! $image ) {
				$gallery = \PSS\get_gallery_ids( $id );
				$image   = $gallery ? wp_get_attachment_image_url( $gallery[0], 'full' ) : '';
			}
			$meta = array_filter( array(
				\PSS\get_project_taxonomy_value( $id, 'pss_project_type' ),
				\PSS\get_project_taxonomy_value( $id, 'pss_project_location' ),
				\PSS\get_meta( $id, '_pss_year' ),
			) );
			$meta = array_values( array_filter( $meta, function( $item ) { return ! \PSS\is_placeholder_text( $item ); } ) );
			echo '<article class="pss-scroll__panel">';
			echo '<div class="pss-scroll__media">';
			if ( $image ) {
				echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( get_the_title( $id ) ) . '">';
			}
			echo '<span class="pss-scroll__index">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span>';
			echo '</div><div class="pss-scroll__copy">';
			if ( $meta ) {
				echo '<span class="pss-scroll__meta">' . esc_html( implode( ' · ', $meta ) ) . '</span>';
			}
			echo '<h2>' . esc_html( get_the_title( $id ) ) . '</h2>';
			$excerpt = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 22 );
			if ( $excerpt ) {
				echo '<p>' . esc_html( $excerpt ) . '</p>';
			}
			echo '<a class="pss-scroll__cta" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( $s['cta_label'] ?: 'View project' ) . '<span>↗</span></a>';
			echo '</div></article>';
		}
		echo '</div></div></div></div>';
	}
}
