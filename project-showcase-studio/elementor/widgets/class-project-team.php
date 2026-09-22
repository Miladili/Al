<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Team extends Base {
	public function get_name() { return 'pss_project_team'; }
	public function get_title() { return 'Project Team'; }
	public function get_icon() { return 'eicon-person'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Team' ) );
		if ( class_exists( '\Elementor\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'name', array( 'label' => 'Name', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'role', array( 'label' => 'Role', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'image', array( 'label' => 'Portrait', 'type' => \Elementor\Controls_Manager::MEDIA ) );
			$this->add_control( 'people', array( 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ name }}}', 'condition' => array( 'content_source' => 'manual' ) ) );
		}
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'selectors' => array( '{{WRAPPER}} .pss-team' => '--pss-cols: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		$people = array();
		if ( $this->is_manual( $s ) ) {
			$people = (array) ( $s['people'] ?? array() );
		} elseif ( $id ) {
			foreach ( array( 'designer' => 'Designer', 'architect' => 'Architect', 'client' => 'Client' ) as $key => $role ) {
				$name = \PSS\get_meta( $id, '_pss_' . $key );
				if ( $name && ! \PSS\is_placeholder_text( $name ) ) {
					$people[] = array( 'name' => $name, 'role' => $role, 'image' => array() );
				}
			}
			$team = \PSS\get_meta( $id, '_pss_team' );
			if ( $team && ! \PSS\is_placeholder_text( $team ) ) {
				$people[] = array( 'name' => $team, 'role' => 'Team', 'image' => array() );
			}
		}
		if ( ! $people ) {
			return;
		}
		echo '<div class="pss-team">';
		foreach ( $people as $person ) {
			echo '<article class="pss-team__card">';
			$url = $this->media_url( $person['image'] ?? array() );
			if ( $url ) {
				echo '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $person['name'] ?? '' ) . '">';
			}
			echo '<strong>' . esc_html( $person['name'] ?? '' ) . '</strong><span>' . esc_html( $person['role'] ?? '' ) . '</span></article>';
		}
		echo '</div>';
	}
}
