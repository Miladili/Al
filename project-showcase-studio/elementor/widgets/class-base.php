<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

abstract class Base extends \Elementor\Widget_Base {
	private static $project_options = null;
	private static $field_options   = null;

	public function get_categories() {
		return array( 'pss-projects' );
	}

	public function get_keywords() {
		return array( 'project', 'portfolio', 'architecture', 'interior', 'showcase' );
	}

	public function get_style_depends() {
		return array( 'pss-frontend', 'pss-elementor' );
	}

	public function get_script_depends() {
		return array( 'pss-frontend' );
	}

	protected function add_source_controls() {
		$this->start_controls_section(
			'pss_source',
			array(
				'label' => 'Content source',
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'content_source',
			array(
				'label'   => 'Source',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'project',
				'options' => array(
					'project' => 'Dynamic Project Data',
					'manual'  => 'Manual / custom content',
				),
			)
		);
		$this->add_control(
			'project_source',
			array(
				'label'     => 'Project',
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'current',
				'options'   => array(
					'current'  => 'Current / preview project',
					'selected' => 'Select a project',
				),
				'condition' => array( 'content_source' => 'project' ),
			)
		);
		$this->add_control(
			'selected_project',
			array(
				'label'     => 'Choose project',
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'options'   => self::project_options(),
				'condition' => array(
					'content_source' => 'project',
					'project_source' => 'selected',
				),
			)
		);
		$this->end_controls_section();
	}

	protected function add_icon_control( $id, $label = 'Icon', $default = array() ) {
		$this->add_control(
			$id,
			array(
				'label'            => $label,
				'type'             => \Elementor\Controls_Manager::ICONS,
				'fa4compatibility' => $id . '_fa4',
				'default'          => $default ? $default : array(
					'value'   => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				),
			)
		);
	}

	protected function add_field_select( $id, $label = 'Project field' ) {
		$this->add_control(
			$id,
			array(
				'label'       => $label,
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => self::field_options(),
				'label_block' => true,
			)
		);
	}

	protected function add_typography( $name, $selector ) {
		if ( ! class_exists( '\Elementor\Group_Control_Typography' ) ) {
			return;
		}
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => $name,
				'selector' => $selector,
			)
		);
	}

	protected function render_icon( $icon, $class = '' ) {
		if ( empty( $icon ) || empty( $icon['value'] ) ) {
			return;
		}
		if ( class_exists( '\Elementor\Icons_Manager' ) ) {
			\Elementor\Icons_Manager::render_icon( $icon, array( 'class' => $class, 'aria-hidden' => 'true' ) );
			return;
		}
		echo '<i class="' . esc_attr( $icon['value'] . ' ' . $class ) . '" aria-hidden="true"></i>';
	}

	protected function is_manual( $settings ) {
		return 'manual' === sanitize_key( $settings['content_source'] ?? 'project' );
	}

	private static function project_options() {
		if ( null !== self::$project_options ) {
			return self::$project_options;
		}
		self::$project_options = array();
		$projects              = get_posts(
			array(
				'post_type'      => \PSS_PROJECT_CPT,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'fields'         => 'ids',
			)
		);
		foreach ( (array) $projects as $project_id ) {
			self::$project_options[ absint( $project_id ) ] = get_the_title( $project_id );
		}
		return self::$project_options;
	}

	protected static function field_options() {
		if ( null !== self::$field_options ) {
			return self::$field_options;
		}
		self::$field_options = array();
		foreach ( \PSS\get_project_field_library_options() as $key => $field ) {
			self::$field_options[ $key ] = (string) ( $field['label'] ?? $key );
		}
		return self::$field_options;
	}

	protected function project_id( $settings = array() ) {
		if ( $this->is_manual( $settings ) ) {
			return 0;
		}
		if ( ! empty( $settings['project_source'] ) && 'selected' === $settings['project_source'] && ! empty( $settings['selected_project'] ) ) {
			return absint( $settings['selected_project'] );
		}
		return \PSS\get_project_id();
	}

	protected function media_url( $image_control, $fallback = '' ) {
		$url = $this->resolve_image_src( $image_control );
		return $url ? $url : ( is_string( $fallback ) ? $fallback : '' );
	}

	protected function resolve_image_src( $value ) {
		try {
			if ( is_array( $value ) ) {
				if ( ! empty( $value['url'] ) && is_string( $value['url'] ) ) {
					return esc_url_raw( $value['url'] );
				}
				if ( ! empty( $value['id'] ) ) {
					$value = $value['id'];
				} elseif ( isset( $value[0] ) ) {
					$value = $value[0];
				} else {
					return '';
				}
			}
			if ( is_numeric( $value ) && absint( $value ) ) {
				$url = wp_get_attachment_image_url( absint( $value ), 'full' );
				return $url ? $url : '';
			}
			if ( is_string( $value ) ) {
				$value = trim( $value );
				if ( '' === $value ) {
					return '';
				}
				if ( 0 === strpos( $value, 'http://' ) || 0 === strpos( $value, 'https://' ) ) {
					return esc_url_raw( $value );
				}
				if ( is_numeric( $value ) ) {
					$url = wp_get_attachment_image_url( absint( $value ), 'full' );
					return $url ? $url : '';
				}
			}
		} catch ( \Throwable $e ) {
			return '';
		}
		return '';
	}

	protected function add_box_style( $selector ) {
		$this->start_controls_section( 'pss_box_style', array( 'label' => 'Box', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'pss_pad', array( 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'selectors' => array( $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_control( 'pss_bg', array( 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( $selector => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'pss_border_c', array( 'label' => 'Border', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( $selector => 'border-color: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'pss_radius', array( 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => array( $selector => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function project_image( $project_id, $which = 'featured' ) {
		$project_id = absint( $project_id );
		if ( ! $project_id ) {
			return '';
		}
		if ( 'before' === $which ) {
			$id = absint( \PSS\get_meta( $project_id, '_pss_before', 0 ) );
			return $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		}
		if ( 'after' === $which ) {
			$id = absint( \PSS\get_meta( $project_id, '_pss_after', 0 ) );
			return $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		}
		if ( 'floor' === $which ) {
			$id = absint( \PSS\get_meta( $project_id, '_pss_floor_plan', 0 ) );
			return $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		}
		if ( 'gallery' === $which ) {
			$ids = \PSS\get_gallery_ids( $project_id );
			return $ids ? wp_get_attachment_image_url( $ids[0], 'full' ) : '';
		}
		$url = get_the_post_thumbnail_url( $project_id, 'full' );
		if ( $url ) {
			return $url;
		}
		$ids = \PSS\get_gallery_ids( $project_id );
		return $ids ? wp_get_attachment_image_url( $ids[0], 'full' ) : '';
	}

	protected function control_label() {
		return 'Style';
	}
}
