<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Field extends Base {
	public function get_name() { return 'pss_project_field'; }
	public function get_title() { return 'Project Field'; }
	public function get_icon() { return 'eicon-database'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'field', array( 'label' => 'Field', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_field_select( 'field_key', 'Select field' );
		$this->add_control( 'field_key_manual', array( 'label' => 'Or type a field key', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'cabinet_material' ) );
		$this->add_control( 'manual_value', array( 'label' => 'Manual value', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'show_label', array( 'label' => 'Show label', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'custom_label', array( 'label' => 'Custom label', 'type' => \Elementor\Controls_Manager::TEXT ) );
		$this->add_icon_control( 'icon', 'Icon', array( 'value' => '', 'library' => '' ) );
		$this->add_control( 'empty_text', array( 'label' => 'Empty text', 'type' => \Elementor\Controls_Manager::TEXT ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'label_color', array( 'label' => 'Label color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-project-field-widget__label' => 'color: {{VALUE}};' ) ) );
		$this->add_typography( 'value_typo', '{{WRAPPER}} .pss-project-field-widget__value' );
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$id  = $this->project_id( $s );
		$key = sanitize_text_field( $s['field_key'] ?? '' );
		if ( ! $key ) {
			$key = sanitize_key( $s['field_key_manual'] ?? '' );
		}
		if ( $this->is_manual( $s ) ) {
			$text  = (string) ( $s['manual_value'] ?? '' );
			$label = $s['custom_label'] ?: $key;
		} else {
			if ( ! $id || ! $key ) {
				return;
			}
			$data  = \PSS\get_project_card_field( $id, $key );
			$text  = \PSS\field_value_text( $data['value'] ?? '' );
			$label = $s['custom_label'] ?: ( $data['label'] ?? $key );
		}
		if ( '' === $text ) {
			if ( '' !== ( $s['empty_text'] ?? '' ) ) {
				echo '<span class="pss-project-field-widget__empty">' . esc_html( $s['empty_text'] ) . '</span>';
			}
			return;
		}
		echo '<div class="pss-project-field-widget">';
		$this->render_icon( $s['icon'] ?? array(), 'pss-project-field-widget__icon' );
		if ( ! empty( $s['show_label'] ) ) {
			echo '<span class="pss-project-field-widget__label">' . esc_html( $label ) . '</span>';
		}
		echo '<div class="pss-project-field-widget__value">' . esc_html( $text ) . '</div></div>';
	}
}
