<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Title extends Base {
	public function get_name() { return 'pss_project_title'; }
	public function get_title() { return 'Project Title'; }
	public function get_icon() { return 'eicon-heading'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Content' ) );
		$this->add_control( 'html_tag', array( 'label' => 'HTML tag', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'p' => 'Paragraph', 'div' => 'Div' ) ) );
		$this->add_control( 'style', array( 'label' => 'Style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'editorial', 'options' => array( 'editorial' => 'Editorial', 'cinematic' => 'Cinematic', 'minimal' => 'Minimal' ) ) );
		$this->add_control( 'show_kicker', array( 'label' => 'Show kicker', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'kicker', array( 'label' => 'Kicker text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Project', 'condition' => array( 'show_kicker' => 'yes' ) ) );
		$this->add_control( 'show_subtitle', array( 'label' => 'Show subtitle', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'manual_title', array( 'label' => 'Manual title', 'type' => \Elementor\Controls_Manager::TEXT, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'manual_subtitle', array( 'label' => 'Manual subtitle', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-title' );
		$this->add_title_style( '{{WRAPPER}} .pss-project-title__heading' );
		$this->add_text_style( 'kicker', 'Kicker', '{{WRAPPER}} .pss-kicker' );
		$this->add_text_style( 'subtitle', 'Subtitle', '{{WRAPPER}} .pss-project-title__sub' );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( $this->is_manual( $s ) ) {
			$title    = $s['manual_title'] ?? '';
			$subtitle = $s['manual_subtitle'] ?? '';
		} else {
			$id = $this->project_id( $s );
			if ( ! $id ) { return; }
			$title    = get_the_title( $id );
			$subtitle = \PSS\get_meta( $id, '_pss_subtitle' );
		}
		if ( ! $title ) { return; }
		$tag = in_array( $s['html_tag'] ?? 'h1', array( 'h1', 'h2', 'h3', 'h4', 'p', 'div' ), true ) ? $s['html_tag'] : 'h1';
		echo '<div class="pss-project-title pss-project-title--' . esc_attr( $s['style'] ?? 'editorial' ) . '">';
		if ( ! empty( $s['show_kicker'] ) ) {
			echo '<span class="pss-kicker">' . esc_html( $s['kicker'] ?: 'Project' ) . '</span>';
		}
		echo '<' . $tag . ' class="pss-project-title__heading">' . esc_html( $title ) . '</' . $tag . '>';
		if ( ! empty( $s['show_subtitle'] ) && $subtitle ) {
			echo '<p class="pss-project-title__sub">' . esc_html( $subtitle ) . '</p>';
		}
		echo '</div>';
	}
}
