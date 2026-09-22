<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Share extends Base {
	public function get_name() {
		return 'pss_project_share';
	}
	public function get_title() {
		return 'Project Share';
	}
	public function get_icon() {
		return 'eicon-share';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content',
			array(
				'label' => 'Share',
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'label',
			array(
				'label'   => 'Label',
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Share project',
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$id = \PSS\get_project_id();
		if ( ! $id ) {
			return;
		}
		$url  = get_permalink( $id );
		$s    = $this->get_settings_for_display();
		echo '<div class="pss-project-share"><span>' . esc_html( $s['label'] ?? 'Share project' ) . '</span>';
		echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) . '" target="_blank" rel="noopener">Facebook</a>';
		echo '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ) . '" target="_blank" rel="noopener">LinkedIn</a>';
		echo '<a href="mailto:?subject=' . rawurlencode( get_the_title( $id ) ) . '&body=' . rawurlencode( $url ) . '">Email</a></div>';
	}
}
