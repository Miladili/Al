<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Marquee extends Base {
	public function get_name() { return 'pss_project_marquee'; }
	public function get_title() { return 'Project Marquee'; }
	public function get_icon() { return 'eicon-animation-text'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Marquee' ) );
		$this->add_control( 'text', array( 'label' => 'Manual text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Selected work — architecture — interiors —', 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'limit', array( 'label' => 'Projects', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 8, 'condition' => array( 'content_source' => 'project' ) ) );
		$this->add_control( 'speed', array( 'label' => 'Speed (s)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 28, 'min' => 8, 'max' => 80 ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_typography( 'typo', '{{WRAPPER}} .pss-marquee' );
		$this->add_control( 'color', array( 'label' => 'Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-marquee' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$bits = array();
		if ( $this->is_manual( $s ) ) {
			$bits[] = $s['text'] ?? '';
		} else {
			$posts = get_posts( array( 'post_type' => \PSS_PROJECT_CPT, 'post_status' => 'publish', 'posts_per_page' => absint( $s['limit'] ?? 8 ) ) );
			foreach ( $posts as $post ) {
				$bits[] = $post->post_title;
			}
		}
		$bits = array_filter( $bits );
		if ( ! $bits ) {
			return;
		}
		$line = implode( '  —  ', $bits );
		echo '<div class="pss-marquee" style="--pss-marquee:' . esc_attr( absint( $s['speed'] ?? 28 ) ) . 's"><div><span>' . esc_html( $line ) . '</span><span>' . esc_html( $line ) . '</span></div></div>';
	}
}
