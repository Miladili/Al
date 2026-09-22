<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Stats extends Base {
	public function get_name() { return 'pss_project_stats'; }
	public function get_title() { return 'Project Stats'; }
	public function get_icon() { return 'eicon-counter-circle'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Stats', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'fields', array( 'label' => 'Field keys', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'placeholder' => "core:year\ncore:area\ncore:location", 'condition' => array( 'content_source' => 'project' ) ) );
		if ( class_exists( '\Elementor\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'label', array( 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'value', array( 'label' => 'Value', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'icon', array( 'label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS ) );
			$this->add_control( 'manual_items', array( 'label' => 'Manual stats', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ label }}}', 'condition' => array( 'content_source' => 'manual' ) ) );
		}
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 4, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 6, 'selectors' => array( '{{WRAPPER}} .pss-project-stats' => '--pss-stats-cols: {{VALUE}};' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'label_color', array( 'label' => 'Label', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-stat span' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'value_color', array( 'label' => 'Value', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-stat strong' => 'color: {{VALUE}};' ) ) );
		$this->add_typography( 'value_typo', '{{WRAPPER}} .pss-project-stat strong' );
		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		$items = array();
		if ( $this->is_manual( $s ) ) {
			foreach ( (array) ( $s['manual_items'] ?? array() ) as $row ) {
				if ( '' === trim( (string) ( $row['value'] ?? '' ) ) ) {
					continue;
				}
				$items[] = $row;
			}
		} elseif ( $id ) {
			$keys = array_filter( array_map( function( $key ) {
				$key = trim( (string) $key );
				return 0 === strpos( $key, 'core:' ) ? 'core:' . sanitize_key( substr( $key, 5 ) ) : sanitize_key( $key );
			}, preg_split( '/[,\n]+/', (string) ( $s['fields'] ?? '' ) ) ) );
			if ( ! $keys ) {
				$keys = array( 'core:year', 'core:area', 'core:location', 'core:type' );
			}
			foreach ( $keys as $key ) {
				$data  = \PSS\get_project_card_field( $id, $key );
				$value = \PSS\field_value_text( $data['value'] ?? '' );
				if ( '' === $value || \PSS\is_placeholder_text( $value ) ) {
					continue;
				}
				$items[] = array( 'label' => $data['label'] ?? $key, 'value' => $value, 'icon' => array() );
			}
		}
		if ( ! $items ) {
			return;
		}
		echo '<div class="pss-project-stats">';
		foreach ( $items as $item ) {
			echo '<div class="pss-project-stat">';
			$this->render_icon( $item['icon'] ?? array() );
			echo '<span>' . esc_html( $item['label'] ?? '' ) . '</span><strong>' . esc_html( $item['value'] ?? '' ) . '</strong></div>';
		}
		echo '</div>';
	}
}
