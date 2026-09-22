<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Showcase extends Base {
	public function get_name() { return 'pss_project_showcase'; }
	public function get_title() { return 'Project Showcase'; }
	public function get_icon() { return 'eicon-gallery-masonry'; }

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => 'Projects', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'layout', array(
			'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'grid',
			'options' => array( 'grid'=>'Grid', 'masonry'=>'Masonry', 'bento'=>'Bento', 'carousel'=>'Carousel', 'horizontal'=>'Horizontal', 'featured'=>'Featured', 'editorial'=>'Editorial' ),
		) );
		$this->add_control( 'preset', array(
			'label' => 'Card Style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'modern',
			'options' => array( 'modern'=>'Modern', 'luxury'=>'Luxury', 'editorial'=>'Editorial', 'architectural'=>'Architectural', 'cinematic'=>'Cinematic', 'minimal'=>'Minimal', 'classic'=>'Classic', 'dark'=>'Dark', 'light'=>'Light', 'glass'=>'Glass', 'magazine'=>'Magazine', 'overlay'=>'Overlay', 'split'=>'Split', 'bento'=>'Bento', 'floating'=>'Floating', 'monochrome'=>'Monochrome', 'line'=>'Editorial Line' ),
		) );
		$this->add_control( 'animation', array(
			'label' => 'Animation', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'reveal',
			'options' => array( 'none'=>'None', 'reveal'=>'Reveal', 'lift'=>'Lift', 'zoom'=>'Image Zoom', 'parallax'=>'Cursor Parallax', 'directional'=>'Directional Hover', 'float'=>'Soft Float', 'tilt'=>'3D Tilt', 'mask'=>'Mask Reveal', 'blur'=>'Blur Reveal', 'text'=>'Text Reveal', 'magnetic'=>'Magnetic Card', 'cinematic'=>'Cinematic Hover' ),
		) );
		$this->add_control( 'meta_placement', array(
			'label' => 'Project info placement',
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'auto',
			'options' => array(
				'auto'    => 'Auto (inside overlay cards, below editorial cards)',
				'overlay' => 'Inside the card image only',
				'below'   => 'Below the image only',
				'none'    => 'Hide extra fields',
			),
			'description' => 'Never shows the same fields both on the image and under the card.',
		) );
		$this->add_control( 'card_data_mode', array(
			'label' => 'Card data', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'project',
			'options' => array( 'project'=>'Use each Project’s selected fields', 'core'=>'Use standard meta fields', 'manual'=>'Choose field keys manually' ),
		) );
		$this->add_control( 'manual_card_fields', array(
			'label' => 'Manual field keys', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'placeholder' => 'cabinet_material, countertop, ceiling_height',
			'condition' => array( 'card_data_mode' => 'manual' ),
		) );
		$this->add_control( 'show_title', array( 'label' => 'Show title', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'limit', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 9, 'min' => 1, 'max' => 100 ) );
		$this->add_control( 'orderby', array( 'label' => 'Order By', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'date', 'options' => array( 'date'=>'Date', 'title'=>'Title', 'modified'=>'Modified', 'menu_order'=>'Menu Order' ) ) );
		$this->add_control( 'order', array( 'label' => 'Order', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC'=>'Descending', 'ASC'=>'Ascending' ) ) );
		$this->add_control( 'enable_search', array( 'label' => 'Enable Search', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->end_controls_section();

		$this->start_controls_section( 'responsive', array( 'label' => 'Responsive', 'tab' => \Elementor\Controls_Manager::TAB_LAYOUT ) );
		$this->add_responsive_control( 'columns', array(
			'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER,
			'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 6,
			'selectors' => array( '{{WRAPPER}} .pss-showcase' => '--pss-cols: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'gap', array(
			'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
			'default' => array( 'size' => 22, 'unit' => 'px' ),
			'tablet_default' => array( 'size' => 16, 'unit' => 'px' ),
			'mobile_default' => array( 'size' => 12, 'unit' => 'px' ),
			'selectors' => array( '{{WRAPPER}} .pss-showcase' => '--pss-gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'image_ratio', array(
			'label' => 'Image ratio', 'type' => \Elementor\Controls_Manager::SELECT,
			'default' => '4 / 5', 'tablet_default' => '4 / 5', 'mobile_default' => '4 / 5',
			'options' => array( '1 / 1'=>'1:1', '4 / 5'=>'4:5', '3 / 4'=>'3:4', '16 / 11'=>'16:11', '16 / 9'=>'16:9', '3 / 2'=>'3:2' ),
			'selectors' => array( '{{WRAPPER}} .pss-card__media' => 'aspect-ratio: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'card_radius', array(
			'label' => 'Corner radius', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
			'selectors' => array( '{{WRAPPER}} .pss-card__media' => 'border-radius: {{SIZE}}{{UNIT}};' ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'card_style', array( 'label' => 'Card', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'title_size', array(
			'label' => 'Title size', 'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 14, 'max' => 72 ) ),
			'selectors' => array( '{{WRAPPER}} .pss-card__body h3' => 'font-size: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'title_color', array(
			'label' => 'Title color', 'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .pss-card__body h3' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'overlay_color', array(
			'label' => 'Image overlay', 'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .pss-card__veil' => 'background: linear-gradient(180deg, transparent 28%, {{VALUE}} 100%);' ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'filters', array( 'label' => 'Filters', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'filter_category', array( 'label' => 'Enable Category Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_style', array( 'label' => 'Enable Style Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_location', array( 'label' => 'Enable Location Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_type', array( 'label' => 'Enable Project Type Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_year', array( 'label' => 'Enable Year Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'load_more', array( 'label' => 'Load More', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$settings = array(
			'limit' => absint( $s['limit'] ?? 9 ),
			'page' => 1,
			'orderby' => sanitize_key( $s['orderby'] ?? 'date' ),
			'order' => sanitize_key( $s['order'] ?? 'DESC' ),
			'preset' => sanitize_key( $s['preset'] ?? 'modern' ),
			'animation' => sanitize_key( $s['animation'] ?? 'reveal' ),
			'meta_placement' => sanitize_key( $s['meta_placement'] ?? 'auto' ),
			'card_data_mode' => sanitize_key( $s['card_data_mode'] ?? 'project' ),
			'manual_card_fields' => (string) ( $s['manual_card_fields'] ?? '' ),
			'show_title' => ! empty( $s['show_title'] ),
			'allow_category' => ! empty( $s['filter_category'] ),
			'allow_style' => ! empty( $s['filter_style'] ),
			'allow_location' => ! empty( $s['filter_location'] ),
			'allow_type' => ! empty( $s['filter_type'] ),
			'allow_year' => ! empty( $s['filter_year'] ),
		);
		$posts = \PSS\Ajax::query( $settings );
		$layout = sanitize_key( $s['layout'] ?? 'grid' );
		$cols = absint( $s['columns'] ?? 3 );
		$gap = isset( $s['gap']['size'] ) ? absint( $s['gap']['size'] ) : absint( $s['gap'] ?? 22 );
		$enable_search = ! empty( $s['enable_search'] );
		echo '<div class="pss-showcase pss-showcase--' . esc_attr( $layout ) . '" data-settings="' . \PSS\esc_attr_json( $settings ) . '" data-page="1" style="--pss-cols:' . esc_attr( $cols ) . ';--pss-gap:' . esc_attr( $gap ) . 'px">';
		if ( $enable_search || $settings['allow_category'] || $settings['allow_style'] || $settings['allow_location'] || $settings['allow_type'] || $settings['allow_year'] ) {
			echo '<div class="pss-showcase__filters">';
			if ( $enable_search ) echo '<input type="search" class="pss-filter-search" placeholder="Search projects…" value="">';
			if ( $settings['allow_category'] ) echo self::term_select( 'category', 'Category' );
			if ( $settings['allow_style'] ) echo self::term_select( 'style', 'Style' );
			if ( $settings['allow_location'] ) echo self::term_select( 'location', 'Location' );
			if ( $settings['allow_type'] ) echo self::term_select( 'type', 'Project Type' );
			if ( $settings['allow_year'] ) echo self::year_select();
			echo '</div>';
		}
		echo '<div class="pss-showcase__grid">' . \PSS\RenderCards::cards( $posts, $settings ) . '</div>';
		if ( ! empty( $s['load_more'] ) ) echo '<button type="button" class="pss-load-more">Load more</button>';
		echo '</div>';
	}

	private static function term_select( $key, $label ) {
		$map = array( 'category'=>'pss_project_category', 'style'=>'pss_project_style', 'location'=>'pss_project_location', 'type'=>'pss_project_type' );
		$taxonomy = $map[ $key ] ?? '';
		$terms = $taxonomy ? get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) ) : array();
		if ( is_wp_error( $terms ) ) return '';
		$html = '<label class="pss-filter"><span>' . esc_html( $label ) . '</span><select data-pss-filter="' . esc_attr( $key ) . '"><option value="">All</option>';
		foreach ( $terms as $term ) $html .= '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
		return $html . '</select></label>';
	}

	private static function year_select() {
		$years = get_posts( array( 'post_type' => \PSS_PROJECT_CPT, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_pss_year', 'orderby' => 'meta_value_num', 'order' => 'DESC' ) );
		$unique = array();
		foreach ( $years as $id ) { $year = absint( \PSS\get_meta( $id, '_pss_year', 0 ) ); if ( $year ) $unique[ $year ] = $year; }
		ksort( $unique, SORT_NUMERIC ); $unique = array_reverse( $unique, true );
		$html = '<label class="pss-filter"><span>Year</span><select data-pss-filter="year"><option value="">All</option>';
		foreach ( $unique as $year ) $html .= '<option value="' . esc_attr( $year ) . '">' . esc_html( $year ) . '</option>';
		return $html . '</select></label>';
	}
}
