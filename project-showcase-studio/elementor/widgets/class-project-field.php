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
		$this->add_box_style( '{{WRAPPER}} .pss-project-field-widget' );
		$this->add_text_style( 'label', 'Label', '{{WRAPPER}} .pss-project-field-widget__label' );
		$this->add_text_style( 'value', 'Value', '{{WRAPPER}} .pss-project-field-widget__value' );
		$this->add_icon_style( '{{WRAPPER}} .pss-project-field-widget__icon' );
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$id  = $this->project_id( $s );
		$key = $this->resolve_field_key( $s );
		$html = '';
		$unit = '';
		$label = $s['custom_label'] ?: $key;
		if ( $this->is_manual( $s ) ) {
			$text  = (string) ( $s['manual_value'] ?? '' );
			$html  = esc_html( $text );
		} else {
			if ( ! $id || ! $key ) {
				$this->empty_state( 'Project Field', $key ? 'Choose a preview project in Single Layout settings, or switch this widget to Manual content.' : 'Select a field in the widget panel.' );
				return;
			}
			$data  = \PSS\get_project_card_field( $id, $key );
			$value = $data['value'] ?? '';
			$label = $s['custom_label'] ?: ( $data['label'] ?? $key );
			$type  = $data['type'] ?? 'text';
			$unit  = (string) ( ( $data['definition']['unit'] ?? '' ) );
			$text  = \PSS\field_value_text( $value );
			$html  = is_array( $value ) ? \PSS\render_field_value( $value, $type, $data['definition'] ?? array() ) : esc_html( $text );
			if ( $unit && ! is_array( $value ) && '' !== $text ) {
				$html .= ' <span class="pss-unit">' . esc_html( $unit ) . '</span>';
			}
		}
		if ( '' === trim( wp_strip_all_tags( (string) $html ) ) ) {
			if ( '' !== ( $s['empty_text'] ?? '' ) ) {
				echo '<span class="pss-project-field-widget__empty">' . esc_html( $s['empty_text'] ) . '</span>';
			} else {
				$this->empty_state( 'Project Field', 'No value for this field on the preview project.' );
			}
			return;
		}
		echo '<div class="pss-project-field-widget">';
		$this->render_icon( $s['icon'] ?? array(), 'pss-project-field-widget__icon' );
		if ( ! empty( $s['show_label'] ) ) {
			echo '<span class="pss-project-field-widget__label">' . esc_html( $label ) . '</span>';
		}
		echo '<div class="pss-project-field-widget__value">' . $html . '</div></div>';
	}
}
