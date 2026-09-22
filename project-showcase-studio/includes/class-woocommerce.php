<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

class WooCommerce {
	public static function init() {
		add_action( 'save_post_' . PSS_PROJECT_CPT, array( __CLASS__, 'save_related_products' ), 20 );
	}

	public static function render_project_products( $project_id ) {
		self::render_product_selector( $project_id );
	}

	public static function render_product_selector( $project_id ) {
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_products' ) ) {
			echo '<p>WooCommerce is not active. Projects will still work without it.</p>';
			return;
		}
		$selected = get_meta( $project_id, '_pss_related_products', array() );
		if ( ! is_array( $selected ) ) $selected = array();
		$products = wc_get_products( array( 'limit' => 100, 'status' => array( 'publish', 'private' ), 'return' => 'objects' ) );
		echo '<p class="description">Select products related to this project.</p><select name="pss_related_products[]" multiple size="8" class="widefat">';
		foreach ( $products as $product ) {
			echo '<option value="' . esc_attr( $product->get_id() ) . '" ' . selected( in_array( $product->get_id(), $selected, true ), true, false ) . '>' . esc_html( $product->get_name() ) . '</option>';
		}
		echo '</select>';
	}

	public static function save_related_products( $post_id ) {
		if ( ! isset( $_POST['pss_project_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pss_project_nonce'] ) ), 'pss_save_project' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		$ids = isset( $_POST['pss_related_products'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['pss_related_products'] ) ) : array();
		$ids = array_values( array_filter( $ids ) );
		update_post_meta( $post_id, '_pss_related_products', $ids );
	}

	public static function get_products( $project_id ) {
		$ids = get_meta( $project_id, '_pss_related_products', array() );
		if ( ! is_array( $ids ) || ! function_exists( 'wc_get_product' ) ) return array();
		return array_values( array_filter( array_map( 'wc_get_product', $ids ) ) );
	}
}
