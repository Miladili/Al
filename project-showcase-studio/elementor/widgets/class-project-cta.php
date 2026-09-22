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
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'color', array( 'label' => 'Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-cta' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'bg', array( 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-cta' => 'background: {{VALUE}};' ) ) );
		$this->add_typography( 'typo', '{{WRAPPER}} .pss-project-cta' );
		$this->add_responsive_control( 'pad', array( 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => array( '{{WRAPPER}} .pss-project-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$id   = $this->project_id( $s );
		$href = $this->is_manual( $s ) ? ( $s['link']['url'] ?? '#' ) : ( $id ? get_permalink( $id ) : '' );
		if ( ! $href ) {
			return;
		}
		echo '<a class="pss-project-cta" href="' . esc_url( $href ) . '"><span>' . esc_html( $s['text'] ?? 'View project' ) . '</span>';
		$this->render_icon( $s['icon'] ?? array(), 'pss-project-cta__icon' );
		echo '</a>';
	}
}
