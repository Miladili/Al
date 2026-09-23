<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Video extends Base {
	public function get_name() { return 'pss_project_video'; }
	public function get_title() { return 'Project Video'; }
	public function get_icon() { return 'eicon-video-camera'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Video' ) );
		$this->add_control( 'ratio', array( 'label' => 'Ratio', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => '16-9', 'options' => array( '16-9' => '16:9', '4-3' => '4:3', '1-1' => '1:1', '21-9' => '21:9' ) ) );
		$this->add_control( 'manual_url', array( 'label' => 'Video URL', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'https://youtube.com/...', 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'poster', array( 'label' => 'Poster', 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-video' );
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$id  = $this->project_id( $s );
		$url = $this->is_manual( $s ) ? ( $s['manual_url'] ?? '' ) : ( $id ? ( \PSS\get_meta( $id, '_pss_video' ) ?: \PSS\get_field_value( $id, 'video', '' ) ) : '' );
		if ( ! $url ) {
			$this->empty_state( 'Project Video', 'Add a video URL on the project, or switch this widget to Manual content.' );
			return;
		}
		$html = wp_oembed_get( $url );
		if ( ! $html ) {
			$poster = $this->media_url( $s['poster'] ?? array() );
			$html   = '<video controls src="' . esc_url( $url ) . '" preload="metadata"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . '></video>';
		}
		echo '<div class="pss-video pss-video--' . esc_attr( $s['ratio'] ) . '">' . $html . '</div>';
	}
}
