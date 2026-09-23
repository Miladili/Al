<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Timeline extends Base {
	public function get_name() { return 'pss_project_timeline'; }
	public function get_title() { return 'Project Timeline'; }
	public function get_icon() { return 'eicon-time-line'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Process' ) );
		if ( class_exists( '\Elementor\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'year', array( 'label' => 'Step / year', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'text', array( 'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
			$repeater->add_control( 'icon', array( 'label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS ) );
			$this->add_control( 'items', array( 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ title }}}', 'default' => array( array( 'year' => '01', 'title' => 'Brief' ), array( 'year' => '02', 'title' => 'Design' ), array( 'year' => '03', 'title' => 'Build' ) ) ) );
		}
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'vertical', 'options' => array( 'vertical' => 'Vertical', 'horizontal' => 'Horizontal' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = (array) ( $s['items'] ?? array() );
		if ( ! $items ) {
			$this->empty_state( 'Project Timeline', 'Add steps in the widget. Each row is a year/title/text.' );
			return;
		}
		echo '<ol class="pss-timeline pss-timeline--' . esc_attr( $s['layout'] ?? 'vertical' ) . '">';
		foreach ( $items as $item ) {
			echo '<li><span class="pss-timeline__mark">';
			$this->render_icon( $item['icon'] ?? array() );
			echo esc_html( $item['year'] ?? '' ) . '</span><div><strong>' . esc_html( $item['title'] ?? '' ) . '</strong>';
			if ( ! empty( $item['text'] ) ) {
				echo '<p>' . esc_html( $item['text'] ) . '</p>';
			}
			echo '</div></li>';
		}
		echo '</ol>';
	}
}
