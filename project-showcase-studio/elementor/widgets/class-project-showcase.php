<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Showcase extends Base {
	public function get_name() { return 'pss_project_showcase'; }
	public function get_title() { return 'Project Showcase'; }
	public function get_icon() { return 'eicon-gallery-masonry'; }
	public function get_script_depends() { return array( 'pss-frontend' ); }

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => 'Projects', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'content_source', array( 'label' => 'Source', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'project', 'options' => array( 'project' => 'Dynamic projects', 'manual' => 'Manual project list' ) ) );
		$this->add_control( 'layout', array(
			'label' => 'Composition engine', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'grid',
			'options' => array(
				'grid' => 'Classic Grid', 'editorial' => 'Editorial Grid', 'masonry' => 'Masonry', 'bento' => 'Bento',
				'asymmetric' => 'Asymmetric', 'luxury' => 'Luxury', 'cinematic' => 'Cinematic', 'architectural' => 'Architectural',
				'magazine' => 'Magazine', 'fullimage' => 'Full Image', 'fullscreen' => 'Fullscreen', 'overlay' => 'Overlay',
				'split' => 'Split', 'floating' => 'Floating', 'stack' => 'Stacked', 'overlapping' => 'Overlapping',
				'dossier' => 'Dossier', 'minimal' => 'Minimal', 'interactive' => 'Interactive', 'perspective' => 'Perspective / 3D',
				'vstory' => 'Vertical Story', 'horizontal' => 'Horizontal Story',
			),
		) );
		$this->add_control( 'preset', array(
			'label' => 'Card composition', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'modern',
			'options' => array(
				'modern'=>'Classic stack', 'luxury'=>'Luxury caption', 'editorial'=>'Editorial split', 'architectural'=>'Index split',
				'cinematic'=>'Cinematic overlay', 'minimal'=>'Minimal caption', 'classic'=>'Classic', 'magazine'=>'Magazine side caption',
				'overlay'=>'Overlay', 'split'=>'Split', 'floating'=>'Floating panel', 'asymmetric'=>'Asymmetric reverse',
				'fullimage'=>'Full image', 'fullscreen'=>'Fullscreen', 'interactive'=>'Hover reveal', 'dossier'=>'Dossier',
				'stacked'=>'Stacked', 'overlapping'=>'Overlapping', 'perspective'=>'3D / Perspective',
				'flip'=>'Flip / 3D card', 'follow'=>'Image follow', 'expanding'=>'Expanding card', 'magnetic'=>'Magnetic card',
				'story'=>'Editorial story', 'hover_reveal'=>'Hover reveal card',
			),
		) );
		$this->add_control( 'animation', array(
			'label' => 'Motion', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'reveal',
			'options' => array(
				'none'=>'None', 'reveal'=>'Scroll reveal', 'stagger'=>'Stagger', 'lift'=>'Lift', 'zoom'=>'Image Zoom',
				'parallax'=>'Cursor Parallax', 'directional'=>'Directional Hover', 'float'=>'Soft Float', 'tilt'=>'3D Tilt',
				'mask'=>'Mask Reveal', 'blur'=>'Blur Reveal', 'text'=>'Text Reveal', 'magnetic'=>'Magnetic Card',
				'cinematic'=>'Cinematic Hover', 'pan'=>'Image pan', 'clip'=>'Clip reveal', 'follow'=>'Cursor follow',
			),
		) );
		$this->add_control( 'limit', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 9, 'min' => 1, 'max' => 100, 'condition' => array( 'content_source' => 'project' ) ) );
		$this->add_control( 'orderby', array( 'label' => 'Order By', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'date', 'options' => array( 'date'=>'Date', 'title'=>'Title', 'modified'=>'Modified', 'menu_order'=>'Menu Order' ), 'condition' => array( 'content_source' => 'project' ) ) );
		$this->add_control( 'order', array( 'label' => 'Order', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC'=>'Descending', 'ASC'=>'Ascending' ), 'condition' => array( 'content_source' => 'project' ) ) );
		if ( class_exists( '\\Elementor\\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'project_id', array( 'label' => 'Project', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => $this->safe_projects(), 'label_block' => true ) );
			$this->add_control( 'manual_projects', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => 'Project', 'condition' => array( 'content_source' => 'manual' ), 'prevent_empty' => false ) );
		}
		$this->add_control( 'enable_search', array( 'label' => 'Enable Search', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->end_controls_section();

		$this->start_controls_section( 'builder', array( 'label' => 'Card builder', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'title_placement', array( 'label' => 'Title placement', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'auto', 'options' => $this->places(), 'condition' => array( 'show_title' => 'yes' ) ) );
		$this->add_control( 'show_subtitle', array( 'label' => 'Subtitle', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'subtitle_placement', array( 'label' => 'Subtitle placement', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'below', 'options' => $this->places(), 'condition' => array( 'show_subtitle' => 'yes' ) ) );
		$this->add_control( 'meta_placement', array( 'label' => 'Metadata placement', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'auto', 'options' => $this->places(), 'description' => 'Never prints the same fields both on the image and under the card.' ) );
		$this->add_control( 'index_placement', array( 'label' => 'Index', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'overlay', 'options' => array( 'overlay'=>'On the image', 'below'=>'Below', 'hidden'=>'Hidden' ) ) );
		$this->add_control( 'show_badge', array( 'label' => 'Badge', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'cta_placement', array( 'label' => 'CTA', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'hidden', 'options' => array( 'hidden'=>'Hidden', 'overlay'=>'On the image', 'below'=>'Below', 'floating'=>'Floating', 'hover'=>'Hover only' ) ) );
		$this->add_control( 'cta_label', array( 'label' => 'CTA label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View project', 'condition' => array( 'cta_placement!' => 'hidden' ) ) );
		$this->add_control( 'card_data_mode', array( 'label' => 'Card data', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'project', 'options' => array( 'project'=>'Use each Project’s selected fields', 'core'=>'Use standard meta fields', 'manual'=>'Choose field keys' ) ) );
		$this->add_control( 'manual_card_fields', array( 'label' => 'Field keys', 'type' => \Elementor\Controls_Manager::SELECT2, 'multiple' => true, 'options' => self::field_options(), 'condition' => array( 'card_data_mode' => 'manual' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'responsive', array( 'label' => 'Responsive', 'tab' => \Elementor\Controls_Manager::TAB_LAYOUT ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 6, 'selectors' => array( '{{WRAPPER}} .pss-showcase' => '--pss-cols: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 120 ) ), 'default' => array( 'size' => 22, 'unit' => 'px' ), 'tablet_default' => array( 'size' => 16, 'unit' => 'px' ), 'mobile_default' => array( 'size' => 12, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .pss-showcase' => '--pss-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'image_ratio', array( 'label' => 'Image ratio', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => '4 / 5', 'tablet_default' => '4 / 5', 'mobile_default' => '4 / 5', 'options' => array( '1 / 1'=>'1:1', '4 / 5'=>'4:5', '3 / 4'=>'3:4', '16 / 11'=>'16:11', '16 / 9'=>'16:9', '3 / 2'=>'3:2' ), 'selectors' => array( '{{WRAPPER}} .pss-card__media' => 'aspect-ratio: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'image_height', array( 'label' => 'Image height', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 120, 'max' => 900 ), 'vh' => array( 'min' => 20, 'max' => 90 ) ), 'selectors' => array( '{{WRAPPER}} .pss-card__media' => 'height: {{SIZE}}{{UNIT}}; aspect-ratio: auto;' ) ) );
		$this->add_responsive_control( 'card_radius', array( 'label' => 'Corner radius', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 48 ) ), 'selectors' => array( '{{WRAPPER}} .pss-card__media' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'card_style', array( 'label' => 'Card', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'title_size', array( 'label' => 'Title size', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 14, 'max' => 72 ) ), 'selectors' => array( '{{WRAPPER}} .pss-card__title' => 'font-size: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'title_color', array( 'label' => 'Title color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-card__title' => 'color: {{VALUE}};' ) ) );
		$this->add_typography( 'title_typo', '{{WRAPPER}} .pss-card__title' );
		$this->add_control( 'overlay_color', array( 'label' => 'Image overlay', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-card__veil' => 'background: linear-gradient(180deg, transparent 28%, {{VALUE}} 100%);' ) ) );
		$this->add_control( 'cta_color', array( 'label' => 'CTA', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-card__cta' => 'color: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'content_align', array( 'label' => 'Content align', 'type' => \Elementor\Controls_Manager::CHOOSE, 'options' => array( 'left'=>array( 'title'=>'Left', 'icon'=>'eicon-text-align-left' ), 'center'=>array( 'title'=>'Center', 'icon'=>'eicon-text-align-center' ), 'right'=>array( 'title'=>'Right', 'icon'=>'eicon-text-align-right' ) ), 'selectors' => array( '{{WRAPPER}} .pss-card__body, {{WRAPPER}} .pss-card__overlay' => 'text-align: {{VALUE}};' ) ) );
		$this->end_controls_section();
		$this->add_motion_vars( '{{WRAPPER}} .pss-showcase' );

		$this->start_controls_section( 'filters', array( 'label' => 'Filters', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'filter_category', array( 'label' => 'Enable Category Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_style', array( 'label' => 'Enable Style Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_location', array( 'label' => 'Enable Location Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_type', array( 'label' => 'Enable Project Type Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'filter_year', array( 'label' => 'Enable Year Filter', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->add_control( 'load_more', array( 'label' => 'Load More', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->end_controls_section();
	}

	private function places() {
		return array( 'auto'=>'Auto for this composition', 'overlay'=>'On the image', 'top'=>'Top overlay', 'below'=>'Below the image', 'floating'=>'Floating', 'hover'=>'Hover only', 'hidden'=>'Hidden' );
	}

	private function safe_projects() {
		try { return self::project_options_public(); } catch ( \Throwable $e ) { return array(); }
	}

	private static function project_options_public() {
		$out = array();
		foreach ( get_posts( array( 'post_type' => \PSS_PROJECT_CPT, 'post_status' => 'publish', 'posts_per_page' => 80, 'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids' ) ) as $id ) {
			$out[ absint( $id ) ] = get_the_title( $id );
		}
		return $out;
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$manual_keys = $s['manual_card_fields'] ?? array();
		if ( is_array( $manual_keys ) ) { $manual_keys = implode( "\n", $manual_keys ); }
		$settings = array(
			'limit' => absint( $s['limit'] ?? 9 ),
			'page' => 1,
			'orderby' => sanitize_key( $s['orderby'] ?? 'date' ),
			'order' => sanitize_key( $s['order'] ?? 'DESC' ),
			'preset' => sanitize_key( $s['preset'] ?? 'modern' ),
			'animation' => sanitize_key( $s['animation'] ?? 'reveal' ),
			'title_placement' => sanitize_key( $s['title_placement'] ?? 'auto' ),
			'subtitle_placement' => sanitize_key( $s['subtitle_placement'] ?? 'hidden' ),
			'meta_placement' => sanitize_key( $s['meta_placement'] ?? 'auto' ),
			'index_placement' => sanitize_key( $s['index_placement'] ?? 'overlay' ),
			'cta_placement' => sanitize_key( $s['cta_placement'] ?? 'hidden' ),
			'cta_label' => (string) ( $s['cta_label'] ?? 'View project' ),
			'card_data_mode' => sanitize_key( $s['card_data_mode'] ?? 'project' ),
			'manual_card_fields' => (string) $manual_keys,
			'show_title' => ! empty( $s['show_title'] ),
			'show_image' => ! empty( $s['show_image'] ),
			'show_subtitle' => ! empty( $s['show_subtitle'] ),
			'show_badge' => ! empty( $s['show_badge'] ),
			'allow_category' => ! empty( $s['filter_category'] ),
			'allow_style' => ! empty( $s['filter_style'] ),
			'allow_location' => ! empty( $s['filter_location'] ),
			'allow_type' => ! empty( $s['filter_type'] ),
			'allow_year' => ! empty( $s['filter_year'] ),
		);
		if ( 'manual' === ( $s['content_source'] ?? '' ) ) {
			$posts = array();
			foreach ( (array) ( $s['manual_projects'] ?? array() ) as $row ) {
				$id = absint( $row['project_id'] ?? 0 );
				if ( $id ) { $posts[] = get_post( $id ); }
			}
			$posts = array_filter( $posts );
		} else {
			$posts = \PSS\Ajax::query( $settings );
		}
		$layout = sanitize_key( $s['layout'] ?? 'grid' );
		$enable_search = ! empty( $s['enable_search'] );
		echo '<div class="pss-showcase pss-showcase--' . esc_attr( $layout ) . '" data-settings="' . \PSS\esc_attr_json( $settings ) . '" data-page="1">';
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
		$inner = \PSS\RenderCards::cards( $posts, $settings );
		if ( in_array( $layout, array( 'horizontal', 'cinematic' ), true ) ) {
			echo '<div class="pss-engine pss-engine--hscroll"><div class="pss-engine__track pss-showcase__grid">' . $inner . '</div></div>';
		} elseif ( 'vstory' === $layout ) {
			echo '<div class="pss-engine pss-engine--vstory pss-showcase__grid">' . $inner . '</div>';
		} elseif ( 'bento' === $layout ) {
			echo '<div class="pss-engine pss-engine--bento pss-showcase__grid">' . $inner . '</div>';
		} elseif ( 'masonry' === $layout ) {
			echo '<div class="pss-engine pss-engine--masonry pss-showcase__grid">' . $inner . '</div>';
		} elseif ( 'magazine' === $layout ) {
			echo '<div class="pss-engine pss-engine--magazine pss-showcase__grid">' . $inner . '</div>';
		} elseif ( 'fullscreen' === $layout ) {
			echo '<div class="pss-engine pss-engine--fullscreen pss-showcase__grid">' . $inner . '</div>';
		} else {
			echo '<div class="pss-showcase__grid">' . $inner . '</div>';
		}
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
