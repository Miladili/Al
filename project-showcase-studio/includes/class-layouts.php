<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

class Layouts {
	public static function init() {
		add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
		add_action( 'init', array( __CLASS__, 'register_cpt' ), 6 );
		add_action( 'admin_menu', array( __CLASS__, 'submenu' ) );
		add_filter( 'parent_file', array( __CLASS__, 'parent_file' ) );
		add_filter( 'submenu_file', array( __CLASS__, 'submenu_file' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );
		add_action( 'save_post_' . PSS_LAYOUT_CPT, array( __CLASS__, 'save' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_action' ), 10, 2 );
		add_action( 'admin_post_pss_duplicate_layout', array( __CLASS__, 'duplicate_layout' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade_starters' ), 20 );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'disable_block_editor' ), 10, 2 );
	}

	private static function is_elementor_request() {
		$action = '';
		if ( isset( $_GET['action'] ) ) {
			$action = sanitize_key( wp_unslash( $_GET['action'] ) );
		} elseif ( isset( $_POST['action'] ) ) {
			$action = sanitize_key( wp_unslash( $_POST['action'] ) );
		}
		if ( 'elementor' === $action || 0 === strpos( $action, 'elementor' ) ) {
			return true;
		}
		if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) ) {
			return true;
		}
		return defined( 'ELEMENTOR_VERSION' ) && wp_doing_ajax() && $action && false !== strpos( $action, 'elementor' );
	}

	public static function admin_body_class( $classes ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) { return $classes; }
		$allowed = array( PSS_PROJECT_CPT, PSS_LAYOUT_CPT );
		if ( in_array( $screen->post_type, $allowed, true ) || false !== strpos( (string) $screen->id, 'pss-project-fields' ) || false !== strpos( (string) $screen->id, 'pss-project-settings' ) || false !== strpos( (string) $screen->id, 'pss-layouts' ) ) {
			$classes .= ' pss-admin-screen';
		}
		return $classes;
	}

	public static function register_cpt() {
		register_post_type(
			PSS_LAYOUT_CPT,
			array(
				'labels' => array(
					'name' => 'Single Layouts',
					'singular_name' => 'Single Layout',
					'add_new' => 'Add New Layout',
					'add_new_item' => 'Add New Single Layout',
					'edit_item' => 'Edit Single Layout',
				),
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'capability_type' => 'post',
				'map_meta_cap' => true,
				'supports' => array( 'title', 'revisions' ),
			)
		);
	}

	public static function disable_block_editor( $use_block_editor, $post_type ) {
		return PSS_LAYOUT_CPT === $post_type ? false : $use_block_editor;
	}

	public static function submenu() {
		add_submenu_page(
			'edit.php?post_type=' . PSS_PROJECT_CPT,
			'Single Layouts',
			'Single Layouts',
			'edit_posts',
			'pss-layouts',
			array( __CLASS__, 'hub_page' )
		);
	}

	public static function parent_file( $parent ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && PSS_LAYOUT_CPT === $screen->post_type ) {
			return 'edit.php?post_type=' . PSS_PROJECT_CPT;
		}
		return $parent;
	}

	public static function submenu_file( $submenu ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return $submenu;
		}
		$id = (string) $screen->id;
		if ( PSS_LAYOUT_CPT === $screen->post_type || false !== strpos( $id, 'pss-layouts' ) || false !== strpos( $id, 'pss-project-settings' ) ) {
			return 'pss-layouts';
		}
		return $submenu;
	}

	public static function assets( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return;
		}
		$is_hub = false !== strpos( (string) $screen->id, 'pss-layouts' ) || false !== strpos( (string) $hook, 'pss-layouts' );
		if ( ! $is_hub && PSS_LAYOUT_CPT !== $screen->post_type ) {
			return;
		}
		wp_enqueue_style( 'pss-admin', PSS_URL . 'admin/assets/admin.css', array(), PSS_VERSION );
		if ( PSS_LAYOUT_CPT === $screen->post_type ) {
			wp_enqueue_script( 'pss-layouts', PSS_URL . 'admin/assets/layouts.js', array(), PSS_VERSION, true );
		}
	}

	public static function meta_box() {
		add_meta_box( 'pss_layout_conditions', 'Single Project Layout Settings', array( __CLASS__, 'render_meta' ), PSS_LAYOUT_CPT, 'normal', 'high' );
	}

	private static function condition_data() {
		$data = array(
			'all' => array( array( 'id' => '', 'name' => 'All Projects' ) ),
			'project' => array(),
			'category' => array(),
			'style' => array(),
			'location' => array(),
			'type' => array(),
			'field' => array(),
		);
		foreach ( get_posts( array( 'post_type' => PSS_PROJECT_CPT, 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => 300 ) ) as $project ) {
			$data['project'][] = array( 'id' => (string) $project->ID, 'name' => $project->post_title );
		}
		$map = array( 'category' => 'pss_project_category', 'style' => 'pss_project_style', 'location' => 'pss_project_location', 'type' => 'pss_project_type' );
		foreach ( $map as $key => $taxonomy ) {
			foreach ( get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) ) as $term ) {
				if ( ! is_wp_error( $term ) ) $data[ $key ][] = array( 'id' => (string) $term->term_id, 'name' => $term->name );
			}
		}
		foreach ( get_field_definitions() as $field ) {
			$data['field'][] = array( 'id' => sanitize_key( $field['key'] ?? '' ), 'name' => (string) ( $field['label'] ?? $field['key'] ?? '' ) );
		}
		return $data;
	}

	public static function render_meta( $post ) {
		wp_nonce_field( 'pss_save_layout', 'pss_layout_nonce' );
		$conditions = get_meta( $post->ID, '_pss_conditions', array() );
		$logic = get_layout_condition_logic( $post->ID );
		$preview = absint( get_meta( $post->ID, '_pss_preview_project', 0 ) );
		?>
		<div class="pss-layout-settings">
			<p><strong>Preview Project</strong></p>
			<select name="pss_preview_project" class="widefat">
				<option value="0">Choose a project for Elementor preview</option>
				<?php foreach ( get_posts( array( 'post_type' => PSS_PROJECT_CPT, 'posts_per_page' => -1, 'post_status' => array( 'publish', 'draft' ) ) ) as $project ) : ?>
				<option value="<?php echo esc_attr( $project->ID ); ?>" <?php selected( $preview, $project->ID ); ?>><?php echo esc_html( $project->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<hr>
			<p><strong>Display Conditions</strong></p>
			<p>
				<label for="pss_condition_logic"><strong>Match logic</strong></label>
				<select id="pss_condition_logic" name="pss_condition_logic">
					<option value="all" <?php selected( $logic, 'all' ); ?>>Match ALL include rules</option>
					<option value="any" <?php selected( $logic, 'any' ); ?>>Match ANY include rule</option>
				</select>
			</p>
			<div id="pss-condition-list">
				<?php foreach ( $conditions as $i => $condition ) self::condition_row( $i, $condition ); ?>
			</div>
			<button type="button" class="button" id="pss-add-condition">+ Add Condition</button>
			<p class="description">Exclude rules always veto a layout. Specific Project rules automatically receive the highest specificity, followed by custom-field, project type, style, location and category rules. The Priority field can further refine the winner.</p>
			<script>window.PSSConditionData=<?php echo wp_json_encode( self::condition_data() ); ?>;</script>
			<?php if ( 'auto-draft' !== $post->post_status ) : ?>
				<?php if ( ! class_exists( '\Elementor\Plugin' ) ) : ?>
					<p class="description">Activate Elementor to open this layout in the real Elementor editor. The layout manager record itself is never edited as an Elementor page.</p>
				<?php else : ?>
					<?php $elementor_url = self::get_elementor_edit_url( $post->ID ); ?>
					<?php if ( $elementor_url ) : ?>
						<p><a class="button button-primary pss-layout-elementor-button" href="<?php echo esc_url( $elementor_url ); ?>">Edit this Layout with Elementor</a></p>
					<?php else : ?>
						<p class="description">Save this layout first. The Elementor design document will then be created.</p>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function condition_row( $i, $c ) {
		$type = sanitize_key( $c['type'] ?? 'all' );
		$mode = ( $c['mode'] ?? 'include' ) === 'exclude' ? 'exclude' : 'include';
		$value = $c['value'] ?? '';
		$priority = absint( $c['priority'] ?? 10 );
		$field_key = sanitize_key( $c['field_key'] ?? '' );
		$operator = sanitize_key( $c['operator'] ?? 'equals' );
		$data = self::condition_data();
		?>
		<div class="pss-condition-row" data-index="<?php echo esc_attr( $i ); ?>">
			<select name="pss_conditions[<?php echo esc_attr( $i ); ?>][mode]">
				<option value="include" <?php selected( $mode, 'include' ); ?>>Include</option>
				<option value="exclude" <?php selected( $mode, 'exclude' ); ?>>Exclude</option>
			</select>
			<select name="pss_conditions[<?php echo esc_attr( $i ); ?>][type]" class="pss-condition-type">
				<?php foreach ( array( 'all' => 'All Projects', 'project' => 'Specific Project', 'category' => 'Category', 'style' => 'Style', 'location' => 'Location', 'type' => 'Project Type', 'field' => 'Custom Field' ) as $option => $label ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $type, $option ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="pss_conditions[<?php echo esc_attr( $i ); ?>][value]" class="pss-condition-value" data-current="<?php echo esc_attr( $value ); ?>">
				<?php foreach ( $data[ $type ] ?? array() as $option ) : ?>
					<option value="<?php echo esc_attr( $option['id'] ); ?>" <?php selected( (string) $value, (string) $option['id'] ); ?>><?php echo esc_html( $option['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="pss_conditions[<?php echo esc_attr( $i ); ?>][field_key]" class="pss-condition-field" data-current="<?php echo esc_attr( $field_key ); ?>">
				<?php foreach ( $data['field'] as $field_option ) : ?>
					<option value="<?php echo esc_attr( $field_option['id'] ); ?>" <?php selected( $field_key, $field_option['id'] ); ?>><?php echo esc_html( $field_option['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="pss_conditions[<?php echo esc_attr( $i ); ?>][operator]" class="pss-condition-operator">
				<?php foreach ( array( 'equals' => 'Equals', 'not_equals' => 'Does not equal', 'contains' => 'Contains', 'not_contains' => 'Does not contain', 'greater' => 'Greater than', 'less' => 'Less than', 'greater_equal' => 'Greater or equal', 'less_equal' => 'Less or equal', 'empty' => 'Is empty', 'not_empty' => 'Is not empty' ) as $op => $label ) : ?>
					<option value="<?php echo esc_attr( $op ); ?>" <?php selected( $operator, $op ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="text" name="pss_conditions[<?php echo esc_attr( $i ); ?>][field_value]" class="pss-condition-field-value" value="<?php echo esc_attr( $type === 'field' ? $value : '' ); ?>" placeholder="Field value">
			<input type="number" name="pss_conditions[<?php echo esc_attr( $i ); ?>][priority]" value="<?php echo esc_attr( $priority ); ?>" min="0" max="10000" title="Additional priority">
			<button type="button" class="button-link-delete pss-remove-condition">Remove</button>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['pss_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pss_layout_nonce'] ) ), 'pss_save_layout' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$logic = sanitize_key( $_POST['pss_condition_logic'] ?? 'all' );
		if ( ! in_array( $logic, array( 'all', 'any' ), true ) ) $logic = 'all';
		update_post_meta( $post_id, '_pss_condition_logic', $logic );

		$raw = isset( $_POST['pss_conditions'] ) ? wp_unslash( $_POST['pss_conditions'] ) : array();
		$conditions = array();
		$valid_types = array( 'all', 'project', 'category', 'style', 'location', 'type', 'field' );
		$valid_ops = array( 'equals', 'not_equals', 'contains', 'not_contains', 'greater', 'less', 'greater_equal', 'less_equal', 'empty', 'not_empty' );
		foreach ( (array) $raw as $row ) {
			$type = sanitize_key( $row['type'] ?? 'all' );
			if ( ! in_array( $type, $valid_types, true ) ) $type = 'all';
			$condition = array(
				'mode' => in_array( $row['mode'] ?? '', array( 'include', 'exclude' ), true ) ? $row['mode'] : 'include',
				'type' => $type,
				'value' => '',
				'field_key' => sanitize_key( $row['field_key'] ?? '' ),
				'operator' => in_array( $row['operator'] ?? '', $valid_ops, true ) ? sanitize_key( $row['operator'] ) : 'equals',
				'priority' => absint( $row['priority'] ?? 10 ),
			);
			if ( 'field' === $type ) {
				$condition['value'] = sanitize_text_field( $row['field_value'] ?? '' );
				if ( ! $condition['field_key'] ) continue;
			} else {
				$condition['value'] = sanitize_text_field( $row['value'] ?? '' );
			}
			if ( 'all' === $type ) $condition['value'] = '';
			$conditions[] = $condition;
		}
		update_post_meta( $post_id, '_pss_conditions', $conditions );
		update_post_meta( $post_id, '_pss_preview_project', absint( $_POST['pss_preview_project'] ?? 0 ) );
		self::ensure_elementor_document( $post_id );
	}

	public static function row_action( $actions, $post ) {
		if ( PSS_LAYOUT_CPT === $post->post_type && in_array( $post->post_status, array( 'publish', 'draft' ), true ) ) {
				$url = self::get_elementor_edit_url( $post->ID );
			if ( $url ) {
				$actions['pss_elementor'] = '<a href="' . esc_url( $url ) . '">Edit with Elementor</a>';
			}
			$duplicate_url = wp_nonce_url( admin_url( 'admin-post.php?action=pss_duplicate_layout&layout_id=' . $post->ID ), 'pss_duplicate_layout_' . $post->ID );
			$actions['pss_duplicate'] = '<a href="' . esc_url( $duplicate_url ) . '">Duplicate</a>';
		}
		return $actions;
	}

	public static function duplicate_layout() {
		$layout_id = absint( $_GET['layout_id'] ?? 0 );
		if ( ! $layout_id || ! current_user_can( 'edit_post', $layout_id ) ) wp_die( 'Unauthorized' );
		check_admin_referer( 'pss_duplicate_layout_' . $layout_id );
		$source = get_post( $layout_id );
		if ( ! $source || PSS_LAYOUT_CPT !== $source->post_type ) wp_die( 'Invalid layout' );
		$new_id = wp_insert_post( array( 'post_type' => PSS_LAYOUT_CPT, 'post_status' => 'draft', 'post_title' => $source->post_title . ' Copy' ), true );
		if ( is_wp_error( $new_id ) ) wp_die( esc_html( $new_id->get_error_message() ) );
		foreach ( get_post_meta( $layout_id ) as $key => $values ) {
			if ( in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) continue;
			foreach ( $values as $value ) add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
		}
		update_post_meta( $new_id, '_pss_document_mode', 'native' );
		$source_template = absint( get_post_meta( $layout_id, '_pss_elementor_template_id', true ) );
		delete_post_meta( $new_id, '_pss_elementor_template_id' );
		$new_template = self::ensure_elementor_document( $new_id );
		if ( $source_template && $new_template && $source_template !== $new_template ) {
			$data = get_post_meta( $source_template, '_elementor_data', true );
			if ( $data ) {
				update_post_meta( $new_template, '_elementor_data', wp_slash( is_string( $data ) ? $data : wp_json_encode( $data ) ) );
			}
		}
		wp_safe_redirect( admin_url( 'post.php?post=' . $new_id . '&action=edit' ) );
		exit;
	}

	public static function get_elementor_template_id( $layout_id ) {
		return self::ensure_elementor_document( $layout_id );
	}

	private static function create_library_document( $layout_id ) {
		$title      = get_the_title( $layout_id );
		$post_title = $title ? $title . ' — Design' : 'Project Layout Design';

		if ( ! post_type_exists( 'elementor_library' ) ) {
			return 0;
		}

		$template_id = wp_insert_post(
			array(
				'post_type'   => 'elementor_library',
				'post_status' => 'publish',
				'post_title'  => $post_title,
			),
			true
		);
		if ( is_wp_error( $template_id ) || ! $template_id ) {
			return 0;
		}

		self::prepare_library_document( $template_id );

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->documents ) ) {
			try {
				$document = \Elementor\Plugin::$instance->documents->get( absint( $template_id ), false );
				if ( $document && method_exists( $document, 'set_is_built_with_elementor' ) ) {
					$document->set_is_built_with_elementor( true );
				}
			} catch ( \Throwable $e ) {
				error_log( '[PSS] Could not mark library document: ' . $e->getMessage() );
			}
		}

		return absint( $template_id );
	}

	public static function prepare_library_document( $template_id ) {
		$template_id = absint( $template_id );
		if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) ) {
			return;
		}
		update_post_meta( $template_id, '_elementor_edit_mode', 'builder' );
		if ( ! get_post_meta( $template_id, '_elementor_template_type', true ) ) {
			update_post_meta( $template_id, '_elementor_template_type', 'page' );
		}
		if ( ! get_post_meta( $template_id, '_elementor_version', true ) ) {
			update_post_meta( $template_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : PSS_VERSION );
		}
		if ( taxonomy_exists( 'elementor_library_type' ) ) {
			$terms = wp_get_object_terms( $template_id, 'elementor_library_type', array( 'fields' => 'slugs' ) );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				wp_set_object_terms( $template_id, 'page', 'elementor_library_type' );
			}
		}
	}

	private static function strip_manager_elementor_meta( $layout_id ) {
		$layout_id = absint( $layout_id );
		if ( ! $layout_id || PSS_LAYOUT_CPT !== get_post_type( $layout_id ) ) {
			return;
		}
		foreach ( array( '_elementor_edit_mode', '_elementor_template_type', '_elementor_data', '_elementor_css', '_elementor_page_settings', '_elementor_controls_usage', '_elementor_version' ) as $key ) {
			delete_post_meta( $layout_id, $key );
		}
	}

	/**
	 * Ensure the design side of a manager layout is a normal Elementor Library
	 * template. The manager record itself is never opened as an Elementor document.
	 */
	public static function ensure_elementor_document( $layout_id ) {
		try {
			return self::ensure_elementor_document_inner( $layout_id );
		} catch ( \Throwable $e ) {
			error_log( '[PSS] ensure_elementor_document: ' . $e->getMessage() );
			return 0;
		}
	}

	private static function ensure_elementor_document_inner( $layout_id ) {
		$layout_id = absint( $layout_id );
		if ( ! $layout_id || PSS_LAYOUT_CPT !== get_post_type( $layout_id ) ) {
			return 0;
		}

		$template_id = absint( get_post_meta( $layout_id, '_pss_elementor_template_id', true ) );
		if ( $template_id && 'elementor_library' !== get_post_type( $template_id ) ) {
			$template_id = 0;
		}

		$legacy_data     = get_post_meta( $layout_id, '_elementor_data', true );
		$legacy_settings = get_post_meta( $layout_id, '_elementor_page_settings', true );

		if ( ! $template_id ) {
			$template_id = self::create_library_document( $layout_id );
			if ( ! $template_id ) {
				return 0;
			}
			update_post_meta( $layout_id, '_pss_elementor_template_id', $template_id );
		}

		update_post_meta( $template_id, '_pss_manager_layout_id', $layout_id );
		self::prepare_library_document( $template_id );
		if ( $legacy_settings && ! get_post_meta( $template_id, '_elementor_page_settings', true ) ) {
			update_post_meta( $template_id, '_elementor_page_settings', maybe_unserialize( $legacy_settings ) );
		}

		$data = get_post_meta( $template_id, '_elementor_data', true );
		if ( empty( $data ) || '[]' === $data ) {
			if ( is_string( $legacy_data ) && '' !== trim( $legacy_data ) && '[]' !== trim( $legacy_data ) ) {
				update_post_meta( $template_id, '_elementor_data', wp_slash( $legacy_data ) );
			} else {
				$seed_style = sanitize_key( get_post_meta( $layout_id, '_pss_seed_style', true ) );
				if ( in_array( $seed_style, array( 'modern', 'premium' ), true ) ) {
					Elementor::seed_layout_content( $template_id, $seed_style );
				} else {
					update_post_meta( $template_id, '_elementor_data', wp_slash( wp_json_encode( array() ) ) );
				}
			}
		}

		self::strip_manager_elementor_meta( $layout_id );

		return absint( $template_id );
	}

	public static function get_elementor_edit_url( $layout_id ) {
		try {
			return self::get_elementor_edit_url_inner( $layout_id );
		} catch ( \Throwable $e ) {
			error_log( '[PSS] get_elementor_edit_url: ' . $e->getMessage() );
			return '';
		}
	}

	private static function get_elementor_edit_url_inner( $layout_id ) {
		$layout_id = absint( $layout_id );
		if ( ! $layout_id || PSS_LAYOUT_CPT !== get_post_type( $layout_id ) ) {
			return '';
		}
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		$template_id = absint( get_post_meta( $layout_id, '_pss_elementor_template_id', true ) );
		if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) ) {
			$template_id = self::ensure_elementor_document( $layout_id );
		}
		if ( ! $template_id ) {
			return '';
		}

		$url = '';
		if ( isset( \Elementor\Plugin::$instance->documents ) ) {
			try {
				$documents = \Elementor\Plugin::$instance->documents;
				$document  = method_exists( $documents, 'get' ) ? $documents->get( $template_id, false ) : null;
				if ( $document && method_exists( $document, 'get_edit_url' ) ) {
					$url = $document->get_edit_url();
				}
			} catch ( \Throwable $e ) {
				$url = '';
			}
		}
		if ( ! $url ) {
			$url = add_query_arg(
				array(
					'post'   => $template_id,
					'action' => 'elementor',
				),
				admin_url( 'post.php' )
			);
		}

		$preview = absint( get_post_meta( $layout_id, '_pss_preview_project', true ) );
		if ( $preview ) {
			$url = add_query_arg( 'pss_preview_project', $preview, $url );
		}
		return $url;
	}

	public static function sync_elementor_documents() {
		self::migrate_legacy_documents();
	}

	public static function delete_elementor_document( $post_id ) {
		return;
	}

	public static function migrate_legacy_documents() {
		if ( self::is_elementor_request() ) {
			return;
		}
		$ids = get_posts(
			array(
				'post_type'      => PSS_LAYOUT_CPT,
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $ids as $layout_id ) {
			self::ensure_elementor_document( absint( $layout_id ) );
		}
	}

	public static function hub_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$tab = sanitize_key( $_GET['tab'] ?? 'layouts' );
		if ( ! in_array( $tab, array( 'layouts', 'add', 'conditions', 'default', 'settings' ), true ) ) {
			$tab = 'layouts';
		}
		if ( isset( $_POST['pss_create_layout_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pss_create_layout_nonce'] ) ), 'pss_create_layout' ) && current_user_can( 'edit_posts' ) ) {
			$title = sanitize_text_field( wp_unslash( $_POST['pss_layout_title'] ?? '' ) ) ?: 'New Single Layout';
			$id    = wp_insert_post( array( 'post_type' => PSS_LAYOUT_CPT, 'post_status' => 'publish', 'post_title' => $title ) );
			if ( ! is_wp_error( $id ) && $id ) {
				self::ensure_elementor_document( $id );
				wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit' ) );
				exit;
			}
		}
		if ( isset( $_POST['pss_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pss_settings_nonce'] ) ), 'pss_settings' ) && current_user_can( 'manage_options' ) ) {
			update_option( 'pss_project_slug', sanitize_title( wp_unslash( $_POST['pss_project_slug'] ?? 'project' ) ) ?: 'project' );
			update_option( 'pss_default_layout', absint( $_POST['pss_default_layout'] ?? 0 ) );
			if ( isset( $_POST['pss_archive_layout'] ) ) {
				update_option( 'pss_archive_layout', absint( $_POST['pss_archive_layout'] ) );
			}
			if ( isset( $_POST['pss_widgets_present'] ) ) {
				$disabled = array_map( 'sanitize_key', (array) ( $_POST['pss_disabled_widgets'] ?? array() ) );
				update_option( 'pss_disabled_widgets', array_values( array_filter( $disabled ) ) );
			}
			flush_rewrite_rules();
			echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
		}
		$base = admin_url( 'edit.php?post_type=' . PSS_PROJECT_CPT . '&page=pss-layouts' );
		$tabs = array(
			'layouts'    => 'All Layouts',
			'add'        => 'Add New Layout',
			'default'    => 'Default Layout',
			'conditions' => 'Conditions',
			'settings'   => 'Settings',
		);
		echo '<div class="wrap pss-layouts-hub"><div class="pss-hub-hero"><div><span class="pss-editor-kicker">SINGLE PROJECT</span><h1>Single Layouts</h1><p>One place to manage reusable Elementor layouts, conditions and the default template. Design still opens in the real Elementor editor.</p></div><a class="button button-primary" href="' . esc_url( add_query_arg( 'tab', 'add', $base ) ) . '">Add New Layout</a></div>';
		echo '<nav class="pss-hub-tabs">';
		foreach ( $tabs as $id => $label ) {
			echo '<a class="' . ( $tab === $id ? 'is-active' : '' ) . '" href="' . esc_url( add_query_arg( 'tab', $id, $base ) ) . '">' . esc_html( $label ) . '</a>';
		}
		echo '</nav>';
		if ( 'add' === $tab ) {
			self::hub_add();
		} elseif ( 'conditions' === $tab ) {
			self::hub_conditions();
		} elseif ( 'default' === $tab || 'settings' === $tab ) {
			self::hub_settings( $tab );
		} else {
			self::hub_list();
		}
		echo '</div>';
	}

	private static function hub_list() {
		$layouts = get_posts( array( 'post_type' => PSS_LAYOUT_CPT, 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC' ) );
		$default = absint( get_option( 'pss_default_layout', 0 ) );
		if ( ! $layouts ) {
			echo '<div class="pss-empty-panel"><strong>No layouts yet.</strong><span>Create a layout, then open it with Elementor. Projects stay as data.</span></div>';
			return;
		}
		echo '<div class="pss-layout-cards">';
		foreach ( $layouts as $layout ) {
			$edit = admin_url( 'post.php?post=' . $layout->ID . '&action=edit' );
			$el   = self::get_elementor_edit_url( $layout->ID );
			$conds = get_meta( $layout->ID, '_pss_conditions', array() );
			$count = is_array( $conds ) ? count( $conds ) : 0;
			echo '<article class="pss-layout-card">';
			echo '<div><span class="pss-editor-kicker">' . ( $default === (int) $layout->ID ? 'DEFAULT' : esc_html( strtoupper( $layout->post_status ) ) ) . '</span>';
			echo '<h2>' . esc_html( $layout->post_title ) . '</h2>';
			echo '<p>' . esc_html( $count ? $count . ' condition rule(s)' : 'No extra conditions — can match all projects' ) . '</p></div>';
			echo '<div class="pss-layout-card__actions">';
			if ( $el ) {
				echo '<a class="button button-primary" href="' . esc_url( $el ) . '">Edit with Elementor</a>';
			}
			echo '<a class="button" href="' . esc_url( $edit ) . '">Conditions</a>';
			$dup = wp_nonce_url( admin_url( 'admin-post.php?action=pss_duplicate_layout&layout_id=' . $layout->ID ), 'pss_duplicate_layout_' . $layout->ID );
			echo '<a class="button" href="' . esc_url( $dup ) . '">Duplicate</a>';
			echo '</div></article>';
		}
		echo '</div>';
	}

	private static function hub_add() {
		echo '<form method="post" class="pss-admin-card pss-hub-form">';
		wp_nonce_field( 'pss_create_layout', 'pss_create_layout_nonce' );
		echo '<h2>Add New Layout</h2><p>Creates a layout manager record and its Elementor design document. You will land on conditions; use Edit with Elementor for the canvas.</p>';
		echo '<label>Layout name<input class="widefat" name="pss_layout_title" placeholder="e.g. Residential — Editorial" required></label>';
		echo '<p><button class="button button-primary">Create layout</button></p></form>';
	}

	private static function hub_conditions() {
		echo '<div class="pss-admin-card"><h2>How conditions work</h2><p>Each layout has include/exclude rules. Specific Project wins, then custom field, type, style, location, category. Exclude always vetoes. Open a layout to edit its rules, then Edit with Elementor for design.</p></div>';
		self::hub_list();
	}

	private static function hub_settings( $tab ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo '<div class="pss-empty-panel"><strong>Settings are limited.</strong><span>Ask an administrator to change the default layout and project slug.</span></div>';
			return;
		}
		$current_slug    = get_option( 'pss_project_slug', 'project' );
		$current_layout  = absint( get_option( 'pss_default_layout', 0 ) );
		$archive_layout  = absint( get_option( 'pss_archive_layout', 0 ) );
		$layouts        = get_posts( array( 'post_type' => PSS_LAYOUT_CPT, 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		echo '<form method="post" class="pss-admin-card pss-hub-form">';
		wp_nonce_field( 'pss_settings', 'pss_settings_nonce' );
		if ( 'default' === $tab ) {
			echo '<h2>Default Layout</h2><p>Used when no more specific layout matches a project.</p>';
			echo '<label>Default Single Layout<select name="pss_default_layout" class="widefat"><option value="0">— None —</option>';
			foreach ( $layouts as $layout ) {
				echo '<option value="' . esc_attr( $layout->ID ) . '" ' . selected( $current_layout, $layout->ID, false ) . '>' . esc_html( $layout->post_title ) . '</option>';
			}
			echo '</select></label><input type="hidden" name="pss_project_slug" value="' . esc_attr( $current_slug ) . '"><input type="hidden" name="pss_archive_layout" value="' . esc_attr( $archive_layout ) . '">';
		} else {
			echo '<h2>Settings</h2>';
			echo '<label>Project URL slug<input name="pss_project_slug" class="widefat" value="' . esc_attr( $current_slug ) . '"></label>';
			echo '<label>Default Single Layout<select name="pss_default_layout" class="widefat"><option value="0">— None —</option>';
			foreach ( $layouts as $layout ) {
				echo '<option value="' . esc_attr( $layout->ID ) . '" ' . selected( $current_layout, $layout->ID, false ) . '>' . esc_html( $layout->post_title ) . '</option>';
			}
			echo '</select></label>';
			echo '<label>Archive Layout<select name="pss_archive_layout" class="widefat"><option value="0">— Fallback grid —</option>';
			foreach ( $layouts as $layout ) {
				echo '<option value="' . esc_attr( $layout->ID ) . '" ' . selected( $archive_layout, $layout->ID, false ) . '>' . esc_html( $layout->post_title ) . '</option>';
			}
			echo '</select></label><p class="description">Optional. Point the project archive at a Single Layout design that contains a Project Showcase widget. This does not register a custom Elementor document type.</p>';
			$disabled = get_option( 'pss_disabled_widgets', array() );
			if ( ! is_array( $disabled ) ) { $disabled = array(); }
			$widgets = array(
				'project-showcase' => 'Project Showcase',
				'project-slider' => 'Project Slider',
				'project-scroll' => 'Project Horizontal Scroll',
				'project-sticky' => 'Sticky Scroll Story',
				'project-gallery' => 'Project Gallery',
				'project-before-after' => 'Before / After',
				'related-projects' => 'Related Projects',
				'project-hero' => 'Project Hero',
				'project-marquee' => 'Project Marquee',
			);
			echo '<input type="hidden" name="pss_widgets_present" value="1">';
			echo '<p><strong>Disable unused widgets</strong></p><p class="description">Unchecked widgets stay registered. Checked widgets are skipped on the next page load — they will not appear in the Elementor panel.</p><div class="pss-widget-toggles" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin:12px 0 20px">';
			foreach ( $widgets as $slug => $label ) {
				echo '<label><input type="checkbox" name="pss_disabled_widgets[]" value="' . esc_attr( $slug ) . '" ' . checked( in_array( $slug, $disabled, true ), true, false ) . '> ' . esc_html( $label ) . '</label>';
			}
			echo '</div>';
		}
		echo '<p><button class="button button-primary">Save</button></p></form>';
	}

	public static function settings_page() {
		self::hub_page();
	}

	public static function maybe_upgrade_starters() {
		if ( self::is_elementor_request() ) {
			return;
		}
		if ( get_option( 'pss_starter_upgrade_version', '' ) === '2.6.0' ) {
			return;
		}
		try {
			self::ensure_seed_layouts();
			update_option( 'pss_starter_upgrade_version', '2.6.0', false );
		} catch ( \Throwable $e ) {
			error_log( '[PSS] starter upgrade: ' . $e->getMessage() );
		}
	}

	public static function ensure_seed_layouts() {
		if ( ! post_type_exists( PSS_LAYOUT_CPT ) ) return;
		$existing = get_posts( array( 'post_type' => PSS_LAYOUT_CPT, 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'any' ) );
		$titles = wp_list_pluck( array_map( 'get_post', $existing ), 'post_title' );
		$modern_id = 0;
		$premium_id = 0;
		if ( ! in_array( 'Modern — Editorial', $titles, true ) ) {
			$modern_id = self::create_seed( 'Modern — Editorial', 'modern' );
		} else {
			$modern_id = self::find_title( 'Modern — Editorial' );
			self::maybe_refresh_starter( $modern_id, 'modern' );
		}
		if ( ! in_array( 'Premium — Cinematic', $titles, true ) ) {
			$premium_id = self::create_seed( 'Premium — Cinematic', 'premium' );
		} else {
			$premium_id = self::find_title( 'Premium — Cinematic' );
			self::maybe_refresh_starter( $premium_id, 'premium' );
		}
		if ( ! get_option( 'pss_default_layout' ) && $modern_id ) update_option( 'pss_default_layout', $modern_id, false );
	}

	private static function maybe_refresh_starter( $layout_id, $style ) {
		$layout_id = absint( $layout_id );
		if ( ! $layout_id || ! in_array( $style, array( 'modern', 'premium' ), true ) ) return;
		$version = (string) get_post_meta( $layout_id, '_pss_starter_version', true );
		if ( version_compare( $version ?: '0.0.0', '2.6.0', '>=' ) ) return;
		$document_id = self::get_elementor_template_id( $layout_id );
		$document = $document_id ? get_post( $document_id ) : null;
		$manager = get_post( $layout_id );
		// Never overwrite an Elementor design the user has already edited.
		$data = $document ? get_post_meta( $document_id, '_elementor_data', true ) : '';
		$parsed = is_string( $data ) ? json_decode( $data, true ) : ( is_array( $data ) ? $data : array() );
		$effectively_blank = ! is_array( $parsed ) || count( $parsed ) < 3;
		if ( $document && $manager && ( $document->post_modified_gmt === $document->post_date_gmt || $effectively_blank ) ) {
			Elementor::seed_layout_content( $document_id, $style );
			update_post_meta( $layout_id, '_pss_starter_version', '2.6.0' );
		}
	}

	private static function find_title( $title ) {
		$found = get_posts(
			array(
				'post_type'      => PSS_LAYOUT_CPT,
				'title'          => $title,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $found ) {
			return absint( $found[0] );
		}
		if ( function_exists( 'get_page_by_title' ) ) {
			$post = get_page_by_title( $title, OBJECT, PSS_LAYOUT_CPT );
			return $post ? $post->ID : 0;
		}
		return 0;
	}

	private static function create_seed( $title, $style ) {
		$id = wp_insert_post( array( 'post_type' => PSS_LAYOUT_CPT, 'post_status' => 'publish', 'post_title' => $title ) );
		if ( is_wp_error( $id ) || ! $id ) return 0;
		update_post_meta( $id, '_pss_seed_style', sanitize_key( $style ) );
		update_post_meta( $id, '_pss_starter_version', '2.6.0' );
		update_post_meta( $id, '_pss_condition_logic', 'all' );
		if ( 'modern' === $style ) update_post_meta( $id, '_pss_conditions', array( array( 'mode' => 'include', 'type' => 'all', 'value' => '', 'field_key' => '', 'operator' => 'equals', 'priority' => 10 ) ) );
		else update_post_meta( $id, '_pss_conditions', array( array( 'mode' => 'include', 'type' => 'all', 'value' => '', 'field_key' => '', 'operator' => 'equals', 'priority' => 0 ) ) );
		self::ensure_elementor_document( $id );
		return $id;
	}
}
