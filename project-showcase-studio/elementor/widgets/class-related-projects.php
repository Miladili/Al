<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Related_Projects extends Base {
	public function get_name() { return 'pss_related_projects'; }
	public function get_title() { return 'Related Projects'; }
	public function get_icon() { return 'eicon-posts-grid'; }
	public function get_script_depends() { return array( 'pss-frontend' ); }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Projects' ) );
		$this->add_control( 'limit', array( 'label' => 'Limit', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 12, 'default' => 3 ) );
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'cards', 'options' => array( 'cards' => 'Cards', 'masonry' => 'Masonry' ) ) );
		$this->add_control( 'preset', array( 'label' => 'Card composition', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'modern', 'options' => array( 'modern' => 'Classic stack', 'minimal' => 'Minimal', 'editorial' => 'Editorial split', 'cinematic' => 'Cinematic overlay' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 6, 'selectors' => array( '{{WRAPPER}} .pss-related-grid' => '--pss-cols: {{VALUE}};' ) ) );
		$this->add_control( 'show_title', array( 'label' => 'Show titles', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-related-grid' );
		$this->add_text_style( 'card_title', 'Card title', '{{WRAPPER}} .pss-card__title' );
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		$posts = \PSS\Ajax::query(
			array(
				'query_type' => 'related',
				'related_id' => $id,
				'limit'      => absint( $s['limit'] ?? 3 ),
			)
		);
		$settings = array(
			'preset'     => sanitize_key( $s['preset'] ?? 'modern' ),
			'animation'  => 'reveal',
			'show_title' => ! empty( $s['show_title'] ),
			'show_image' => true,
		);
		echo '<div class="pss-related-grid pss-related-grid--' . esc_attr( $s['layout'] ?? 'cards' ) . '">' . \PSS\RenderCards::cards( $posts, $settings ) . '</div>';
	}
}
