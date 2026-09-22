<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Awards extends Base {
	public function get_name() { return 'pss_project_awards'; }
	public function get_title() { return 'Project Awards'; }
	public function get_icon() { return 'eicon-favorite'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Awards' ) );
		if ( class_exists( '\Elementor\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'title', array( 'label' => 'Award', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'meta', array( 'label' => 'Year / body', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'icon', array( 'label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-award', 'library' => 'fa-solid' ) ) );
			$repeater->add_control( 'image', array( 'label' => 'Logo', 'type' => \Elementor\Controls_Manager::MEDIA ) );
			$this->add_control( 'items', array( 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ title }}}' ) );
		}
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$items = (array) ( $s['items'] ?? array() );
		if ( ! $items ) {
			return;
		}
		echo '<ul class="pss-awards">';
		foreach ( $items as $item ) {
			echo '<li>';
			$url = $this->media_url( $item['image'] ?? array() );
			if ( $url ) {
				echo '<img src="' . esc_url( $url ) . '" alt="">';
			} else {
				$this->render_icon( $item['icon'] ?? array() );
			}
			echo '<div><strong>' . esc_html( $item['title'] ?? '' ) . '</strong><span>' . esc_html( $item['meta'] ?? '' ) . '</span></div></li>';
		}
		echo '</ul>';
	}
}
