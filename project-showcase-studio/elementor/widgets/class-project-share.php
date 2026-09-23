<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Share extends Base {
	public function get_name() { return 'pss_project_share'; }
	public function get_title() { return 'Project Share'; }
	public function get_icon() { return 'eicon-share'; }

	protected function register_controls() {
		$this->start_controls_section(
			'content',
			array(
				'label' => 'Share',
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'label', array( 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Share project' ) );
		$this->add_control( 'show_facebook', array( 'label' => 'Facebook', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_linkedin', array( 'label' => 'LinkedIn', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_email', array( 'label' => 'Email', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-share' );
		$this->add_text_style( 'share', 'Links', '{{WRAPPER}} .pss-project-share a' );
	}

	protected function render() {
		$id = \PSS\get_project_id();
		if ( ! $id ) { return; }
		$url = get_permalink( $id );
		$s   = $this->get_settings_for_display();
		echo '<div class="pss-project-share"><span>' . esc_html( $s['label'] ?? 'Share project' ) . '</span>';
		if ( ! empty( $s['show_facebook'] ) ) {
			echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) . '" target="_blank" rel="noopener">Facebook</a>';
		}
		if ( ! empty( $s['show_linkedin'] ) ) {
			echo '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ) . '" target="_blank" rel="noopener">LinkedIn</a>';
		}
		if ( ! empty( $s['show_email'] ) ) {
			echo '<a href="mailto:?subject=' . rawurlencode( get_the_title( $id ) ) . '&body=' . rawurlencode( $url ) . '">Email</a>';
		}
		echo '</div>';
	}
}
