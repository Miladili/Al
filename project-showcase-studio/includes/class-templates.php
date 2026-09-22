<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

/**
 * Starter Single Project layouts.
 *
 * Uses classic Elementor sections/columns so templates open even when the
 * Flexbox Container experiment is off. Widget names match registered PSS widgets.
 */
class Templates {
	private static $n = 0;

	private static function id( $key ) {
		self::$n++;
		return substr( md5( 'pss-tpl-' . $key . '-' . self::$n ), 0, 7 );
	}

	private static function section( $key, $settings, $widgets, $column_settings = array() ) {
		$elements = array();
		foreach ( (array) $widgets as $widget ) {
			$elements[] = $widget;
		}
		return array(
			'id'       => self::id( $key . '-s' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => $settings,
			'elements' => array(
				array(
					'id'       => self::id( $key . '-c' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => array_merge( array( '_column_size' => 100 ), $column_settings ),
					'elements' => $elements,
				),
			),
		);
	}

	private static function two_column( $key, $settings, $left, $right, $left_size = 58, $right_size = 42 ) {
		return array(
			'id'       => self::id( $key . '-s' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => $settings,
			'elements' => array(
				array(
					'id'       => self::id( $key . '-cl' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => array( '_column_size' => absint( $left_size ) ),
					'elements' => array_values( (array) $left ),
				),
				array(
					'id'       => self::id( $key . '-cr' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => array( '_column_size' => absint( $right_size ) ),
					'elements' => array_values( (array) $right ),
				),
			),
		);
	}

	private static function widget( $key, $type, $settings = array() ) {
		return array(
			'id'         => self::id( $key ),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	private static function heading( $key, $title, $size, $color ) {
		return self::widget(
			$key,
			'heading',
			array(
				'title'       => $title,
				'header_size' => $size,
				'title_color' => $color,
			)
		);
	}

	private static function text( $key, $html, $color ) {
		return self::widget(
			$key,
			'text-editor',
			array(
				'editor'     => $html,
				'text_color' => $color,
			)
		);
	}

	private static function spacer( $key, $height = 22 ) {
		return self::widget(
			$key,
			'spacer',
			array(
				'height' => array(
					'unit' => 'px',
					'size' => $height,
				),
			)
		);
	}

	private static function pad( $top, $bottom, $side = 48 ) {
		return array(
			'unit'     => 'px',
			'top'      => (string) $top,
			'right'    => (string) $side,
			'bottom'   => (string) $bottom,
			'left'     => (string) $side,
			'isLinked' => false,
		);
	}

	private static function dark( $extra = array() ) {
		return array_merge(
			array(
				'layout'                 => 'boxed',
				'content_width'          => array( 'unit' => '%', 'size' => 100 ),
				'background_background'  => 'classic',
				'background_color'       => '#101214',
				'padding'                => self::pad( 88, 88, 40 ),
			),
			$extra
		);
	}

	private static function cream( $extra = array() ) {
		return array_merge(
			array(
				'layout'                => 'boxed',
				'background_background' => 'classic',
				'background_color'      => '#F3EFE8',
				'padding'               => self::pad( 92, 96, 40 ),
			),
			$extra
		);
	}

	public static function modern_elements() {
		self::$n = 0;
		$gold    = '#D7C2A1';
		$ink     = '#17191D';
		$muted   = '#877D70';

		return array(
			self::section(
				'modern-hero',
				self::dark(
					array(
						'background_color' => '#0C0E10',
						'min_height'       => array( 'unit' => 'vh', 'size' => 88 ),
						'_css_classes'     => 'pss-template-section pss-template-modern-hero',
					)
				),
				array(
					self::widget( 'm-bc', 'pss_project_breadcrumbs', array() ),
					self::spacer( 'm-sp1', 28 ),
					self::widget(
						'm-hero',
						'pss_project_hero',
						array(
							'show_image'    => 'yes',
							'show_subtitle' => 'yes',
							'eyebrow'       => 'PROJECT / FEATURED',
						)
					),
					self::spacer( 'm-sp2', 24 ),
					self::widget(
						'm-btn',
						'button',
						array(
							'text'              => 'Explore the project',
							'link'              => array( 'url' => '#project-details' ),
							'align'             => 'left',
							'button_text_color' => '#111214',
							'background_color'  => $gold,
							'border_radius'     => array( 'unit' => 'px', 'size' => 999 ),
						)
					),
				)
			),
			self::two_column(
				'modern-intro',
				self::cream( array( '_css_classes' => 'pss-template-section pss-template-modern-intro' ) ),
				array(
					self::heading( 'm-kicker', 'THE IDEA', 'h6', $muted ),
					self::heading( 'm-intro-t', 'A project told through space, material and detail.', 'h2', $ink ),
					self::spacer( 'm-sp3', 12 ),
					self::widget( 'm-desc', 'pss_project_description', array() ),
					self::spacer( 'm-sp4', 18 ),
					self::widget( 'm-tags', 'pss_project_tags', array() ),
				),
				array(
					self::heading( 'm-meta-k', 'PROJECT FACTS', 'h6', $muted ),
					self::widget( 'm-meta', 'pss_project_meta', array( 'layout' => 'grid' ) ),
					self::spacer( 'm-sp4b', 18 ),
					self::widget( 'm-loc-side', 'pss_project_location', array() ),
				),
				58,
				42
			),
			self::section(
				'modern-stats',
				self::dark(
					array(
						'background_color' => '#161A1E',
						'padding'          => self::pad( 64, 64, 40 ),
						'_css_classes'     => 'pss-template-section pss-template-modern-stats',
					)
				),
				array(
					self::heading( 'm-stats-k', 'AT A GLANCE', 'h6', '#AAB0B7' ),
					self::spacer( 'm-sp5', 16 ),
					self::widget(
						'm-stats',
						'pss_project_stats',
						array(
							'fields' => "core:year\ncore:area\ncore:location\ncore:type",
						)
					),
				)
			),
			self::section(
				'modern-gallery',
				self::dark( array( '_css_classes' => 'pss-template-section pss-template-gallery-wall' ) ),
				array(
					self::heading( 'm-gal-t', 'SELECTED IMAGES', 'h2', '#FFFFFF' ),
					self::text( 'm-gal-c', '<p>Material, light, proportion and craft — presented as a visual sequence.</p>', '#AAB0B7' ),
					self::spacer( 'm-sp6', 16 ),
					self::widget( 'm-gal', 'pss_project_gallery', array( 'layout' => 'masonry' ) ),
				)
			),
			self::section(
				'modern-details',
				self::cream(
					array(
						'_css_classes' => 'pss-template-section pss-template-modern-details',
						'html_id'      => 'project-details',
					)
				),
				array(
					self::heading( 'm-dna-k', 'PROJECT DNA', 'h6', $muted ),
					self::heading( 'm-dna-t', 'Specifications, fields and the choices that make this project unique.', 'h2', $ink ),
					self::spacer( 'm-sp7', 22 ),
					self::widget( 'm-specs', 'pss_project_specifications', array() ),
					self::spacer( 'm-sp8', 22 ),
					self::widget( 'm-fields', 'pss_project_custom_fields', array() ),
					self::spacer( 'm-sp9', 22 ),
					self::widget( 'm-feat', 'pss_project_features', array() ),
					self::spacer( 'm-sp9b', 28 ),
					self::heading( 'm-plan-t', 'Floor plan', 'h3', $ink ),
					self::widget( 'm-plan', 'pss_project_floor_plan', array() ),
				)
			),
			self::section(
				'modern-materials',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#D9C8AF',
					'padding'               => self::pad( 86, 94, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-three-column',
				),
				array(
					self::widget( 'm-mat', 'pss_project_materials', array() ),
					self::widget( 'm-srv', 'pss_project_services', array() ),
					self::widget( 'm-loc', 'pss_project_location', array() ),
				)
			),
			self::section(
				'modern-transform',
				self::dark(
					array(
						'background_color' => '#0C0E10',
						'_css_classes'     => 'pss-template-section pss-template-media-split',
					)
				),
				array(
					self::heading( 'm-tr-t', 'TRANSFORMATION', 'h2', '#FFFFFF' ),
					self::spacer( 'm-sp10', 18 ),
					self::widget( 'm-ba', 'pss_project_before_after', array( 'height' => 560 ) ),
					self::spacer( 'm-sp11', 18 ),
					self::widget( 'm-vid', 'pss_project_video', array( 'ratio' => '16-9' ) ),
				)
			),
			self::section(
				'modern-products',
				self::cream(
					array(
						'background_color' => '#ECE6DD',
						'_css_classes'     => 'pss-template-section pss-template-products',
					)
				),
				array(
					self::heading( 'm-pr-k', 'OBJECTS / MATERIALS / PRODUCTS', 'h6', $muted ),
					self::heading( 'm-pr-t', 'Designed spaces can become shoppable stories.', 'h2', $ink ),
					self::spacer( 'm-sp12', 18 ),
					self::widget( 'm-pr', 'pss_project_products', array( 'layout' => 'grid' ) ),
				)
			),
			self::section(
				'modern-story',
				self::cream( array( 'background_color' => '#EFE8DC', '_css_classes' => 'pss-template-section pss-template-story' ) ),
				array(
					self::heading( 'm-st-k', 'NARRATIVE', 'h6', $muted ),
					self::heading( 'm-st-t', 'How the space was conceived.', 'h2', $ink ),
					self::spacer( 'm-st-sp', 18 ),
					self::widget( 'm-story', 'pss_project_story', array() ),
					self::spacer( 'm-st-sp2', 28 ),
					self::widget( 'm-sticky', 'pss_project_sticky', array() ),
				)
			),
			self::section(
				'modern-process',
				self::dark( array( 'background_color' => '#121416', '_css_classes' => 'pss-template-section pss-template-process' ) ),
				array(
					self::heading( 'm-prcs-k', 'PROCESS', 'h6', '#AAB0B7' ),
					self::heading( 'm-prcs-t', 'From brief to built form.', 'h2', '#FFFFFF' ),
					self::spacer( 'm-prcs-sp', 18 ),
					self::widget( 'm-process', 'pss_project_process', array( 'layout' => 'timeline' ) ),
					self::spacer( 'm-prcs-sp2', 22 ),
					self::widget( 'm-awards', 'pss_project_awards', array() ),
					self::widget( 'm-team', 'pss_project_team', array() ),
				)
			),
			self::section(
				'modern-marquee',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#0C0E10',
					'padding'               => self::pad( 28, 28, 0 ),
					'_css_classes'          => 'pss-template-section pss-template-marquee',
				),
				array(
					self::widget( 'm-marquee', 'pss_project_marquee', array() ),
				)
			),
			self::section(
				'modern-cta',
				self::dark(
					array(
						'background_color' => '#17191D',
						'_css_classes'     => 'pss-template-section pss-template-cta',
					)
				),
				array(
					self::heading( 'm-cta-t', 'Need a project with this level of detail?', 'h2', '#FFFFFF' ),
					self::text( 'm-cta-c', '<p>Use this section as your enquiry, contact or project hand-off area.</p>', '#B8BDC4' ),
					self::widget( 'm-inq', 'pss_project_inquiry', array() ),
				)
			),
			self::section(
				'modern-related',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#FFFFFF',
					'padding'               => self::pad( 92, 96, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-related',
				),
				array(
					self::heading( 'm-rel-k', 'MORE PROJECTS', 'h6', $muted ),
					self::heading( 'm-rel-t', 'Continue exploring.', 'h2', $ink ),
					self::widget( 'm-scroll', 'pss_project_scroll', array( 'limit' => 6, 'eyebrow' => 'Continue through the studio' ) ),
					self::spacer( 'm-sp13', 28 ),
					self::widget(
						'm-slider',
						'pss_project_slider',
						array(
							'limit'           => 6,
							'preset'          => 'cinematic',
							'title_placement' => 'overlay',
							'meta_placement'  => 'overlay',
							'full_width'      => '',
							'visible'         => 1.2,
							'arrows'          => 'yes',
							'dots'            => 'yes',
							'loop'            => 'yes',
							'peek'            => 'yes',
						)
					),
					self::spacer( 'm-sp-slider', 28 ),
					self::widget( 'm-rel', 'pss_related_projects', array( 'limit' => 3, 'layout' => 'cards' ) ),
					self::spacer( 'm-sp13b', 28 ),
					self::widget( 'm-share', 'pss_project_share', array() ),
					self::widget( 'm-nav', 'pss_project_navigation', array() ),
				)
			),
		);
	}

	public static function premium_elements() {
		self::$n = 0;
		$gold    = '#CDB995';
		$ivory   = '#F5F0E7';
		$ink     = '#191714';

		return array(
			self::section(
				'prem-hero',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#11100E',
					'padding'               => self::pad( 92, 92, 40 ),
					'min_height'            => array( 'unit' => 'vh', 'size' => 92 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-hero',
				),
				array(
					self::widget( 'p-bc', 'pss_project_breadcrumbs', array() ),
					self::spacer( 'p-sp1', 34 ),
					self::widget(
						'p-hero',
						'pss_project_hero',
						array(
							'show_image'    => 'yes',
							'show_subtitle' => 'yes',
							'eyebrow'       => 'PRIVATE RESIDENCE / SIGNATURE PROJECT',
						)
					),
					self::spacer( 'p-sp2', 28 ),
					self::widget(
						'p-btn',
						'button',
						array(
							'text'              => 'Enter the project',
							'link'              => array( 'url' => '#premium-story' ),
							'align'             => 'left',
							'button_text_color' => '#11100E',
							'background_color'  => '#D6C29D',
							'border_radius'     => array( 'unit' => 'px', 'size' => 999 ),
						)
					),
				)
			),
			self::section(
				'prem-bar',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => $gold,
					'padding'               => self::pad( 28, 28, 40 ),
					'_css_classes'          => 'pss-template-premium-bar',
				),
				array(
					self::widget( 'p-meta', 'pss_project_meta', array( 'layout' => 'inline' ) ),
				)
			),
			self::two_column(
				'prem-story',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#171513',
					'padding'               => self::pad( 92, 92, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-story',
					'html_id'               => 'premium-story',
				),
				array(
					self::heading( 'p-st-k', 'A QUIET, MATERIAL-LED APPROACH', 'h6', $gold ),
					self::heading( 'p-st-t', 'Crafted details. Calm proportions. A space designed to last.', 'h2', '#F6F0E6' ),
					self::widget( 'p-desc', 'pss_project_description', array() ),
				),
				array(
					self::widget(
						'p-stats',
						'pss_project_stats',
						array(
							'fields' => "core:year\ncore:area\ncore:location\ncore:type",
						)
					),
					self::spacer( 'p-sp3', 18 ),
					self::widget( 'p-tags', 'pss_project_tags', array() ),
				),
				62,
				38
			),
			self::section(
				'prem-edit',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => $ivory,
					'padding'               => self::pad( 92, 100, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-columns',
				),
				array(
					self::heading( 'p-ed-t', 'The brief, the material and the finished result.', 'h2', $ink ),
					self::widget( 'p-specs', 'pss_project_specifications', array() ),
					self::spacer( 'p-sp4', 22 ),
					self::widget( 'p-fields', 'pss_project_custom_fields', array() ),
				)
			),
			self::section(
				'prem-gal',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#0D0E10',
					'padding'               => self::pad( 92, 92, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-gallery',
				),
				array(
					self::heading( 'p-gal-k', 'THE VISUAL SEQUENCE', 'h6', $gold ),
					self::heading( 'p-gal-t', 'A cinematic walk through the space.', 'h2', '#FFFFFF' ),
					self::spacer( 'p-sp5', 24 ),
					self::widget( 'p-gal', 'pss_project_gallery', array( 'layout' => 'masonry' ) ),
				)
			),
			self::section(
				'prem-mat',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#EAE1D3',
					'padding'               => self::pad( 86, 94, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-columns',
				),
				array(
					self::heading( 'p-mat-t', 'Materials / Services / Location', 'h2', $ink ),
					self::widget( 'p-mat', 'pss_project_materials', array() ),
					self::widget( 'p-srv', 'pss_project_services', array() ),
					self::widget( 'p-loc', 'pss_project_location', array() ),
				)
			),
			self::section(
				'prem-tr',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#171513',
					'padding'               => self::pad( 86, 94, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-media',
				),
				array(
					self::heading( 'p-tr-t', 'TRANSFORMATION', 'h2', '#F6F0E6' ),
					self::widget( 'p-ba', 'pss_project_before_after', array( 'height' => 600 ) ),
					self::spacer( 'p-sp6', 18 ),
					self::widget( 'p-vid', 'pss_project_video', array( 'ratio' => '16-9' ) ),
				)
			),
			self::section(
				'prem-pr',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#F8F2E9',
					'padding'               => self::pad( 86, 94, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-products',
				),
				array(
					self::heading( 'p-pr-k', 'COLLECTED OBJECTS', 'h6', '#8B7B60' ),
					self::heading( 'p-pr-t', 'Pieces that complete the story.', 'h2', $ink ),
					self::spacer( 'p-sp7', 18 ),
					self::widget( 'p-pr', 'pss_project_products', array( 'layout' => 'carousel' ) ),
				)
			),
			self::section(
				'prem-story',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#11100E',
					'padding'               => self::pad( 92, 92, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-story-block',
				),
				array(
					self::heading( 'p-nar-k', 'THE NARRATIVE', 'h6', $gold ),
					self::heading( 'p-nar-t', 'A slower reading of the space.', 'h2', '#F6F0E6' ),
					self::widget( 'p-story', 'pss_project_story', array() ),
					self::spacer( 'p-nar-sp', 24 ),
					self::widget( 'p-sticky', 'pss_project_sticky', array( 'layout' => 'image-right' ) ),
					self::spacer( 'p-nar-sp2', 24 ),
					self::widget( 'p-process', 'pss_project_process', array( 'layout' => 'steps' ) ),
					self::widget( 'p-awards', 'pss_project_awards', array() ),
					self::widget( 'p-team', 'pss_project_team', array() ),
					self::widget( 'p-marquee', 'pss_project_marquee', array() ),
				)
			),
			self::section(
				'prem-cta',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#0C0D0E',
					'padding'               => self::pad( 86, 94, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-premium-cta',
				),
				array(
					self::heading( 'p-cta-t', 'Ready for your own signature project?', 'h2', '#FFFFFF' ),
					self::text( 'p-cta-c', '<p>Turn this final section into your enquiry, booking or consultation call-to-action.</p>', '#B9B0A3' ),
					self::widget( 'p-inq', 'pss_project_inquiry', array() ),
				)
			),
			self::section(
				'prem-rel',
				array(
					'layout'                => 'boxed',
					'background_background' => 'classic',
					'background_color'      => '#171513',
					'padding'               => self::pad( 86, 94, 40 ),
					'_css_classes'          => 'pss-template-section pss-template-related',
				),
				array(
					self::heading( 'p-rel-k', 'SELECTED WORK', 'h6', $gold ),
					self::heading( 'p-rel-t', 'More spaces worth exploring.', 'h2', '#F6F0E6' ),
					self::widget( 'p-scroll', 'pss_project_scroll', array( 'limit' => 6, 'eyebrow' => 'Selected work', 'easing' => 'cinematic' ) ),
					self::spacer( 'p-sp8', 28 ),
					self::widget( 'p-rel', 'pss_related_projects', array( 'limit' => 3, 'layout' => 'cards' ) ),
					self::spacer( 'p-sp8b', 28 ),
					self::widget( 'p-share', 'pss_project_share', array() ),
					self::widget( 'p-nav', 'pss_project_navigation', array() ),
				)
			),
		);
	}
}
