<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

abstract class Base extends \Elementor\Widget_Base {
	private static $project_options = null;

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
				'label' => 'Project Source',
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'project_source',
			array(
				'label'   => 'Source',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'current',
				'options' => array(
					'current'  => 'Current Project',
					'selected' => 'Select Project',
				),
			)
		);
		$this->add_control(
			'selected_project',
			array(
				'label'     => 'Project',
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'options'   => self::project_options(),
				'condition' => array( 'project_source' => 'selected' ),
			)
		);
		$this->end_controls_section();
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

	protected function project_id( $settings = array() ) {
		if ( ! empty( $settings['project_source'] ) && 'selected' === $settings['project_source'] && ! empty( $settings['selected_project'] ) ) {
			return absint( $settings['selected_project'] );
		}
		return \PSS\get_project_id();
	}

	protected function control_label() {
		return 'Style';
	}
}
