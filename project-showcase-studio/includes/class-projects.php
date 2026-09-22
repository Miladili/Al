<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

class Projects {
	public static function init() {
		add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
		add_action( 'init', array( __CLASS__, 'register_content_types' ), 5 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . PSS_PROJECT_CPT, array( __CLASS__, 'save_project' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'disable_block_editor' ), 10, 2 );
		add_filter( 'use_block_editor_for_post', array( __CLASS__, 'disable_block_editor_for_post' ), 100, 2 );
		add_filter( 'manage_' . PSS_PROJECT_CPT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . PSS_PROJECT_CPT . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'remove_elementor_row_action' ), 50, 2 );
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

	public static function disable_block_editor( $use_block_editor, $post_type ) {
		return PSS_PROJECT_CPT === $post_type ? false : $use_block_editor;
	}

	public static function disable_block_editor_for_post( $use_block_editor, $post ) {
		if ( $post && isset( $post->post_type ) && PSS_PROJECT_CPT === $post->post_type ) {
			return false;
		}
		return $use_block_editor;
	}

	public static function remove_elementor_row_action( $actions, $post ) {
		if ( $post && PSS_PROJECT_CPT === $post->post_type ) {
			unset( $actions['edit_with_elementor'], $actions['elementor'] );
		}
		return $actions;
	}

	public static function register_content_types() {
		register_post_type(
			PSS_PROJECT_CPT,
			array(
				'labels' => array(
					'name' => __( 'Projects', 'project-showcase-studio' ),
					'singular_name' => __( 'Project', 'project-showcase-studio' ),
					'menu_name' => __( 'Projects', 'project-showcase-studio' ),
					'add_new' => __( 'Add New', 'project-showcase-studio' ),
					'add_new_item' => __( 'Add New Project', 'project-showcase-studio' ),
					'edit_item' => __( 'Edit Project', 'project-showcase-studio' ),
				),
				'public' => true,
				'show_ui' => true,
				'show_in_rest' => true,
				'menu_icon' => 'dashicons-portfolio',
				'has_archive' => true,
				'rewrite' => array( 'slug' => sanitize_title( get_option( 'pss_project_slug', 'project' ) ), 'with_front' => false ),
				'supports' => array( 'title', 'editor', 'thumbnail', 'revisions' ),
				'taxonomies' => array( 'pss_project_category', 'pss_project_style', 'pss_project_location', 'pss_project_type' ),
			)
		);

		$taxonomies = array(
			'pss_project_category' => array( 'Categories', 'Category' ),
			'pss_project_style' => array( 'Styles', 'Style' ),
			'pss_project_location' => array( 'Locations', 'Location' ),
			'pss_project_type' => array( 'Project Types', 'Project Type' ),
		);
		foreach ( $taxonomies as $slug => $labels ) {
			register_taxonomy(
				$slug,
				PSS_PROJECT_CPT,
				array(
					'labels' => array(
						'name' => __( $labels[0], 'project-showcase-studio' ),
						'singular_name' => __( $labels[1], 'project-showcase-studio' ),
						'menu_name' => __( $labels[0], 'project-showcase-studio' ),
					),
					'public' => true,
					'show_ui' => true,
					'show_in_rest' => true,
					'show_admin_column' => true,
					'hierarchical' => true,
					'rewrite' => array( 'slug' => str_replace( 'pss_project_', '', $slug ) ),
				)
			);
		}
	}

	public static function activate() {
		self::register_content_types();
		Layouts::register_cpt();
		Layouts::ensure_seed_layouts();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public static function admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || PSS_PROJECT_CPT !== $screen->post_type ) {
			return;
		}
		if ( false !== strpos( (string) $screen->id, 'pss-layouts' ) || false !== strpos( (string) $screen->id, 'pss-project-fields' ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'pss-admin', PSS_URL . 'admin/assets/admin.css', array(), PSS_VERSION );
		wp_enqueue_script( 'pss-admin', PSS_URL . 'admin/assets/admin.js', array( 'jquery' ), PSS_VERSION, true );
		wp_enqueue_script( 'pss-fields', PSS_URL . 'admin/assets/fields.js', array(), PSS_VERSION, true );
		wp_localize_script( 'pss-admin', 'PSSAdmin', array( 'mediaTitle' => __( 'Select images', 'project-showcase-studio' ) ) );
	}

	public static function meta_boxes() {
		add_meta_box( 'pss_project_data', __( 'Project Data', 'project-showcase-studio' ), array( __CLASS__, 'render_meta_box' ), PSS_PROJECT_CPT, 'normal', 'high' );
		add_meta_box( 'pss_project_layout', __( 'Single Project Layout', 'project-showcase-studio' ), array( __CLASS__, 'render_layout_box' ), PSS_PROJECT_CPT, 'side', 'default' );
	}

	public static function render_meta_box( $post ) {
		wp_nonce_field( 'pss_save_project', 'pss_project_nonce' );
		$video = get_meta( $post->ID, '_pss_video' );
		$before = get_meta( $post->ID, '_pss_before' );
		$after = get_meta( $post->ID, '_pss_after' );
		$floor_plan = get_meta( $post->ID, '_pss_floor_plan' );
		$gallery = get_gallery_ids( $post->ID );
		?>
		<div class="pss-admin-tabs pss-project-editor">
			<nav>
				<button type="button" class="pss-tab-link is-active" data-tab="project-data">Project Data</button>
				<button type="button" class="pss-tab-link" data-tab="media">Media</button>
				<button type="button" class="pss-tab-link" data-tab="details">Taxonomies</button>
				<button type="button" class="pss-tab-link" data-tab="products">Products</button>
			</nav>
			<section class="pss-tab is-active" data-tab="project-data">
				<?php
				try {
					Fields::render_project_fields( $post->ID );
				} catch ( \Throwable $e ) {
					error_log( '[PSS] Project Data editor failed: ' . $e->getMessage() );
					self::render_recovery_editor( $post->ID, $e );
				}
				?>
			</section>
			<section class="pss-tab" data-tab="media">
				<div class="pss-media-editor-grid">
					<div class="pss-media-card"><span class="pss-editor-kicker">GALLERY</span><h3>Project gallery</h3><p>Upload and reorder the project images in the WordPress Media Library.</p><input type="hidden" id="pss_gallery" name="pss_gallery" value="<?php echo esc_attr( implode( ',', $gallery ) ); ?>"><button type="button" class="button button-primary pss-media-button" data-target="#pss_gallery">Choose gallery</button><span id="pss_gallery_preview" class="pss-media-preview"></span></div>
					<div class="pss-media-card"><span class="pss-editor-kicker">VIDEO</span><h3>Project video</h3><p>YouTube, Vimeo or a self-hosted video URL.</p><input type="url" name="pss_video" value="<?php echo esc_attr( $video ); ?>" class="widefat" placeholder="https://…"></div>
					<div class="pss-media-card"><span class="pss-editor-kicker">BEFORE / AFTER</span><h3>Transformation images</h3><p>Use Media Library IDs. A dedicated selector is intentionally kept simple and safe.</p><div class="pss-media-pair"><label>Before<input type="number" name="pss_before" value="<?php echo esc_attr( $before ); ?>" class="widefat"></label><label>After<input type="number" name="pss_after" value="<?php echo esc_attr( $after ); ?>" class="widefat"></label></div></div>
					<div class="pss-media-card"><span class="pss-editor-kicker">PLAN</span><h3>Floor plan</h3><p>Store the attachment ID for the plan image.</p><input type="number" name="pss_floor_plan" value="<?php echo esc_attr( $floor_plan ); ?>" class="widefat"></div>
				</div>
			</section>
			<section class="pss-tab" data-tab="details">
				<div class="pss-taxonomy-hint"><strong>Project taxonomies stay native to WordPress.</strong><p>Use the Category, Style, Location and Project Type boxes around this editor. They are independent from your flexible project data records.</p><ul><li>Title, long description and featured image use the standard WordPress fields above.</li><li>All extra project attributes belong in Project Data records.</li><li>No fixed project schema is required.</li></ul></div>
			</section>
			<section class="pss-tab" data-tab="products">
				<?php
				try {
					WooCommerce::render_project_products( $post->ID );
				} catch ( \Throwable $e ) {
					error_log( '[PSS] WooCommerce project products editor failed: ' . $e->getMessage() );
					echo '<div class="notice notice-warning inline"><p>WooCommerce integration could not load on this screen. The project can still be saved without product relations.</p></div>';
				}
				?>
			</section>
		</div>
		<?php
	}

	public static function render_recovery_editor( $project_id, $error = null ) {
		$description = get_post_field( 'post_content', $project_id );
		if ( $error ) {
			echo '<div class="notice notice-error inline"><p><strong>Project Data editor recovered from an internal error.</strong> The rest of the WordPress Project editor is still available. Check the PHP error log for the exact cause.</p></div>';
		}
		echo '<div class="pss-recovery-editor"><h3>Project Data Recovery</h3><p>Use the standard WordPress fields to continue editing this project. The advanced record builder will become available again after the underlying issue is resolved.</p>';
		echo '<label><strong>Project description</strong><textarea class="widefat" rows="10" name="post_content">' . esc_textarea( $description ) . '</textarea></label></div>';
	}

	public static function render_layout_box( $post ) {
		$selected = absint( get_meta( $post->ID, '_pss_layout_override', 0 ) );
		$layouts = get_posts( array( 'post_type' => PSS_LAYOUT_CPT, 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		?>
		<p><label><strong>Use a specific Single Layout for this project</strong></label></p>
		<select name="pss_layout_override" class="widefat">
			<option value="0">Use automatic matching</option>
			<?php foreach ( $layouts as $layout ) : ?>
			<option value="<?php echo esc_attr( $layout->ID ); ?>" <?php selected( $selected, $layout->ID ); ?>><?php echo esc_html( $layout->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function save_project( $post_id, $post ) {
		if ( ! isset( $_POST['pss_project_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pss_project_nonce'] ) ), 'pss_save_project' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		// Legacy/core fields are no longer rendered as a fixed schema. Preserve them
		// only when an older/newer editor explicitly submits a matching control.
		$map = array(
			'pss_subtitle' => '_pss_subtitle', 'pss_year' => '_pss_year', 'pss_area' => '_pss_area', 'pss_duration' => '_pss_duration',
			'pss_designer' => '_pss_designer', 'pss_architect' => '_pss_architect', 'pss_client' => '_pss_client', 'pss_team' => '_pss_team',
			'pss_services' => '_pss_services', 'pss_materials' => '_pss_materials', 'pss_colors' => '_pss_colors', 'pss_features' => '_pss_features',
			'pss_contractor' => '_pss_contractor', 'pss_contractor_company' => '_pss_contractor_company', 'pss_status' => '_pss_status',
			'pss_budget' => '_pss_budget', 'pss_completion' => '_pss_completion', 'pss_photographer' => '_pss_photographer',
			'pss_consultant' => '_pss_consultant', 'pss_engineer' => '_pss_engineer',
			'pss_video' => '_pss_video', 'pss_before' => '_pss_before', 'pss_after' => '_pss_after', 'pss_floor_plan' => '_pss_floor_plan',
		);
		foreach ( $map as $input => $meta ) {
			if ( ! array_key_exists( $input, $_POST ) ) continue;
			$value = wp_kses_post( wp_unslash( $_POST[ $input ] ) );
			update_post_meta( $post_id, $meta, $value );
		}
		if ( array_key_exists( 'pss_gallery', $_POST ) ) {
			$gallery = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['pss_gallery'] ) ) ) ) );
			update_post_meta( $post_id, '_pss_gallery', $gallery );
		}

		if ( array_key_exists( 'pss_layout_override', $_POST ) ) {
			update_post_meta( $post_id, '_pss_layout_override', absint( $_POST['pss_layout_override'] ) );
		}
		Fields::save_project_fields( $post_id );
		WooCommerce::save_related_products( $post_id );
	}

	public static function columns( $columns ) {
		$columns['pss_style'] = 'Style';
		$columns['pss_type'] = 'Type';
		$columns['pss_location'] = 'Location';
		$columns['pss_year'] = 'Year';
		return $columns;
	}

	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'pss_style': echo esc_html( get_project_taxonomy_value( $post_id, 'pss_project_style' ) ); break;
			case 'pss_type': echo esc_html( get_project_taxonomy_value( $post_id, 'pss_project_type' ) ); break;
			case 'pss_location': echo esc_html( get_project_taxonomy_value( $post_id, 'pss_project_location' ) ); break;
			case 'pss_year': echo esc_html( get_meta( $post_id, '_pss_year' ) ); break;
		}
	}
}
