<?php
namespace PSS\Elementor\Widgets;

class Project_Products extends Base {
	public function get_name() { return 'pss_project_products'; }
	public function get_title() { return 'Project Products'; }
	public function get_icon() { return 'eicon-products'; }
	public function get_script_depends() { return array( 'pss-frontend' ); }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Products' ) );
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'grid', 'options' => array( 'grid'=>'Grid', 'carousel'=>'Carousel', 'list'=>'List' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 6, 'default' => 4, 'tablet_default' => 2, 'mobile_default' => 1 ) );
		$this->add_control( 'image_transition', array( 'label' => 'Subtle Image Transition', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_price', array( 'label' => 'Show Price', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_button', array( 'label' => 'Show Add to Cart', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_rating', array( 'label' => 'Show Rating', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_badge', array( 'label' => 'Show Sale Badge', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'image_count', array( 'label' => 'Transition Images', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 6 ) );
		$this->add_control( 'transition_mode', array( 'label' => 'Image Transition', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'hover', 'options' => array( 'hover'=>'Hover', 'auto'=>'Auto', 'hover_or_auto'=>'Hover + Auto' ) ) );
		$this->add_control( 'transition_speed', array( 'label' => 'Transition Speed (ms)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 550, 'min' => 150, 'max' => 3000 ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		if ( ! $id || ! class_exists( 'WooCommerce' ) ) return;
		$products = \PSS\WooCommerce::get_products( $id );
		if ( ! $products ) return;
		$cols = max( 1, absint( $s['columns'] ?? 4 ) );
		$layout = sanitize_key( $s['layout'] ?? 'grid' );
		echo '<div class="pss-products-grid pss-products-grid--' . esc_attr( $layout ) . '" style="--pss-cols:' . esc_attr( $cols ) . '">';
		foreach ( $products as $product ) {
			$images = $product->get_gallery_image_ids();
			array_unshift( $images, $product->get_image_id() );
			$images = array_values( array_unique( array_filter( array_map( 'absint', $images ) ) ) );
			$images = array_slice( $images, 0, max( 1, min( 6, absint( $s['image_count'] ?? 3 ) ) ) );
			$main = isset( $images[0] ) ? wp_get_attachment_image_url( $images[0], 'woocommerce_thumbnail' ) : '';
			echo '<article class="pss-product-card' . ( 'yes' === ( $s['image_transition'] ?? '' ) ? ' pss-product-card--slide' : '' ) . ' pss-product-card--transition-' . esc_attr( sanitize_key( $s['transition_mode'] ?? 'hover' ) ) . '" data-transition-speed="' . esc_attr( absint( $s['transition_speed'] ?? 550 ) ) . '" style="--pss-product-speed:' . esc_attr( absint( $s['transition_speed'] ?? 550 ) ) . 'ms;">';
			echo '<a href="' . esc_url( $product->get_permalink() ) . '" class="pss-product-card__media">';
			if ( 'yes' === ( $s['image_transition'] ?? '' ) ) {
				foreach ( $images as $idx => $image_id ) {
					$img = wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' );
					if ( ! $img ) continue;
					echo '<img class="pss-product-card__img pss-product-card__slide" data-index="' . esc_attr( $idx ) . '" src="' . esc_url( $img ) . '" alt="' . ( 0 === $idx ? esc_attr( $product->get_name() ) : '' ) . '"' . ( 0 === $idx ? '' : ' aria-hidden="true"' ) . '>';
				}
			} elseif ( $main ) {
				echo '<img class="pss-product-card__img pss-product-card__img--main" src="' . esc_url( $main ) . '" alt="' . esc_attr( $product->get_name() ) . '">';
			}
			if ( 'yes' === ( $s['show_badge'] ?? '' ) && $product->is_on_sale() ) echo '<span class="pss-product-card__badge">Sale</span>';
			if ( count( $images ) > 1 && 'yes' === ( $s['image_transition'] ?? '' ) ) {
				echo '<div class="pss-product-card__dots">';
				foreach ( $images as $idx => $image_id ) echo '<span data-index="' . esc_attr( $idx ) . '"' . ( 0 === $idx ? ' class="is-active"' : '' ) . '></span>';
				echo '</div>';
			}
			echo '</a><div class="pss-product-card__body"><h3>' . esc_html( $product->get_name() ) . '</h3>';
			if ( 'yes' === ( $s['show_rating'] ?? '' ) && $product->get_average_rating() ) echo '<div class="pss-product-card__rating">' . wp_kses_post( wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ) ) . '</div>';
			if ( 'yes' === ( $s['show_price'] ?? '' ) ) echo '<div class="pss-product-card__price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
			if ( 'yes' === ( $s['show_button'] ?? '' ) ) echo '<a class="pss-product-card__button" href="' . esc_url( $product->add_to_cart_url() ) . '">Add to cart</a>';
			echo '</div></article>';
		}
		echo '</div>';
	}
}
