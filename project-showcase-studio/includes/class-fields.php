<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

class Fields {
	public static function init() {
		add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_seed_default_fields' ) );
		add_action( 'admin_post_pss_save_field_definitions', array( __CLASS__, 'save_definitions' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
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

	public static function menu() {
		add_submenu_page( 'edit.php?post_type=' . PSS_PROJECT_CPT, 'Project Fields', 'Project Fields', 'manage_options', 'pss-project-fields', array( __CLASS__, 'page' ) );
	}

	public static function assets( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_fields = $screen && false !== strpos( (string) $screen->id, 'pss-project-fields' );
		if ( ! $is_fields && false === strpos( (string) $hook, 'pss-project-fields' ) ) {
			return;
		}
		wp_enqueue_style( 'pss-admin', PSS_URL . 'admin/assets/admin.css', array(), PSS_VERSION );
		wp_enqueue_script( 'pss-fields', PSS_URL . 'admin/assets/fields.js', array(), PSS_VERSION, true );
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$defs = get_field_definitions();
		$field_options = array();
		foreach ( $defs as $field ) { $field_options[ sanitize_key( $field['key'] ?? '' ) ] = (string) ( $field['label'] ?? $field['key'] ?? '' ); }
		?>
		<div class="wrap pss-fields-admin">
			<div class="pss-field-library-hero">
				<div>
					<span class="pss-editor-kicker">FIELD LIBRARY</span>
					<h1>Reusable field definitions</h1>
					<p>Define Label, Key, Type, Options, Validation and Conditions once. This list is not applied to every project. Each project adds only the fields it needs.</p>
				</div>
				<span class="pss-editor-pill"><?php echo esc_html( count( $defs ) ); ?> definitions</span>
			</div>
			<p><input type="search" id="pss-field-library-search" class="regular-text" placeholder="Search field library…"></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="pss_save_field_definitions">
				<?php wp_nonce_field( 'pss_fields_save', 'pss_fields_nonce' ); ?>
				<div id="pss-field-list">
					<?php foreach ( $defs as $index => $field ) : self::field_builder_row( $index, $field ); endforeach; ?>
				</div>
				<p><button type="button" class="button button-secondary" id="pss-add-field">+ Add Field</button></p>
				<p><button type="submit" class="button button-primary">Save Fields</button></p>
			</form>
			<script>window.PSSFieldOptions=<?php echo wp_json_encode( $field_options ); ?>;</script>
		</div>
		<?php
	}

	private static function field_builder_row( $index, $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'text';
		$subfields = is_array( $field['subfields'] ?? null ) ? $field['subfields'] : array();
		?>
		<div class="pss-field-builder" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="pss-field-builder__head"><strong>Field</strong><button type="button" class="button-link-delete pss-remove-field">Remove</button></div>
			<div class="pss-grid-3">
				<label>Label<input type="text" name="fields[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $field['label'] ?? '' ); ?>"></label>
				<label>Key<input type="text" name="fields[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $field['key'] ?? '' ); ?>" placeholder="cabinet_material"></label>
				<label>Type<select class="pss-field-type" name="fields[<?php echo esc_attr( $index ); ?>][type]"><?php self::type_options( $type ); ?></select></label>
			</div>
			<label>Description<input type="text" name="fields[<?php echo esc_attr( $index ); ?>][description]" value="<?php echo esc_attr( $field['description'] ?? '' ); ?>"></label>
			<div class="pss-grid-3">
				<label>Placeholder<input type="text" name="fields[<?php echo esc_attr( $index ); ?>][placeholder]" value="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"></label>
				<label>Default value<input type="text" name="fields[<?php echo esc_attr( $index ); ?>][default_value]" value="<?php echo esc_attr( $field['default_value'] ?? '' ); ?>"></label>
				<label>Unit (e.g. m²)<input type="text" name="fields[<?php echo esc_attr( $index ); ?>][unit]" value="<?php echo esc_attr( $field['unit'] ?? '' ); ?>"></label>
			</div>
			<label><input type="checkbox" name="fields[<?php echo esc_attr( $index ); ?>][required]" value="1" <?php checked( ! empty( $field['required'] ) ); ?>> Required</label>
			<?php self::render_visibility_builder( $index, $field ); ?>
			<div class="pss-field-type-options">
				<?php self::render_definition_options( $index, $type, $field, $subfields ); ?>
			</div>
		</div>
		<?php
	}

	private static function render_visibility_builder( $index, $field ) {
		$visibility = is_array( $field['visibility'] ?? null ) ? $field['visibility'] : array();
		$enabled = ! empty( $visibility['enabled'] );
		$source = sanitize_key( $visibility['field_key'] ?? '' );
		$operator = sanitize_key( $visibility['operator'] ?? 'equals' );
		$value = (string) ( $visibility['value'] ?? '' );
		?>
		<div class="pss-field-visibility">
			<strong>Conditional visibility</strong>
			<label><input type="checkbox" class="pss-visibility-enabled" name="fields[<?php echo esc_attr( $index ); ?>][visibility][enabled]" value="1" <?php checked( $enabled ); ?>> Show this field only when another field matches</label>
			<div class="pss-visibility-rule" <?php echo $enabled ? '' : 'style="display:none"'; ?>>
				<select name="fields[<?php echo esc_attr( $index ); ?>][visibility][field_key]" class="pss-visibility-field">
					<option value="">Choose field</option>
					<?php foreach ( get_field_definitions() as $other ) : $other_key = sanitize_key( $other['key'] ?? '' ); if ( $other_key === sanitize_key( $field['key'] ?? '' ) ) continue; ?>
						<option value="<?php echo esc_attr( $other_key ); ?>" <?php selected( $source, $other_key ); ?>><?php echo esc_html( $other['label'] ?? $other_key ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="fields[<?php echo esc_attr( $index ); ?>][visibility][operator]">
					<?php foreach ( array( 'equals' => 'Equals', 'not_equals' => 'Does not equal', 'contains' => 'Contains', 'not_contains' => 'Does not contain', 'empty' => 'Is empty', 'not_empty' => 'Is not empty' ) as $op => $label ) : ?>
						<option value="<?php echo esc_attr( $op ); ?>" <?php selected( $operator, $op ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="fields[<?php echo esc_attr( $index ); ?>][visibility][value]" value="<?php echo esc_attr( $value ); ?>" placeholder="Value">
			</div>
		</div>
		<?php
	}

	private static function render_definition_options( $index, $type, $field, $subfields ) {
		if ( in_array( $type, array( 'select', 'multi_select', 'radio', 'checkbox' ), true ) ) : ?>
			<label>Options (one per line)
				<textarea name="fields[<?php echo esc_attr( $index ); ?>][options]" rows="4"><?php echo esc_textarea( implode( "\n", (array) ( $field['options'] ?? array() ) ) ); ?></textarea>
			</label>
		<?php endif;

		if ( in_array( $type, array( 'repeater', 'group', 'table' ), true ) ) : ?>
			<div class="pss-subfields-builder" data-index="<?php echo esc_attr( $index ); ?>">
				<div class="pss-subfields-builder__head"><strong><?php echo 'table' === $type ? 'Table Columns' : 'Subfields'; ?></strong><button type="button" class="button pss-add-subfield">+ Add</button></div>
				<input type="hidden" class="pss-subfields-json" name="fields[<?php echo esc_attr( $index ); ?>][subfields]" value="<?php echo esc_attr( wp_json_encode( $subfields ) ); ?>">
				<div class="pss-subfields-list">
					<?php foreach ( $subfields as $subfield_index => $subfield ) : ?>
						<div class="pss-subfield-row">
							<input type="text" class="pss-subfield-label" value="<?php echo esc_attr( $subfield['label'] ?? '' ); ?>" placeholder="Label">
							<input type="text" class="pss-subfield-key" value="<?php echo esc_attr( $subfield['key'] ?? '' ); ?>" placeholder="key">
							<?php if ( 'table' !== $type ) : ?><select class="pss-subfield-type"><?php self::type_options( $subfield['type'] ?? 'text' ); ?></select><?php endif; ?>
							<button type="button" class="button-link-delete pss-remove-subfield">Remove</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif;

		if ( 'icon_value' === $type ) : ?>
			<p class="description">This field stores repeatable Icon + Title + Value rows.</p>
		<?php endif;
	}

	private static function type_options( $current ) {
		foreach ( self::types() as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
	}

	public static function save_definitions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		check_admin_referer( 'pss_fields_save', 'pss_fields_nonce' );
		$raw  = isset( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : array();
		$defs = array();
		$used = array();
		$allowed_types = array_keys( self::types() );

		foreach ( (array) $raw as $field ) {
			$label = sanitize_text_field( $field['label'] ?? '' );
			$key   = sanitize_key( $field['key'] ?? sanitize_title( $label ) );
			if ( ! $label || ! $key || isset( $used[ $key ] ) ) {
				continue;
			}
			$used[ $key ] = true;
			$type = sanitize_key( $field['type'] ?? 'text' );
			if ( ! in_array( $type, $allowed_types, true ) ) {
				$type = 'text';
			}
			$options = preg_split( '/\r\n|\r|\n/', (string) ( $field['options'] ?? '' ) );
			$options = array_values( array_filter( array_map( 'sanitize_text_field', (array) $options ) ) );
			$subfields = json_decode( wp_unslash( $field['subfields'] ?? '' ), true );
			$subfields = is_array( $subfields ) ? $subfields : array();
			$subfields = self::sanitize_subfields( $subfields, 'table' === $type );
			$visibility_raw = is_array( $field['visibility'] ?? null ) ? $field['visibility'] : array();
			$visibility = array(
				'enabled' => ! empty( $visibility_raw['enabled'] ),
				'field_key' => sanitize_key( $visibility_raw['field_key'] ?? '' ),
				'operator' => in_array( $visibility_raw['operator'] ?? '', array( 'equals', 'not_equals', 'contains', 'not_contains', 'empty', 'not_empty' ), true ) ? sanitize_key( $visibility_raw['operator'] ) : 'equals',
				'value' => sanitize_text_field( $visibility_raw['value'] ?? '' ),
			);
			if ( ! $visibility['field_key'] ) $visibility['enabled'] = false;
			$defs[] = array(
				'label'         => $label,
				'key'           => $key,
				'type'          => $type,
				'description'   => sanitize_text_field( $field['description'] ?? '' ),
				'placeholder'   => sanitize_text_field( $field['placeholder'] ?? '' ),
				'default_value' => sanitize_text_field( $field['default_value'] ?? '' ),
				'unit'          => sanitize_text_field( $field['unit'] ?? '' ),
				'required'      => ! empty( $field['required'] ),
				'options'       => $options,
				'subfields'     => $subfields,
				'visibility'    => $visibility,
			);
		}
		update_option( 'pss_field_definitions', $defs, false );
		wp_safe_redirect( add_query_arg( array( 'post_type' => PSS_PROJECT_CPT, 'page' => 'pss-project-fields', 'updated' => '1' ), admin_url( 'edit.php' ) ) );
		exit;
	}


	public static function maybe_seed_default_fields() {
		$existing = get_option( 'pss_field_definitions', array() );
		if ( is_array( $existing ) && ! empty( $existing ) ) {
			return;
		}
		update_option(
			'pss_field_definitions',
			array(
				array( 'label' => 'Year', 'key' => 'year', 'type' => 'number', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Area', 'key' => 'area', 'type' => 'text', 'description' => 'e.g. 240 m²', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Designer', 'key' => 'designer', 'type' => 'text', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Architect', 'key' => 'architect', 'type' => 'text', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Client', 'key' => 'client', 'type' => 'text', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Budget', 'key' => 'budget', 'type' => 'text', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Status', 'key' => 'status', 'type' => 'select', 'description' => '', 'required' => false, 'options' => array( 'Concept', 'In progress', 'Completed' ), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Completion', 'key' => 'completion', 'type' => 'date', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Materials', 'key' => 'materials', 'type' => 'textarea', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
				array( 'label' => 'Services', 'key' => 'services', 'type' => 'textarea', 'description' => '', 'required' => false, 'options' => array(), 'subfields' => array(), 'visibility' => array() ),
			),
			false
		);
	}

	private static function types() {
		return array(
			'text' => 'Text', 'textarea' => 'Textarea', 'wysiwyg' => 'WYSIWYG', 'number' => 'Number', 'date' => 'Date', 'time' => 'Time', 'url' => 'URL', 'email' => 'Email', 'phone' => 'Phone', 'color' => 'Color', 'icon' => 'Icon',
			'image' => 'Image', 'gallery' => 'Gallery', 'file' => 'File', 'video' => 'Video', 'map' => 'Map / Location',
			'select' => 'Select', 'multi_select' => 'Multi Select', 'radio' => 'Radio', 'checkbox' => 'Checkbox', 'toggle' => 'Toggle', 'relationship' => 'Relationship', 'repeater' => 'Repeater',
			'group' => 'Group', 'table' => 'Table', 'icon_value' => 'Icon + Title + Value',
		);
	}

	private static function sanitize_subfields( $subfields, $table = false ) {
		$out = array();
		foreach ( (array) $subfields as $row ) {
			$label = sanitize_text_field( $row['label'] ?? '' );
			$key   = sanitize_key( $row['key'] ?? sanitize_title( $label ) );
			if ( ! $label || ! $key ) {
				continue;
			}
			$type = $table ? 'text' : sanitize_key( $row['type'] ?? 'text' );
		if ( ! isset( self::types()[ $type ] ) || in_array( $type, array( 'repeater', 'table', 'icon_value' ), true ) ) {
			$type = 'text';
		}
		$nested = array();
		if ( 'group' === $type && ! empty( $row['subfields'] ) && is_array( $row['subfields'] ) ) {
			$nested = self::sanitize_subfields( $row['subfields'], false );
		}
		$out[] = array( 'label' => $label, 'key' => $key, 'type' => $type, 'subfields' => $nested, 'options' => array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $row['options'] ?? array() ) ) ) ) );
		}
		return $out;
	}

	public static function render_project_fields( $project_id ) {
		self::maybe_seed_default_fields();
		$local_defs   = get_project_local_field_definitions( $project_id );
		$local_values = get_project_local_field_values( $project_id );
		$library      = get_project_field_library_options();
		$schema       = array();
		$used         = array();
		foreach ( $local_defs as $field ) {
			$key = sanitize_key( $field['key'] ?? '' );
			if ( ! $key || isset( $used[ $key ] ) ) { continue; }
			$used[ $key ] = true;
			$schema[] = $field;
		}
		// Recover previously saved values without forcing a universal schema.
		if ( ! $schema ) {
			foreach ( $library as $token => $def ) {
				$record_key = sanitize_key( $def['record_key'] ?? '' );
				if ( ! $record_key || isset( $used[ $record_key ] ) ) { continue; }
				$value = get_field_value( $project_id, $record_key, '' );
				$has = is_array( $value ) ? ! empty( $value ) : ( '' !== trim( (string) $value ) );
				if ( ! $has ) { continue; }
				$schema[] = array(
					'source' => $def['source'] ?? 'global',
					'source_key' => $def['key'] ?? $record_key,
					'label' => $def['label'] ?? $record_key,
					'key' => $record_key,
					'type' => $def['type'] ?? 'text',
					'options' => $def['options'] ?? array(),
					'subfields' => $def['subfields'] ?? array(),
					'description' => $def['description'] ?? '',
				);
				$used[ $record_key ] = true;
			}
		}

		echo '<script>window.PSSProjectFieldLibrary=' . wp_json_encode( $library ) . ';window.PSSFieldTypes=' . wp_json_encode( self::types() ) . ';</script>';
		echo '<div class="pss-field-editor-shell pss-cms-editor">';
		echo '<div class="pss-field-editor-hero"><div><span class="pss-editor-kicker">PROJECT SCHEMA</span><h2>Build this project’s fields</h2><p>There is no mandatory project template. Add only the fields this project needs — from the library or created once for this record. After adding, you only edit values.</p></div><span class="pss-editor-pill">' . esc_html( count( $schema ) ) . ' fields</span></div>';

		echo '<div class="pss-cms-panel"><h3>Classification</h3><p class="description">Native WordPress taxonomies. Title, featured image and the editor above stay standard WordPress fields.</p><div class="pss-cms-grid">';
		self::taxonomy_select( $project_id, 'pss_project_type', 'Project Type' );
		self::taxonomy_select( $project_id, 'pss_project_style', 'Style' );
		self::taxonomy_select( $project_id, 'pss_project_location', 'Location' );
		self::taxonomy_select( $project_id, 'pss_project_category', 'Category' );
		echo '</div></div>';

		echo '<div class="pss-cms-panel pss-schema-panel">';
		echo '<div class="pss-data-builder-toolbar"><div class="pss-data-builder-toolbar__copy"><strong>Add Field</strong><span>Reuse a library field or create a new one. Definition stays hidden after insert.</span></div>';
		echo '<div class="pss-data-builder-toolbar__actions"><input type="search" id="pss-quick-record-search" class="widefat" placeholder="Search field library…"><select id="pss-quick-record-source" class="widefat"><option value="">Choose from library…</option>';
		foreach ( $library as $token => $def ) {
			$record_key = sanitize_key( $def['record_key'] ?? '' );
			if ( ! $record_key || isset( $used[ $record_key ] ) ) { continue; }
			echo '<option value="' . esc_attr( $token ) . '">' . esc_html( $def['label'] ?? $record_key ) . '</option>';
		}
		echo '</select><button type="button" class="button button-primary" id="pss-add-library-record">Add Field</button><button type="button" class="button" id="pss-add-local-field">Create new field</button></div></div>';
		echo '<div id="pss-create-field-dialog" class="pss-create-field" hidden><strong>Create a field for this project</strong><div class="pss-grid-3"><label>Label<input type="text" id="pss-new-label" class="widefat"></label><label>Key<input type="text" id="pss-new-key" class="widefat" placeholder="bedrooms"></label><label>Type<select id="pss-new-type" class="widefat">';
		self::type_options( 'text' );
		echo '</select></label></div><label>Options (one per line, for Select)<textarea id="pss-new-options" class="widefat" rows="3"></textarea></label><p><button type="button" class="button button-primary" id="pss-create-field-confirm">Create and add value</button> <button type="button" class="button" id="pss-create-field-cancel">Cancel</button></p></div>';

		echo '<div id="pss-local-fields-list" class="pss-schema-list">';
		foreach ( $schema as $index => $field ) {
			$key = sanitize_key( $field['key'] ?? '' );
			$value = array_key_exists( $key, $local_values ) ? $local_values[ $key ] : get_field_value( $project_id, $key, '' );
			self::render_value_only_row( $index, $field, $value );
		}
		echo '</div>';
		echo '<div id="pss-local-field-empty" class="pss-empty-panel"' . ( $schema ? ' style="display:none"' : '' ) . '><strong>This project has no extra fields yet.</strong><span>Use Add Field. A kitchen project, a villa and an office can each have a completely different schema.</span></div></div>';

		echo '<div class="pss-card-data-panel"><div class="pss-section-head"><div><h3>Card display fields</h3><p>Choose which of this project’s values appear on Showcase cards.</p></div></div>';
		self::render_card_field_picker( $project_id, get_field_definitions( $project_id ) );
		echo '</div></div>';
	}

	private static function taxonomy_select( $project_id, $taxonomy, $label ) {
		if ( ! taxonomy_exists( $taxonomy ) ) { return; }
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$current = wp_get_object_terms( $project_id, $taxonomy, array( 'fields' => 'ids' ) );
		$current = ( ! is_wp_error( $current ) && $current ) ? absint( $current[0] ) : 0;
		echo '<label class="pss-value-row"><span class="pss-value-row__label">' . esc_html( $label ) . '</span>';
		echo '<select name="pss_tax[' . esc_attr( $taxonomy ) . ']" class="widefat"><option value="">— Select —</option>';
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( $current, (int) $term->term_id, false ) . '>' . esc_html( $term->name ) . '</option>';
			}
		}
		echo '</select></label>';
	}

	private static function render_value_only_row( $index, $field, $value = '' ) {
		$type = sanitize_key( $field['type'] ?? 'text' );
		$label = (string) ( $field['label'] ?? '' );
		$key = sanitize_key( $field['key'] ?? sanitize_title( $label ) );
		$options = (array) ( $field['options'] ?? array() );
		$subfields = is_array( $field['subfields'] ?? null ) ? $field['subfields'] : array();
		$source = sanitize_key( $field['source'] ?? 'custom' );
		$source_key = sanitize_key( $field['source_key'] ?? $key );
		$token = 'custom' === $source ? 'custom' : ( 'core' === $source ? 'core:' . $source_key : 'global:' . $source_key );
		echo '<div class="pss-value-row pss-value-row--extra" draggable="true" data-index="' . esc_attr( $index ) . '">';
		echo '<input type="hidden" name="pss_local_defs[' . esc_attr( $index ) . '][source]" value="' . esc_attr( $token ) . '">';
		echo '<input type="hidden" name="pss_local_defs[' . esc_attr( $index ) . '][label]" value="' . esc_attr( $label ) . '">';
		echo '<input type="hidden" name="pss_local_defs[' . esc_attr( $index ) . '][key]" value="' . esc_attr( $key ) . '">';
		echo '<input type="hidden" name="pss_local_defs[' . esc_attr( $index ) . '][type]" class="pss-local-field-type" value="' . esc_attr( $type ) . '">';
		echo '<input type="hidden" name="pss_local_defs[' . esc_attr( $index ) . '][options]" class="pss-local-options" value="' . esc_attr( implode( "\n", $options ) ) . '">';
		echo '<input type="hidden" class="pss-local-subfields-json" name="pss_local_defs[' . esc_attr( $index ) . '][subfields_json]" value="' . esc_attr( wp_json_encode( $subfields ) ) . '">';
		echo '<div class="pss-value-row__label"><button type="button" class="pss-drag-handle" aria-label="Reorder">⋮⋮</button><span>' . esc_html( $label ?: $key ) . '</span><span class="pss-value-row__actions"><button type="button" class="button-link pss-collapse-field">Collapse</button><button type="button" class="button-link pss-duplicate-local-field">Duplicate</button><button type="button" class="button-link-delete pss-remove-local-field">Remove</button></span></div>';
		echo '<div class="pss-local-field-value pss-value-row__control">';
		self::render_editor( 'pss_local_field[' . $key . ']', $type, $value, $field );
		echo '</div></div>';
	}

	private static function library_source_options( $selected = '' ) {
		$html = '<option value="custom">Create a new field for this project</option>';
		foreach ( get_project_field_library_options() as $token => $def ) {
			$label = (string) ( $def['label'] ?? $def['key'] ?? $token );
			$type = (string) ( $def['type'] ?? 'text' );
			$html .= '<option value="' . esc_attr( $token ) . '" ' . selected( $selected, $token, false ) . '>' . esc_html( $label . ' — ' . $type ) . '</option>';
		}
		return $html;
	}

	private static function render_card_field_picker( $project_id, $defs ) {
		$selected = get_project_card_fields( $project_id );
		$available = get_project_core_card_labels();
		foreach ( $defs as $field ) {
			$key = sanitize_key( $field['key'] ?? '' );
			if ( $key ) $available[ $key ] = (string) ( $field['label'] ?? $key );
		}
		echo '<div class="pss-card-field-picker" id="pss-card-field-picker">';
		foreach ( $selected as $key ) {
			if ( ! isset( $available[ $key ] ) ) continue;
			echo '<div class="pss-card-field-row" draggable="true"><input type="hidden" name="pss_card_fields[]" value="' . esc_attr( $key ) . '"><span class="pss-drag-handle">⋮⋮</span><span class="pss-card-field-name">' . esc_html( $available[ $key ] ) . '</span><span class="pss-card-field-key">' . esc_html( $key ) . '</span><button type="button" class="button-link pss-card-move-up">↑</button><button type="button" class="button-link pss-card-move-down">↓</button><button type="button" class="button-link-delete pss-card-remove">Remove</button></div>';
		}
		echo '</div>';
		echo '<div class="pss-card-field-add-row"><select id="pss-card-field-add">';
		foreach ( $available as $key => $label ) {
			if ( in_array( $key, $selected, true ) ) continue;
			echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . ' — ' . esc_html( $key ) . '</option>';
		}
		echo '</select><button type="button" class="button" id="pss-add-card-field">Add to cards</button></div>';
	}

	private static function visibility_matches( $project_id, $visibility ) {
		if ( empty( $visibility['enabled'] ) || empty( $visibility['field_key'] ) ) return true;
		return compare_project_field_condition( $project_id, $visibility['field_key'], $visibility['operator'] ?? 'equals', $visibility['value'] ?? '' );
	}

	private static function render_editor( $name, $type, $value, $field ) {
		$attr = ' name="' . esc_attr( $name ) . '" ';
		$ph   = esc_attr( $field['placeholder'] ?? '' );
		$unit = (string) ( $field['unit'] ?? '' );
		if ( ( '' === $value || null === $value ) && isset( $field['default_value'] ) && '' !== $field['default_value'] ) {
			$value = $field['default_value'];
		}
		if ( is_array( $value ) && ! in_array( $type, array( 'repeater', 'group', 'table', 'icon_value', 'multi_select', 'checkbox', 'gallery', 'map', 'relationship' ), true ) ) {
			$value = '';
		}
		switch ( $type ) {
			case 'textarea':
				echo '<textarea' . $attr . 'rows="4" class="widefat" placeholder="' . $ph . '">' . esc_textarea( is_scalar( $value ) ? $value : '' ) . '</textarea>';
				break;
			case 'number':
				echo '<div class="pss-unit-field"><input type="number" step="any"' . $attr . 'value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '" class="widefat" placeholder="' . $ph . '">';
				if ( $unit ) { echo '<span class="pss-unit">' . esc_html( $unit ) . '</span>'; }
				echo '</div>';
				break;
			case 'date':
				echo '<input type="date"' . $attr . 'value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '" class="widefat">';
				break;
			case 'time':
				echo '<input type="time"' . $attr . 'value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '" class="widefat">';
				break;
			case 'email':
				echo '<input type="email"' . $attr . 'value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '" class="widefat" placeholder="' . ( $ph ?: 'name@example.com' ) . '">';
				break;
			case 'phone':
				echo '<input type="tel"' . $attr . 'value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '" class="widefat" placeholder="' . $ph . '">';
				break;
			case 'url':
			case 'video':
				echo '<input type="url"' . $attr . 'value="' . esc_attr( is_array( $value ) ? ( $value['url'] ?? '' ) : $value ) . '" class="widefat" placeholder="' . ( $ph ?: 'https://' ) . '">';
				break;
			case 'wysiwyg':
				echo '<textarea' . $attr . 'rows="8" class="widefat pss-wysiwyg">' . esc_textarea( is_scalar( $value ) ? $value : '' ) . '</textarea>';
				break;
			case 'color':
				echo '<input type="color"' . $attr . 'value="' . esc_attr( $value ? $value : '#111111' ) . '">';
				break;
			case 'file':
				echo '<div class="pss-media-field"><input type="hidden" class="pss-media-id" data-media-type="file"' . $attr . 'value="' . esc_attr( absint( $value ) ) . '"><button type="button" class="button pss-file-media">Choose file</button><span class="pss-media-current">' . esc_html( $value ? 'ID ' . absint( $value ) : '' ) . '</span></div>';
				break;
			case 'map':
				$map = is_array( $value ) ? $value : array( 'address' => is_string( $value ) ? $value : '' );
				echo '<div class="pss-map-field"><input type="text" class="widefat" name="' . esc_attr( $name ) . '[address]" value="' . esc_attr( $map['address'] ?? '' ) . '" placeholder="Address"><div class="pss-grid-3" style="margin-top:8px"><input type="text" name="' . esc_attr( $name ) . '[lat]" value="' . esc_attr( $map['lat'] ?? '' ) . '" placeholder="Lat"><input type="text" name="' . esc_attr( $name ) . '[lng]" value="' . esc_attr( $map['lng'] ?? '' ) . '" placeholder="Lng"></div></div>';
				break;
			case 'toggle':
				echo '<label class="pss-switch"><input type="hidden" name="' . esc_attr( $name ) . '" value="0"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . checked( ! empty( $value ), true, false ) . '><span>Enabled</span></label>';
				break;
			case 'select':
				echo '<select' . $attr . ' class="widefat"><option value="">— Select —</option>';
				foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
					echo '<option value="' . esc_attr( $option ) . '" ' . selected( $value, $option, false ) . '>' . esc_html( $option ) . '</option>';
				}
				echo '</select>';
				break;
			case 'radio':
				foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
					echo '<label class="pss-choice"><input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $option ) . '" ' . checked( (string) $value, (string) $option, false ) . '> ' . esc_html( $option ) . '</label>';
				}
				break;
			case 'checkbox':
				$vals = is_array( $value ) ? $value : array_filter( array( $value ) );
				foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
					echo '<label class="pss-choice"><input type="checkbox" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $option ) . '" ' . checked( in_array( $option, $vals, true ), true, false ) . '> ' . esc_html( $option ) . '</label>';
				}
				break;
			case 'relationship':
				$vals = is_array( $value ) ? array_map( 'absint', $value ) : array_filter( array( absint( $value ) ) );
				echo '<select multiple name="' . esc_attr( $name ) . '[]" class="widefat" size="6">';
				foreach ( get_posts( array( 'post_type' => PSS_PROJECT_CPT, 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => 'title', 'order' => 'ASC' ) ) as $project ) {
					echo '<option value="' . esc_attr( $project->ID ) . '" ' . selected( in_array( (int) $project->ID, $vals, true ), true, false ) . '>' . esc_html( $project->post_title ) . '</option>';
				}
				echo '</select>';
				break;
			case 'multi_select':
				$vals = is_array( $value ) ? $value : array();
				echo '<select multiple name="' . esc_attr( $name ) . '[]" class="widefat" size="5">';
				foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
					echo '<option value="' . esc_attr( $option ) . '" ' . selected( in_array( $option, $vals, true ), true, false ) . '>' . esc_html( $option ) . '</option>';
				}
				echo '</select>';
				break;
			case 'image':
				$img_id  = absint( is_array( $value ) ? ( $value['id'] ?? 0 ) : $value );
				$thumb   = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
				echo '<div class="pss-media-field"><input type="hidden" class="pss-media-id" data-media-type="image"' . $attr . 'value="' . esc_attr( $img_id ) . '"><button type="button" class="button pss-single-media">Choose Image</button>';
				if ( $thumb ) {
					echo '<img class="pss-media-thumb" src="' . esc_url( $thumb ) . '" alt="">';
				}
				echo '<span class="pss-media-current">' . esc_html( $img_id ? 'ID ' . $img_id : '' ) . '</span></div>';
				break;
			case 'gallery':
				$gallery = is_array( $value ) ? array_map( 'absint', $value ) : array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
				echo '<div class="pss-media-field"><input type="hidden" class="pss-media-id" data-media-type="gallery"' . $attr . 'value="' . esc_attr( implode( ',', $gallery ) ) . '"><button type="button" class="button pss-gallery-media">Choose Gallery</button><span class="pss-media-current">' . esc_html( count( $gallery ) ? count( $gallery ) . ' image(s)' : '' ) . '</span></div>';
				break;
			case 'repeater':
				self::render_repeater( $name, $value, (array) ( $field['subfields'] ?? array() ) );
				break;
			case 'group':
				self::render_group( $name, $value, (array) ( $field['subfields'] ?? array() ) );
				break;
			case 'table':
				self::render_table( $name, $value, (array) ( $field['subfields'] ?? array() ) );
				break;
			case 'icon_value':
				self::render_icon_value( $name, $value );
				break;
			default:
				echo '<div class="pss-unit-field"><input type="text" class="widefat"' . $attr . 'value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '" placeholder="' . $ph . '">';
				if ( $unit ) { echo '<span class="pss-unit">' . esc_html( $unit ) . '</span>'; }
				echo '</div>';
		}
	}

	private static function render_subfield( $base, $field, $value = '' ) {
		$type = $field['type'] ?? 'text';
		self::render_editor( $base, $type, $value, $field );
	}

	private static function render_repeater( $name, $value, $subfields ) {
		$value = is_array( $value ) ? $value : array();
		echo '<div class="pss-repeater" data-name="' . esc_attr( $name ) . '"><div class="pss-repeater-list">';
		foreach ( $value as $i => $row ) {
			echo '<div class="pss-repeater-row"><div class="pss-repeater-row__head"><strong>Item ' . ( $i + 1 ) . '</strong><button type="button" class="button-link-delete pss-remove-repeater">Remove</button></div><div class="pss-repeater-fields">';
			foreach ( $subfields as $subfield ) {
				$key = sanitize_key( $subfield['key'] ?? '' );
				echo '<label>' . esc_html( $subfield['label'] ?? $key ) . '</label>';
				self::render_subfield( $name . '[' . $i . '][' . $key . ']', $subfield, is_array( $row ) ? ( $row[ $key ] ?? '' ) : '' );
			}
			echo '</div></div>';
		}
		echo '<div class="pss-repeater-row pss-template" aria-hidden="true"><div class="pss-repeater-row__head"><strong>Item</strong><button type="button" class="button-link-delete pss-remove-repeater">Remove</button></div><div class="pss-repeater-fields">';
		foreach ( $subfields as $subfield ) {
			$key = sanitize_key( $subfield['key'] ?? '' );
			echo '<label>' . esc_html( $subfield['label'] ?? $key ) . '</label>';
			self::render_subfield( $name . '[__INDEX__][' . $key . ']', $subfield, '' );
		}
		echo '</div></div></div><button type="button" class="button pss-add-repeater">+ Add Item</button></div>';
	}

	private static function render_group( $name, $value, $subfields ) {
		$value = is_array( $value ) ? $value : array();
		echo '<div class="pss-group-fields">';
		foreach ( $subfields as $subfield ) {
			$key = sanitize_key( $subfield['key'] ?? '' );
			echo '<div class="pss-group-field"><label>' . esc_html( $subfield['label'] ?? $key ) . '</label>';
			self::render_subfield( $name . '[' . $key . ']', $subfield, $value[ $key ] ?? '' );
			echo '</div>';
		}
		echo '</div>';
	}

	private static function render_table( $name, $value, $columns ) {
		$value = is_array( $value ) ? $value : array();
		echo '<div class="pss-data-table" data-name="' . esc_attr( $name ) . '"><table><thead><tr>';
		foreach ( $columns as $column ) echo '<th>' . esc_html( $column['label'] ?? $column['key'] ?? '' ) . '</th>';
		echo '<th></th></tr></thead><tbody>';
		foreach ( $value as $i => $row ) {
			echo '<tr>';
			foreach ( $columns as $column ) {
				$key = sanitize_key( $column['key'] ?? '' );
				echo '<td><input type="text" class="widefat" name="' . esc_attr( $name . '[' . $i . '][' . $key . ']' ) . '" value="' . esc_attr( is_array( $row ) ? ( $row[ $key ] ?? '' ) : '' ) . '"></td>';
			}
			echo '<td><button type="button" class="button-link-delete pss-remove-table-row">Remove</button></td></tr>';
		}
		echo '<tr class="pss-table-template" aria-hidden="true">';
		foreach ( $columns as $column ) {
			$key = sanitize_key( $column['key'] ?? '' );
			echo '<td><input type="text" class="widefat" name="' . esc_attr( $name . '[__INDEX__][' . $key . ']' ) . '" value=""></td>';
		}
		echo '<td><button type="button" class="button-link-delete pss-remove-table-row">Remove</button></td></tr>';
		echo '</tbody></table><button type="button" class="button pss-add-table-row">+ Add Row</button></div>';
	}

	private static function render_icon_value( $name, $value ) {
		$value = is_array( $value ) ? $value : array();
		echo '<div class="pss-icon-value-editor" data-name="' . esc_attr( $name ) . '"><div class="pss-icon-value-list">';
		foreach ( $value as $i => $row ) {
			echo '<div class="pss-icon-value-row"><input type="text" name="' . esc_attr( $name . '[' . $i . '][icon]' ) . '" value="' . esc_attr( $row['icon'] ?? '' ) . '" placeholder="Icon"><input type="text" name="' . esc_attr( $name . '[' . $i . '][title]' ) . '" value="' . esc_attr( $row['title'] ?? '' ) . '" placeholder="Title"><input type="text" name="' . esc_attr( $name . '[' . $i . '][value]' ) . '" value="' . esc_attr( $row['value'] ?? '' ) . '" placeholder="Value"><button type="button" class="button-link-delete pss-remove-icon-value">Remove</button></div>';
		}
		echo '<div class="pss-icon-value-row pss-template" aria-hidden="true"><input type="text" name="' . esc_attr( $name . '[__INDEX__][icon]' ) . '" value="" placeholder="Icon"><input type="text" name="' . esc_attr( $name . '[__INDEX__][title]' ) . '" value="" placeholder="Title"><input type="text" name="' . esc_attr( $name . '[__INDEX__][value]' ) . '" value="" placeholder="Value"><button type="button" class="button-link-delete pss-remove-icon-value">Remove</button></div>';
		echo '</div><button type="button" class="button pss-add-icon-value">+ Add Item</button></div>';
	}

	public static function save_project_fields( $post_id ) {
		$defs  = get_field_definitions();
		$input = isset( $_POST['pss_field'] ) ? wp_unslash( $_POST['pss_field'] ) : array();
		$core_library = get_project_core_field_library();
		foreach ( $defs as $field ) {
			$key   = sanitize_key( $field['key'] );
			if ( ! array_key_exists( $key, $input ) ) { continue; }
			$type  = $field['type'];
			$value = $input[ $key ] ?? '';
			$value = self::sanitize_value_by_type( $value, $type, $field );
			if ( '' === $value || array() === $value ) {
				delete_post_meta( $post_id, '_pss_field_' . $key );
			} else {
				update_post_meta( $post_id, '_pss_field_' . $key, $value );
			}
			if ( isset( $core_library[ $key ]['meta_key'] ) ) {
				if ( '' === $value || array() === $value ) {
					delete_post_meta( $post_id, $core_library[ $key ]['meta_key'] );
				} else {
					update_post_meta( $post_id, $core_library[ $key ]['meta_key'], is_array( $value ) ? $value : (string) $value );
				}
			}
		}

		$raw_defs = isset( $_POST['pss_local_defs'] ) ? wp_unslash( $_POST['pss_local_defs'] ) : array();
		$raw_vals = isset( $_POST['pss_local_field'] ) ? wp_unslash( $_POST['pss_local_field'] ) : array();
		$local_defs = array();
		$local_values = array();
		$used = array();
		foreach ( (array) $raw_defs as $row ) {
			$label = sanitize_text_field( $row['label'] ?? '' );
			$key = sanitize_key( $row['key'] ?? sanitize_title( $label ) );
			if ( ! $label || ! $key || isset( $used[ $key ] ) ) continue;
			$used[ $key ] = true;
			$type = sanitize_key( $row['type'] ?? 'text' );
			if ( ! isset( self::types()[ $type ] ) ) $type = 'text';
			$options = preg_split( '/\r\n|\r|\n/', (string) ( $row['options'] ?? '' ) );
			$options = array_values( array_filter( array_map( 'sanitize_text_field', (array) $options ) ) );
			$subfields = json_decode( wp_unslash( $row['subfields_json'] ?? '' ), true );
			$subfields = is_array( $subfields ) ? self::sanitize_subfields( $subfields, 'table' === $type ) : array();
			$source_raw = sanitize_text_field( $row['source'] ?? 'custom' );
			$source = 'custom';
			$source_key = '';
			$source_def = array();
			$library = get_project_field_library_options();
			if ( isset( $library[ $source_raw ] ) ) {
				$source_def = $library[ $source_raw ];
				$source = sanitize_key( $source_def['source'] ?? 'custom' );
				$source_key = sanitize_key( $source_def['key'] ?? '' );
				$key = sanitize_key( $source_def['record_key'] ?? $key );
				$label = (string) ( $source_def['label'] ?? $label );
				$type = sanitize_key( $source_def['type'] ?? $type );
				$options = (array) ( $source_def['options'] ?? $options );
				$subfields = is_array( $source_def['subfields'] ?? null ) ? $source_def['subfields'] : $subfields;
			}
			$field = array(
				'source' => $source,
				'source_key' => $source_key,
				'label' => $label,
				'key' => $key,
				'type' => $type,
				'description' => sanitize_text_field( $row['description'] ?? '' ),
				'options' => $options,
				'subfields' => $subfields,
			);
			$local_defs[] = $field;
			$value = $raw_vals[ $key ] ?? '';
			$value = self::sanitize_value_by_type( $value, $type, $field );
			if ( 'core' === $source && $source_key ) {
				$core_library = get_project_core_field_library();
				if ( isset( $core_library[ $source_key ]['meta_key'] ) ) {
					if ( '' === $value || array() === $value ) {
						delete_post_meta( $post_id, $core_library[ $source_key ]['meta_key'] );
					} else {
						update_post_meta( $post_id, $core_library[ $source_key ]['meta_key'], $value );
					}
				}
			}
			if ( '' !== $value && array() !== $value ) {
				$local_values[ $key ] = $value;
				update_post_meta( $post_id, '_pss_field_' . $key, $value );
			} else {
				delete_post_meta( $post_id, '_pss_field_' . $key );
			}
		}
		$previous_local = get_project_local_field_values( $post_id );
		foreach ( array_keys( (array) $previous_local ) as $old_key ) {
			$old_key = sanitize_key( $old_key );
			if ( $old_key && ! isset( $used[ $old_key ] ) ) {
				delete_post_meta( $post_id, '_pss_field_' . $old_key );
			}
		}
		update_post_meta( $post_id, '_pss_local_field_definitions', $local_defs );
		update_post_meta( $post_id, '_pss_local_field_values', $local_values );

		$card_fields = isset( $_POST['pss_card_fields'] ) ? (array) wp_unslash( $_POST['pss_card_fields'] ) : array();
		$allowed = array_keys( get_project_core_card_labels() );
		foreach ( get_field_definitions( $post_id ) as $field ) {
			$key = sanitize_key( $field['key'] ?? '' );
			if ( $key ) $allowed[] = $key;
		}
		$card_fields = array_values( array_unique( array_filter( array_map( __NAMESPACE__ . '\\sanitize_card_field_key', $card_fields ) ) ) );
		$card_fields = array_values( array_intersect( $card_fields, $allowed ) );
		update_post_meta( $post_id, '_pss_card_fields', $card_fields );
	}

	private static function sanitize_value_by_type( $value, $type, $field ) {
		if ( 'gallery' === $type ) {
			if ( is_array( $value ) ) return array_values( array_filter( array_map( 'absint', $value ) ) );
			return array_values( array_filter( array_map( 'absint', explode( ',', (string) $value ) ) ) );
		}
		if ( in_array( $type, array( 'image', 'file' ), true ) ) return absint( is_array( $value ) ? ( $value['id'] ?? 0 ) : $value );
		if ( in_array( $type, array( 'multi_select', 'checkbox', 'relationship' ), true ) ) {
			$cb = ( 'relationship' === $type ) ? 'absint' : 'sanitize_text_field';
			return array_values( array_filter( array_map( $cb, (array) $value ) ) );
		}
		if ( 'toggle' === $type ) return empty( $value ) ? 0 : 1;
		if ( in_array( $type, array( 'number' ), true ) ) return is_numeric( $value ) ? (float) $value : '';
		if ( 'email' === $type ) return sanitize_email( is_scalar( $value ) ? $value : '' );
		if ( in_array( $type, array( 'date', 'time', 'color', 'phone', 'icon' ), true ) ) return sanitize_text_field( is_scalar( $value ) ? $value : '' );
		if ( in_array( $type, array( 'url', 'video' ), true ) ) return esc_url_raw( is_array( $value ) ? ( $value['url'] ?? '' ) : $value );
		if ( 'map' === $type ) {
			$map = is_array( $value ) ? $value : array( 'address' => (string) $value );
			return array( 'address' => sanitize_text_field( $map['address'] ?? '' ), 'lat' => sanitize_text_field( $map['lat'] ?? '' ), 'lng' => sanitize_text_field( $map['lng'] ?? '' ) );
		}
		if ( in_array( $type, array( 'repeater', 'group', 'table', 'icon_value' ), true ) ) {
			return self::sanitize_structured( $value );
		}
		return wp_kses_post( (string) $value );
	}

	private static function sanitize_structured( $value ) {
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( $value as $key => $item ) {
				$clean_key = is_numeric( $key ) ? (int) $key : sanitize_key( $key );
				$out[ $clean_key ] = self::sanitize_structured( $item );
			}
			return $out;
		}
		return wp_kses_post( (string) $value );
	}
}
