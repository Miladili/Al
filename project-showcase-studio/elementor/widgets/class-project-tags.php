<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Tags extends Base {
	public function get_name() { return 'pss_project_tags'; }
	public function get_title() { return 'Project Tags'; }
	public function get_icon() { return 'eicon-tags'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Tags', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'taxonomies', array( 'label' => 'Taxonomies', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'all', 'options' => array( 'all' => 'All Project Taxonomies', 'category' => 'Category', 'style' => 'Style', 'location' => 'Location', 'type' => 'Project Type' ) ) );
		$this->add_control( 'manual_tags', array( 'label' => 'Manual tags (one per line)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'animation', array( 'label' => 'Animation', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'soft', 'options' => array( 'none' => 'None', 'soft' => 'Soft', 'lift' => 'Lift' ) ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-tags' );
		$this->add_text_style( 'tag', 'Tags', '{{WRAPPER}} .pss-project-tags span' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$terms    = array();
		if ( $this->is_manual( $settings ) ) {
			$terms = array_filter( array_map( 'trim', preg_split( '/\\r\\n|\\r|\\n|,/', (string) ( $settings['manual_tags'] ?? '' ) ) ) );
		} else {
			$project_id = $this->project_id( $settings );
			if ( ! $project_id ) {
				$this->empty_state( 'Project Tags', 'Choose a preview project in Single Layout settings, or switch this widget to Manual content.' );
				return;
			}
			$map      = array( 'category' => 'pss_project_category', 'style' => 'pss_project_style', 'location' => 'pss_project_location', 'type' => 'pss_project_type' );
			$selected = sanitize_key( $settings['taxonomies'] ?? 'all' );
			$taxes    = 'all' === $selected ? $map : array( $selected => $map[ $selected ] ?? '' );
			foreach ( $taxes as $taxonomy ) {
				if ( ! $taxonomy ) { continue; }
				$list = get_the_terms( $project_id, $taxonomy );
				if ( empty( $list ) || is_wp_error( $list ) ) { continue; }
				foreach ( $list as $term ) {
					$terms[] = $term->name;
				}
			}
			$terms = array_values( array_unique( $terms ) );
		}
		if ( ! $terms ) {
			$this->empty_state( 'Project Tags', 'Assign Type, Style, Location or Category on the project.' );
			return;
		}
		$animation = sanitize_key( $settings['animation'] ?? 'soft' );
		echo '<div class="pss-project-tags pss-project-tags--anim-' . esc_attr( $animation ) . '">';
		foreach ( $terms as $term ) {
			echo '<span>' . esc_html( $term ) . '</span>';
		}
		echo '</div>';
	}
}
