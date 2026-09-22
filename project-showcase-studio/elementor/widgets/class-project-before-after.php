<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Before_After extends Base {
	public function get_name() { return 'pss_project_before_after'; }
	public function get_title() { return 'Project Before / After'; }
	public function get_icon() { return 'eicon-image-before-after'; }

	protected function register_controls() {
		$this->add_source_controls();

		$this->start_controls_section( 'content', array( 'label' => 'Comparison', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'orientation', array( 'label' => 'Orientation', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'horizontal', 'options' => array( 'horizontal' => 'Horizontal', 'vertical' => 'Vertical' ) ) );
		$this->add_control( 'start', array( 'label' => 'Start position', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'default' => array( 'size' => 50 ) ) );
		$this->add_control( 'before_label', array( 'label' => 'Before label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Before' ) );
		$this->add_control( 'after_label', array( 'label' => 'After label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'After' ) );
		$this->add_control( 'show_labels', array( 'label' => 'Show labels', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_icon_control( 'handle_icon', 'Handle icon', array( 'value' => 'fas fa-arrows-alt-h', 'library' => 'fa-solid' ) );

		$this->add_control( 'before_image', array( 'label' => 'Before image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'after_image', array( 'label' => 'After image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'before_url', array( 'label' => 'Before image URL', 'type' => \Elementor\Controls_Manager::URL, 'placeholder' => 'https://', 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'after_url', array( 'label' => 'After image URL', 'type' => \Elementor\Controls_Manager::URL, 'placeholder' => 'https://', 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'before_field', array( 'label' => 'Before dynamic field', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => self::field_options(), 'condition' => array( 'content_source' => 'project' ) ) );
		$this->add_control( 'after_field', array( 'label' => 'After dynamic field', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => self::field_options(), 'condition' => array( 'content_source' => 'project' ) ) );
		$this->end_controls_section();

		if ( class_exists( '\Elementor\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'before_image', array( 'label' => 'Before', 'type' => \Elementor\Controls_Manager::MEDIA ) );
			$repeater->add_control( 'after_image', array( 'label' => 'After', 'type' => \Elementor\Controls_Manager::MEDIA ) );
			$this->start_controls_section( 'pairs', array( 'label' => 'Additional comparisons', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ) );
			$this->add_control( 'items', array( 'label' => 'Items', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ title }}}', 'prevent_empty' => false ) );
			$this->end_controls_section();
		}

		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'height', array( 'label' => 'Height', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 180, 'max' => 1000 ), 'vh' => array( 'min' => 30, 'max' => 100 ) ), 'default' => array( 'size' => 520, 'unit' => 'px' ), 'tablet_default' => array( 'size' => 420, 'unit' => 'px' ), 'mobile_default' => array( 'size' => 320, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .pss-before-after' => 'height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'overlay', array( 'label' => 'Overlay', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-before-after__label' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'handle_color', array( 'label' => 'Handle', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-before-after__handle' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'label_color', array( 'label' => 'Label color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .pss-before-after__label' => 'color: {{VALUE}};' ) ) );
		$this->add_typography( 'label_typo', '{{WRAPPER}} .pss-before-after__label' );
		$this->add_responsive_control( 'radius', array( 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 48 ) ), 'selectors' => array( '{{WRAPPER}} .pss-before-after' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		$pairs = $this->pairs( $s, $id );
		if ( ! $pairs ) {
			return;
		}
		$start = isset( $s['start']['size'] ) ? absint( $s['start']['size'] ) : 50;
		$ori   = sanitize_key( $s['orientation'] ?? 'horizontal' );
		foreach ( $pairs as $pair ) {
			echo '<div class="pss-before-after pss-before-after--' . esc_attr( $ori ) . '" style="--pss-ba-pos:' . esc_attr( $start ) . '%" data-pss-ba="' . esc_attr( $ori ) . '">';
			echo '<img class="pss-before-after__after" src="' . esc_url( $pair['after'] ) . '" alt="' . esc_attr( $s['after_label'] ?? 'After' ) . '">';
			echo '<div class="pss-before-after__clip" style="width:' . esc_attr( $start ) . '%"><img src="' . esc_url( $pair['before'] ) . '" alt="' . esc_attr( $s['before_label'] ?? 'Before' ) . '"></div>';
			if ( ! empty( $s['show_labels'] ) ) {
				echo '<span class="pss-before-after__label pss-before-after__label--before">' . esc_html( $s['before_label'] ?: 'Before' ) . '</span>';
				echo '<span class="pss-before-after__label pss-before-after__label--after">' . esc_html( $s['after_label'] ?: 'After' ) . '</span>';
			}
			echo '<span class="pss-before-after__handle">';
			$this->render_icon( $s['handle_icon'] ?? array() );
			echo '</span>';
			echo '<input type="range" min="0" max="100" value="' . esc_attr( $start ) . '" aria-label="Before After position">';
			echo '</div>';
		}
	}

	private function pairs( $s, $project_id ) {
		$pairs = array();
		if ( $this->is_manual( $s ) ) {
			$before = $this->media_url( $s['before_image'] ?? array(), $s['before_url']['url'] ?? '' );
			$after  = $this->media_url( $s['after_image'] ?? array(), $s['after_url']['url'] ?? '' );
			if ( $before && $after ) {
				$pairs[] = array( 'before' => $before, 'after' => $after );
			}
		} else {
			$before = $this->dynamic_image( $project_id, $s['before_field'] ?? '', 'before' );
			$after  = $this->dynamic_image( $project_id, $s['after_field'] ?? '', 'after' );
			if ( $before && $after ) {
				$pairs[] = array( 'before' => $before, 'after' => $after );
			}
		}
		foreach ( (array) ( $s['items'] ?? array() ) as $item ) {
			$b = $this->media_url( $item['before_image'] ?? array() );
			$a = $this->media_url( $item['after_image'] ?? array() );
			if ( $b && $a ) {
				$pairs[] = array( 'before' => $b, 'after' => $a );
			}
		}
		return $pairs;
	}

	private function dynamic_image( $project_id, $field_key, $fallback ) {
		if ( $field_key && $project_id ) {
			$data = \PSS\get_project_card_field( $project_id, $field_key );
			$value = $data['value'] ?? '';
			if ( is_numeric( $value ) ) {
				$url = wp_get_attachment_image_url( absint( $value ), 'full' );
				if ( $url ) {
					return $url;
				}
			}
			if ( is_string( $value ) && 0 === strpos( $value, 'http' ) ) {
				return $value;
			}
		}
		return $this->project_image( $project_id, $fallback );
	}
}
