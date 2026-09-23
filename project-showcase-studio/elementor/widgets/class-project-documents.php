<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Documents extends Base {
	public function get_name() { return 'pss_project_documents'; }
	public function get_title() { return 'Project Documents'; }
	public function get_icon() { return 'eicon-document-file'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Documents' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Downloads' ) );
		if ( class_exists( '\Elementor\Repeater' ) ) {
			$repeater = new \Elementor\Repeater();
			$repeater->add_control( 'label', array( 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT ) );
			$repeater->add_control( 'file', array( 'label' => 'File', 'type' => \Elementor\Controls_Manager::MEDIA ) );
			$repeater->add_control( 'url', array( 'label' => 'Or file URL', 'type' => \Elementor\Controls_Manager::URL ) );
			$this->add_control( 'manual_files', array( 'label' => 'Files', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ label }}}', 'prevent_empty' => false, 'condition' => array( 'content_source' => 'manual' ) ) );
		}
		$this->end_controls_section();
		$this->add_box_style( '{{WRAPPER}} .pss-project-docs' );
		$this->add_text_style( 'doc', 'Links', '{{WRAPPER}} .pss-project-docs a' );
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array();
		if ( $this->is_manual( $s ) ) {
			foreach ( (array) ( $s['manual_files'] ?? array() ) as $row ) {
				$url   = $this->media_url( $row['file'] ?? array(), $row['url']['url'] ?? '' );
				$label = $row['label'] ?: ( $url ? wp_basename( $url ) : '' );
				if ( $url ) {
					$items[] = array( 'label' => $label, 'url' => $url );
				}
			}
		} else {
			$id = $this->project_id( $s );
			if ( ! $id ) {
				$this->empty_state( 'Project Documents', 'Choose a preview project, or switch this widget to Manual content.' );
				return;
			}
			foreach ( \PSS\get_field_definitions( $id ) as $field ) {
				if ( 'file' !== ( $field['type'] ?? '' ) ) { continue; }
				$file_id = absint( \PSS\get_field_value( $id, $field['key'], 0 ) );
				$url     = $file_id ? wp_get_attachment_url( $file_id ) : '';
				if ( $url ) {
					$items[] = array( 'label' => $field['label'] ?? 'Download', 'url' => $url );
				}
			}
		}
		if ( ! $items ) { return; }
		echo '<div class="pss-project-docs">';
		if ( ! empty( $s['heading'] ) ) {
			echo '<h3 class="pss-project-docs__heading">' . esc_html( $s['heading'] ) . '</h3>';
		}
		echo '<ul>';
		foreach ( $items as $item ) {
			echo '<li><a href="' . esc_url( $item['url'] ) . '" download>' . esc_html( $item['label'] ) . '</a></li>';
		}
		echo '</ul></div>';
	}
}
