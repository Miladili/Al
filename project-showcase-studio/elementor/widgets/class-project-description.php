<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Description extends Base {
	public function get_name() { return 'pss_project_description'; }
	public function get_title() { return 'Project Description'; }
	public function get_icon() { return 'eicon-post-content'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Description' ) );
		$this->add_control( 'source', array( 'label' => 'Source', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'content', 'options' => array( 'content' => 'Full description', 'excerpt' => 'Short description' ), 'condition' => array( 'content_source' => 'project' ) ) );
		$this->add_control( 'manual_text', array( 'label' => 'Manual text', 'type' => \Elementor\Controls_Manager::WYSIWYG, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'dropcap', array( 'label' => 'Drop cap', 'type' => \Elementor\Controls_Manager::SWITCHER ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-description' );
		$this->add_text_style( 'body', 'Text', '{{WRAPPER}} .pss-project-description' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( $this->is_manual( $s ) ) {
			$content = $s['manual_text'] ?? '';
		} else {
			$id = $this->project_id( $s );
			if ( ! $id ) {
				$this->empty_state( 'Project Description', 'Choose a preview project in Single Layout settings, or switch this widget to Manual content.' );
				return;
			}
			$content = 'excerpt' === ( $s['source'] ?? 'content' ) ? get_the_excerpt( $id ) : get_post_field( 'post_content', $id );
			$content = 'content' === ( $s['source'] ?? 'content' ) ? apply_filters( 'the_content', $content ) : wpautop( $content );
		}
		if ( ! $content ) {
			$this->empty_state( 'Project Description', 'No description on this project. Write one on the project, or switch the widget to Manual content.' );
			return;
		}
		echo '<div class="pss-project-description' . ( ! empty( $s['dropcap'] ) ? ' pss-project-description--dropcap' : '' ) . '">' . wp_kses_post( $content ) . '</div>';
	}
}
