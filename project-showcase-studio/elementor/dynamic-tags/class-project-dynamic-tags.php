<?php
namespace PSS\Elementor\DynamicTags;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
	return;
}

abstract class Base extends \Elementor\Core\DynamicTags\Tag {
	public function get_group() {
		return 'pss-project';
	}

	protected function project_id() {
		return \PSS\get_project_id();
	}
}

class Project_Title extends Base {
	public function get_name() {
		return 'pss-project-title';
	}
	public function get_title() {
		return esc_html__( 'Project Title', 'project-showcase-studio' );
	}
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}
	public function render() {
		$id = $this->project_id();
		if ( $id ) {
			echo esc_html( get_the_title( $id ) );
		}
	}
}

class Project_Subtitle extends Base {
	public function get_name() {
		return 'pss-project-subtitle';
	}
	public function get_title() {
		return esc_html__( 'Project Subtitle', 'project-showcase-studio' );
	}
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}
	public function render() {
		$id = $this->project_id();
		if ( $id ) {
			echo esc_html( \PSS\get_meta( $id, '_pss_subtitle' ) );
		}
	}
}

class Project_URL extends Base {
	public function get_name() {
		return 'pss-project-url';
	}
	public function get_title() {
		return esc_html__( 'Project URL', 'project-showcase-studio' );
	}
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY, \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}
	public function render() {
		$id = $this->project_id();
		if ( $id ) {
			echo esc_url( get_permalink( $id ) );
		}
	}
}

if ( class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
	class Project_Image extends \Elementor\Core\DynamicTags\Data_Tag {
		public function get_name() {
			return 'pss-project-image';
		}
		public function get_title() {
			return esc_html__( 'Project Featured Image', 'project-showcase-studio' );
		}
		public function get_group() {
			return 'pss-project';
		}
		public function get_categories() {
			return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
		}
		protected function project_id() {
			return \PSS\get_project_id();
		}
		public function get_value( array $options = array() ) {
			$id = $this->project_id();
			if ( ! $id ) {
				return array();
			}
			return array(
				'id'  => get_post_thumbnail_id( $id ),
				'url' => get_the_post_thumbnail_url( $id, 'full' ),
			);
		}
	}
} else {
	class Project_Image extends Base {
		public function get_name() {
			return 'pss-project-image';
		}
		public function get_title() {
			return esc_html__( 'Project Featured Image', 'project-showcase-studio' );
		}
		public function get_categories() {
			return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
		}
		public function render() {
			$id = $this->project_id();
			if ( $id ) {
				echo esc_url( (string) get_the_post_thumbnail_url( $id, 'full' ) );
			}
		}
	}
}

class Project_Content extends Base {
	public function get_name() {
		return 'pss-project-content';
	}
	public function get_title() {
		return esc_html__( 'Project Description', 'project-showcase-studio' );
	}
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}
	public function render() {
		$id = $this->project_id();
		if ( ! $id ) {
			return;
		}
		echo wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) );
	}
}

class Project_Meta extends Base {
	public function get_name() {
		return 'pss-project-meta';
	}
	public function get_title() {
		return esc_html__( 'Project Meta', 'project-showcase-studio' );
	}
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}
	public function render() {
		$id = $this->project_id();
		if ( ! $id ) {
			return;
		}
		$parts = array(
			\PSS\get_project_taxonomy_value( $id, 'pss_project_type' ),
			\PSS\get_project_taxonomy_value( $id, 'pss_project_style' ),
			\PSS\get_project_taxonomy_value( $id, 'pss_project_location' ),
			\PSS\get_meta( $id, '_pss_year' ),
			\PSS\get_meta( $id, '_pss_area' ),
		);
		echo esc_html( implode( ' · ', array_filter( $parts ) ) );
	}
}

class Project_Field extends Base {
	public function get_name() {
		return 'pss-project-field';
	}
	public function get_title() {
		return esc_html__( 'Project Custom Field', 'project-showcase-studio' );
	}
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY, \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY );
	}
	protected function register_controls() {
		$options = array();
		foreach ( \PSS\get_field_definitions() as $field ) {
			$options[ sanitize_key( $field['key'] ) ] = $field['label'] . ' (' . $field['key'] . ')';
		}
		$this->add_control(
			'field_key',
			array(
				'label'   => esc_html__( 'Field', 'project-showcase-studio' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
			)
		);
	}
	public function render() {
		$key = sanitize_key( (string) $this->get_settings( 'field_key' ) );
		$id  = $this->project_id();
		if ( ! $id || ! $key ) {
			return;
		}
		$field = \PSS\get_field_definition( $id, $key );
		$value = \PSS\get_field_value( $id, $key, '' );
		if ( '' === $value || array() === $value ) {
			return;
		}
		$type = is_array( $field ) ? ( $field['type'] ?? 'text' ) : 'text';
		if ( is_scalar( $value ) ) {
			echo esc_html( (string) $value );
		} else {
			echo wp_kses_post( \PSS\render_field_value( $value, $type, (array) $field ) );
		}
	}
}

class Project_Location extends Base {
	public function get_name() { return 'pss-project-location'; }
	public function get_title() { return esc_html__( 'Project Location', 'project-showcase-studio' ); }
	public function get_categories() { return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ); }
	public function render() {
		$id = $this->project_id();
		if ( $id ) {
			echo esc_html( \PSS\get_project_taxonomy_value( $id, 'pss_project_location' ) );
		}
	}
}

class Project_Date extends Base {
	public function get_name() { return 'pss-project-date'; }
	public function get_title() { return esc_html__( 'Project Date', 'project-showcase-studio' ); }
	public function get_categories() { return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ); }
	public function render() {
		$id = $this->project_id();
		if ( ! $id ) { return; }
		$year = \PSS\get_meta( $id, '_pss_year' );
		echo esc_html( $year ? $year : get_the_date( '', $id ) );
	}
}
