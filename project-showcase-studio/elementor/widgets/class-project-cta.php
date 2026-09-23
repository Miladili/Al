<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_CTA extends Base {
	public function get_name() { return 'pss_project_cta'; }
	public function get_title() { return 'Project CTA'; }
	public function get_icon() { return 'eicon-button'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'CTA' ) );
		$this->add_control( 'text', array( 'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View project' ) );
		$this->add_icon_control( 'icon', 'Icon', array( 'value' => 'fas fa-arrow-right', 'library' => 'fa-solid' ) );
		$this->add_control( 'link', array( 'label' => 'Custom URL', 'type' => \Elementor\Controls_Manager::URL, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-cta' );
		$this->add_text_style( 'cta', 'Button', '{{WRAPPER}} .pss-project-cta' );
		$this->add_icon_style( '{{WRAPPER}} .pss-project-cta__icon' );
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$id   = $this->project_id( $s );
		$href = $this->is_manual( $s ) ? ( $s['link']['url'] ?? '#' ) : ( $id ? get_permalink( $id ) : '' );
		if ( ! $href ) { return; }
		$blank = $this->is_manual( $s ) && ! empty( $s['link']['is_external'] ) ? ' target="_blank" rel="noopener"' : '';
		echo '<a class="pss-project-cta" href="' . esc_url( $href ) . '"' . $blank . '><span>' . esc_html( $s['text'] ?? 'View project' ) . '</span>';
		$this->render_icon( $s['icon'] ?? array(), 'pss-project-cta__icon' );
		echo '</a>';
	}
}
