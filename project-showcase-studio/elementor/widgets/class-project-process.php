<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Process extends Base {
	public function get_name() { return 'pss_project_process'; }
	public function get_title() { return 'Project Process'; }
	public function get_icon() { return 'eicon-sitemap'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Process', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'layout', array( 'label' => 'Preset', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'timeline', 'options' => array( 'timeline'=>'Timeline', 'steps'=>'Numbered steps', 'cards'=>'Cards' ) ) );
		if ( class_exists( '\\Elementor\\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'text', array( 'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
			$repeater->add_control( 'icon', array( 'label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS ) );
			$this->add_control( 'steps', array( 'label' => 'Manual steps', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ title }}}', 'condition' => array( 'content_source' => 'manual' ), 'default' => array( array( 'title' => 'Discover', 'text' => 'Brief, site and constraints.' ), array( 'title' => 'Design', 'text' => 'Concept through detail.' ), array( 'title' => 'Deliver', 'text' => 'Build, style, photograph.' ) ) ) );
		}
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'color', array( 'label' => 'Accent', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-process__index' => 'background: {{VALUE}};' ) ) );
		$this->add_typography( 'title_typo', '{{WRAPPER}} .pss-process h3' );
		$this->add_responsive_control( 'gap', array( 'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => array( '{{WRAPPER}} .pss-process' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$steps = array();
		if ( $this->is_manual( $s ) ) {
			foreach ( (array) ( $s['steps'] ?? array() ) as $row ) {
				if ( empty( $row['title'] ) ) {
					continue;
				}
				$steps[] = $row;
			}
		} else {
			$id = $this->project_id( $s );
			$raw = $id ? \PSS\get_meta( $id, '_pss_features' ) : '';
			foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
				$line = trim( $line );
				if ( $line ) {
					$steps[] = array( 'title' => $line, 'text' => '' );
				}
			}
		}
		if ( ! $steps ) {
			$this->empty_state( 'Project Process', 'Switch to Manual content and add steps, or fill Features on the project.' );
			return;
		}
		echo '<ol class="pss-process pss-process--' . esc_attr( sanitize_key( $s['layout'] ?? 'timeline' ) ) . '">';
		foreach ( $steps as $i => $step ) {
			echo '<li><span class="pss-process__index">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span><div>';
			if ( ! empty( $step['icon'] ) ) {
				$this->render_icon( $step['icon'] );
			}
			echo '<h3>' . esc_html( $step['title'] ?? '' ) . '</h3>';
			if ( ! empty( $step['text'] ) ) {
				echo '<p>' . esc_html( $step['text'] ) . '</p>';
			}
			echo '</div></li>';
		}
		echo '</ol>';
	}
}
