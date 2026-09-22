<?php
namespace PSS\Elementor\Widgets;

class Project_Description extends Base {
	public function get_name() { return 'pss_project_description'; }
	public function get_title() { return 'Project Description'; }
	public function get_icon() { return 'eicon-post-content'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Description' ) );
		$this->add_control( 'source', array( 'label' => 'Source', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'content', 'options' => array( 'content' => 'Full Description', 'excerpt' => 'Short Description' ) ) );
		$this->add_control( 'dropcap', array( 'label' => 'Drop Cap', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		if ( ! $id ) return;
		$content = 'excerpt' === ( $s['source'] ?? 'content' ) ? get_the_excerpt( $id ) : get_post_field( 'post_content', $id );
		if ( ! $content ) return;
		$content = 'content' === ( $s['source'] ?? 'content' ) ? apply_filters( 'the_content', $content ) : wpautop( $content );
		echo '<div class="pss-project-description' . ( 'yes' === ( $s['dropcap'] ?? '' ) ? ' pss-project-description--dropcap' : '' ) . '">' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
