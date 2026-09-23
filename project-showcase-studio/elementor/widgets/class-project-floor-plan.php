<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Floor_Plan extends Base {
	public function get_name() { return 'pss_project_floor_plan'; }
	public function get_title() { return 'Project Floor Plan'; }
	public function get_icon() { return 'eicon-image-hotspot'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Floor Plan' ) );
		$this->add_control( 'image_size', array( 'label' => 'Image Size', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'full', 'options' => array( 'medium_large'=>'Medium Large', 'large'=>'Large', 'full'=>'Full' ) ) );
		$this->add_control( 'lightbox', array( 'label' => 'Lightbox', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		if ( ! $id ) {
			$this->empty_state( 'Project Floor Plan', 'Choose a preview project in Single Layout settings.' );
			return;
		}
		$image_id = absint( \PSS\get_meta( $id, '_pss_floor_plan', 0 ) );
		if ( ! $image_id ) {
			$maybe = \PSS\get_field_value( $id, 'floor_plan', 0 );
			$image_id = absint( is_array( $maybe ) ? ( $maybe['id'] ?? 0 ) : $maybe );
		}
		$url = $image_id ? wp_get_attachment_image_url( $image_id, $s['image_size'] ?? 'full' ) : '';
		$full = $image_id ? ( wp_get_attachment_image_url( $image_id, 'full' ) ?: $url ) : '';
		if ( ! $url ) {
			$this->empty_state( 'Project Floor Plan', 'Upload a floor plan on the project (Floor Plan meta or a floor_plan image field).' );
			return;
		}
		if ( 'yes' === ( $s['lightbox'] ?? '' ) ) {
			echo '<a class="pss-floor-plan pss-lightbox-link" href="' . esc_url( $full ) . '"><img src="' . esc_url( $url ) . '" alt=""></a>';
		} else {
			echo '<div class="pss-floor-plan"><img src="' . esc_url( $url ) . '" alt=""></div>';
		}
	}
}
