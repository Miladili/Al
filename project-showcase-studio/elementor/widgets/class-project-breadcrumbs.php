<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Breadcrumbs extends Base {
	public function get_name() { return 'pss_project_breadcrumbs'; }
	public function get_title() { return 'Project Breadcrumbs'; }
	public function get_icon() { return 'eicon-product-breadcrumbs'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Breadcrumbs', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'home_label', array( 'label' => 'Home label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Home' ) );
		$this->add_control( 'projects_label', array( 'label' => 'Projects label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Projects' ) );
		$this->add_control( 'separator', array( 'label' => 'Separator', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/' ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-breadcrumbs' );
		$this->add_text_style( 'crumb', 'Links', '{{WRAPPER}} .pss-project-breadcrumbs a' );
		$this->add_text_style( 'current', 'Current', '{{WRAPPER}} .pss-project-breadcrumbs strong' );
	}

	protected function render() {
		$id = \PSS\get_project_id();
		if ( ! $id ) { return; }
		$s   = $this->get_settings_for_display();
		$sep = $s['separator'] ?? '/';
		echo '<nav class="pss-project-breadcrumbs" aria-label="Breadcrumb">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $s['home_label'] ?? 'Home' ) . '</a>';
		echo '<span class="pss-project-breadcrumbs__sep"> ' . esc_html( $sep ) . ' </span>';
		$archive = get_post_type_archive_link( \PSS_PROJECT_CPT );
		if ( $archive ) {
			echo '<a href="' . esc_url( $archive ) . '">' . esc_html( $s['projects_label'] ?? 'Projects' ) . '</a>';
			echo '<span class="pss-project-breadcrumbs__sep"> ' . esc_html( $sep ) . ' </span>';
		}
		echo '<strong>' . esc_html( get_the_title( $id ) ) . '</strong></nav>';
	}
}
