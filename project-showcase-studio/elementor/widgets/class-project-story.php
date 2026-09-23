<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Story extends Base {
	public function get_name() { return 'pss_project_story'; }
	public function get_title() { return 'Project Story'; }
	public function get_icon() { return 'eicon-text-area'; }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Story' ) );
		$this->add_control( 'kicker', array( 'label' => 'Kicker', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'The story' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT ) );
		$this->add_control( 'text', array( 'label' => 'Text', 'type' => \Elementor\Controls_Manager::WYSIWYG, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->add_control( 'image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'image-right', 'options' => array( 'image-right' => 'Image right', 'image-left' => 'Image left', 'stacked' => 'Stacked', 'sticky' => 'Sticky image' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_typography( 'heading_typo', '{{WRAPPER}} .pss-story h2' );
		$this->add_responsive_control( 'gap', array( 'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => array( '{{WRAPPER}} .pss-story' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$id  = $this->project_id( $s );
		$img = $this->media_url( $s['image'] ?? array(), $this->project_image( $id ) );
		$text = $this->is_manual( $s ) ? ( $s['text'] ?? '' ) : ( $id ? apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) : '' );
		$title = $s['heading'] ?: ( $id ? get_the_title( $id ) : '' );
		if ( ! $title && ! $text ) {
			$this->empty_state( 'Project Story', 'Add a heading and text, or choose a preview project with a description.' );
			return;
		}
		echo '<section class="pss-story pss-story--' . esc_attr( $s['layout'] ?? 'image-right' ) . '">';
		echo '<div class="pss-story__copy"><span class="pss-kicker">' . esc_html( $s['kicker'] ?? '' ) . '</span><h2>' . esc_html( $title ) . '</h2><div class="pss-project-description">' . wp_kses_post( $text ) . '</div></div>';
		if ( $img ) {
			echo '<div class="pss-story__media"><img src="' . esc_url( $img ) . '" alt=""></div>';
		}
		echo '</section>';
	}
}
