<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Meta extends Base {
	public function get_name() { return 'pss_project_meta'; }
	public function get_title() { return 'Project Info'; }
	public function get_icon() { return 'eicon-post-info'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Content' ) );
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'inline', 'options' => array( 'inline' => 'Inline', 'stacked' => 'Stacked', 'grid' => 'Grid' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'min' => 1, 'max' => 6, 'selectors' => array( '{{WRAPPER}} .pss-project-meta--grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ), 'condition' => array( 'layout' => 'grid' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .pss-project-meta' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'fields', array( 'label' => 'Fields', 'type' => \Elementor\Controls_Manager::SELECT2, 'multiple' => true, 'default' => array( 'type', 'location', 'year', 'area', 'style' ), 'options' => array( 'type' => 'Project Type', 'location' => 'Location', 'year' => 'Year', 'area' => 'Area', 'style' => 'Style', 'designer' => 'Designer', 'architect' => 'Architect', 'client' => 'Client', 'duration' => 'Duration', 'status' => 'Status', 'budget' => 'Budget', 'completion' => 'Completion', 'services' => 'Services', 'materials' => 'Materials' ) ) );
		$this->add_control( 'show_labels', array( 'label' => 'Show labels', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-meta' );
		$this->add_text_style( 'label', 'Labels', '{{WRAPPER}} .pss-meta-item span' );
		$this->add_text_style( 'value', 'Values', '{{WRAPPER}} .pss-meta-item strong' );
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		if ( ! $id ) { return; }
		$fields = $s['fields'] ?? array();
		$values = array(
			'type'       => \PSS\get_project_taxonomy_value( $id, 'pss_project_type' ),
			'location'   => \PSS\get_project_taxonomy_value( $id, 'pss_project_location' ),
			'year'       => \PSS\get_meta( $id, '_pss_year' ),
			'area'       => \PSS\get_meta( $id, '_pss_area' ),
			'style'      => \PSS\get_project_taxonomy_value( $id, 'pss_project_style' ),
			'designer'   => \PSS\get_meta( $id, '_pss_designer' ),
			'architect'  => \PSS\get_meta( $id, '_pss_architect' ),
			'client'     => \PSS\get_meta( $id, '_pss_client' ),
			'duration'   => \PSS\get_meta( $id, '_pss_duration' ),
			'status'     => \PSS\get_meta( $id, '_pss_status' ),
			'budget'     => \PSS\get_meta( $id, '_pss_budget' ),
			'completion' => \PSS\get_meta( $id, '_pss_completion' ),
			'services'   => \PSS\get_meta( $id, '_pss_services' ),
			'materials'  => \PSS\get_meta( $id, '_pss_materials' ),
		);
		$labels = array( 'type' => 'Type', 'location' => 'Location', 'year' => 'Year', 'area' => 'Area', 'style' => 'Style', 'designer' => 'Designer', 'architect' => 'Architect', 'client' => 'Client', 'duration' => 'Duration', 'status' => 'Status', 'budget' => 'Budget', 'completion' => 'Completion', 'services' => 'Services', 'materials' => 'Materials' );
		echo '<div class="pss-project-meta pss-project-meta--' . esc_attr( $s['layout'] ?? 'inline' ) . '">';
		foreach ( (array) $fields as $key ) {
			if ( empty( $values[ $key ] ) ) { continue; }
			echo '<div class="pss-meta-item">';
			if ( ! empty( $s['show_labels'] ) ) {
				echo '<span>' . esc_html( $labels[ $key ] ?? $key ) . '</span>';
			}
			echo '<strong>' . esc_html( $values[ $key ] ) . '</strong></div>';
		}
		echo '</div>';
	}
}
