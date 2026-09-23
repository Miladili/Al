<?php
namespace PSS\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Project_Gallery extends Base {
	public function get_name() { return 'pss_project_gallery'; }
	public function get_title() { return 'Project Gallery'; }
	public function get_icon() { return 'eicon-gallery-grid'; }
	public function get_script_depends() { return array( 'pss-frontend' ); }

	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Gallery' ) );
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'masonry', 'options' => array( 'grid' => 'Grid', 'masonry' => 'Masonry', 'featured' => 'Featured', 'strip' => 'Horizontal', 'justified' => 'Justified', 'fullscreen' => 'Fullscreen' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'Columns', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 6, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'selectors' => array( '{{WRAPPER}} .pss-gallery' => '--pss-cols: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .pss-gallery' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'lightbox', array( 'label' => 'Lightbox', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'hover', array( 'label' => 'Hover', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'zoom', 'options' => array( 'none' => 'None', 'zoom' => 'Zoom', 'reveal' => 'Reveal', 'lift' => 'Lift' ) ) );
		$this->add_control( 'gallery', array( 'label' => 'Images', 'type' => \Elementor\Controls_Manager::GALLERY, 'condition' => array( 'content_source' => 'manual' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'radius', array( 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => array( '{{WRAPPER}} .pss-gallery img' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'image_height', array( 'label' => 'Image height', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 120, 'max' => 900 ), 'vh' => array( 'min' => 20, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .pss-gallery img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->project_id( $s );
		$urls = array();
		if ( $this->is_manual( $s ) ) {
			foreach ( (array) ( $s['gallery'] ?? array() ) as $item ) {
				$url = $this->media_url( $item );
				if ( $url ) {
					$urls[] = array( 'thumb' => $url, 'full' => $url );
				}
			}
		} elseif ( $id ) {
			foreach ( \PSS\get_gallery_ids( $id ) as $image_id ) {
				$thumb = wp_get_attachment_image_url( $image_id, 'large' );
				$full  = wp_get_attachment_image_url( $image_id, 'full' ) ?: $thumb;
				if ( $thumb ) {
					$urls[] = array( 'thumb' => $thumb, 'full' => $full );
				}
			}
		}
		if ( ! $urls ) {
			return;
		}
		echo '<div class="pss-gallery pss-gallery--' . esc_attr( $s['layout'] ) . ' pss-gallery--hover-' . esc_attr( $s['hover'] ?? 'zoom' ) . '">';
		foreach ( $urls as $image ) {
			$open = ! empty( $s['lightbox'] ) ? '<a href="' . esc_url( $image['full'] ) . '" class="pss-lightbox-link">' : '<div>';
			$close = ! empty( $s['lightbox'] ) ? '</a>' : '</div>';
			echo $open . '<img loading="lazy" src="' . esc_url( $image['thumb'] ) . '" alt="">' . $close;
		}
		echo '</div>';
	}
}
