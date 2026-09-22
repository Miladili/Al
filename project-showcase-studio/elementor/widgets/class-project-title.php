<?php
namespace PSS\Elementor\Widgets;
class Project_Title extends Base {
	public function get_name() { return 'pss_project_title'; }
	public function get_title() { return 'Project Title'; }
	public function get_icon() { return 'eicon-heading'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Content' ) );
		$this->add_control( 'style', array( 'label' => 'Style', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'editorial', 'options' => array( 'editorial'=>'Editorial', 'cinematic'=>'Cinematic', 'minimal'=>'Minimal' ) ) );
		$this->add_control( 'show_subtitle', array( 'label' => 'Show Subtitle', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'manual_title', array( 'label' => 'Manual title', 'type' => \Elementor\Controls_Manager::TEXT, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'manual_subtitle', array( 'label' => 'Manual subtitle', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style_tab', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'title_color', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-title h1' => 'color: {{VALUE}};' ) ) );
		$this->add_typography( 'title_typo', '{{WRAPPER}} .pss-project-title h1' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( $this->is_manual( $s ) ) {
			$title = $s['manual_title'] ?? '';
			$subtitle = $s['manual_subtitle'] ?? '';
		} else {
			$id = $this->project_id( $s );
			if ( ! $id ) { return; }
			$title = get_the_title( $id );
			$subtitle = \PSS\get_meta( $id, '_pss_subtitle' );
		}
		if ( ! $title ) { return; }
		echo '<div class="pss-project-title pss-project-title--' . esc_attr( $s['style'] ?? 'editorial' ) . '"><span class="pss-kicker">Project</span><h1>' . esc_html( $title ) . '</h1>';
		if ( ! empty( $s['show_subtitle'] ) && $subtitle ) echo '<p>' . esc_html( $subtitle ) . '</p>';
		echo '</div>';
	}
}
