<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Navigation extends Base {
	public function get_name() { return 'pss_project_navigation'; }
	public function get_title() { return 'Project Navigation'; }
	public function get_icon() { return 'eicon-post-navigation'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Labels' ) );
		$this->add_control( 'prev_label', array( 'label' => 'Previous label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Previous' ) );
		$this->add_control( 'next_label', array( 'label' => 'Next label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Next' ) );
		$this->add_control( 'back_label', array( 'label' => 'Archive label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'All Projects' ) );
		$this->add_control( 'show_titles', array( 'label' => 'Show titles', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-nav' );
		$this->add_text_style( 'nav', 'Links', '{{WRAPPER}} .pss-project-nav a' );
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		if ( ! $id ) { return; }
		$current          = $GLOBALS['post'] ?? null;
		$GLOBALS['post']  = get_post( $id );
		if ( $GLOBALS['post'] ) {
			setup_postdata( $GLOBALS['post'] );
		}
		$prev = get_previous_post( false, '', 'pss_project_type' );
		if ( ! $prev ) { $prev = get_previous_post(); }
		$next = get_next_post( false, '', 'pss_project_type' );
		if ( ! $next ) { $next = get_next_post(); }
		wp_reset_postdata();
		$GLOBALS['post'] = $current;
		echo '<nav class="pss-project-nav">';
		if ( $prev ) {
			echo '<a href="' . esc_url( get_permalink( $prev ) ) . '"><span>' . esc_html( $s['prev_label'] ?? 'Previous' ) . '</span>';
			if ( ! empty( $s['show_titles'] ) ) {
				echo '<strong>' . esc_html( get_the_title( $prev ) ) . '</strong>';
			}
			echo '</a>';
		} else {
			echo '<span></span>';
		}
		$archive = get_post_type_archive_link( \PSS_PROJECT_CPT );
		echo '<a class="pss-project-nav__back" href="' . esc_url( $archive ?: home_url( '/' ) ) . '">' . esc_html( $s['back_label'] ?? 'All Projects' ) . '</a>';
		if ( $next ) {
			echo '<a href="' . esc_url( get_permalink( $next ) ) . '" class="pss-project-nav__next"><span>' . esc_html( $s['next_label'] ?? 'Next' ) . '</span>';
			if ( ! empty( $s['show_titles'] ) ) {
				echo '<strong>' . esc_html( get_the_title( $next ) ) . '</strong>';
			}
			echo '</a>';
		} else {
			echo '<span></span>';
		}
		echo '</nav>';
	}
}
