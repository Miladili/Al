<?php
namespace PSS;

defined( 'ABSPATH' ) || exit;

class Fields {
	public static function init() {
		add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
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
			<h1>Project Fields</h1>
			<p class="description">Create reusable fields for Projects. These fields are filled in the Project editor and can be displayed from Elementor.</p>
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
		if ( in_array( $type, array( 'select', 'multi_select' ), true ) ) : ?>
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
		$types = array(
			'text' => 'Text', 'textarea' => 'Textarea', 'number' => 'Number', 'date' => 'Date', 'url' => 'URL', 'image' => 'Image',
			'gallery' => 'Gallery', 'select' => 'Select', 'multi_select' => 'Multi Select', 'toggle' => 'Toggle', 'repeater' => 'Repeater',
			'group' => 'Group', 'table' => 'Table', 'icon_value' => 'Icon + Title + Value',
		);
		foreach ( $types as $value => $label ) {
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
				'label'       => $label,
				'key'         => $key,
				'type'        => $type,
				'description' => sanitize_text_field( $field['description'] ?? '' ),
				'required'    => ! empty( $field['required'] ),
				'options'     => $options,
				'subfields'   => $subfields,
				'visibility'  => $visibility,
			);
		}
		update_option( 'pss_field_definitions', $defs, false );
		wp_safe_redirect( add_query_arg( array( 'post_type' => PSS_PROJECT_CPT, 'page' => 'pss-project-fields', 'updated' => '1' ), admin_url( 'edit.php' ) ) );
		exit;
	}

	private static function types() {
		return array(
			'text' => 'Text', 'textarea' => 'Textarea', 'number' => 'Number', 'date' => 'Date', 'url' => 'URL', 'image' => 'Image',
			'gallery' => 'Gallery', 'select' => 'Select', 'multi_select' => 'Multi Select', 'toggle' => 'Toggle', 'repeater' => 'Repeater',
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
			if ( ! isset( self::types()[ $type ] ) || in_array( $type, array( 'repeater', 'group', 'table', 'icon_value' ), true ) ) {
				$type = 'text';
			}
			$out[] = array( 'label' => $label, 'key' => $key, 'type' => $type );
		}
		return $out;
	}

	public static function render_project_fields( $project_id ) {
		$global_defs = get_field_definitions();
		$local_defs = get_project_local_field_definitions( $project_id );
		$local_values = get_project_local_field_values( $project_id );
		$library = get_project_field_library_options();
		$defs = get_field_definitions( $project_id );
		$used = array();
		foreach ( $local_defs as $field ) {
			$key = sanitize_key( $field['key'] ?? '' );
			if ( $key ) $used[ $key ] = true;
		}

		// Legacy/shared fields that already contain data are surfaced as normal data records,
		// so users can continue editing existing projects without seeing a rigid schema.
		foreach ( $library as $source_token => $def ) {
			$record_key = sanitize_key( $def['record_key'] ?? '' );
			if ( ! $record_key || isset( $used[ $record_key ] ) ) continue;
			$value = get_field_value( $project_id, $record_key, '' );
			$has_value = is_array( $value ) ? ! empty( $value ) : ( '' !== (string) $value );
			if ( ! $has_value || ! in_array( $def['source'] ?? '', array( 'global', 'core' ), true ) ) continue;
			$local_defs[] = array(
				'source' => $def['source'],
				'label' => $def['label'] ?? $record_key,
				'key' => $record_key,
				'type' => $def['type'] ?? 'text',
				'description' => $def['description'] ?? '',
				'options' => $def['options'] ?? array(),
				'subfields' => $def['subfields'] ?? array(),
				'source_key' => $def['key'] ?? '',
			);
			$used[ $record_key ] = true;
		}

		$record_count = count( $local_defs );
		echo '<script>window.PSSProjectFieldLibrary=' . wp_json_encode( $library ) . ';</script>';
		echo '<div class="pss-field-editor-shell">';
		echo '<div class="pss-field-editor-hero"><div><span class="pss-editor-kicker">PROJECT DATA</span><h2>Build this project from data records</h2><p>This editor is intentionally schema-free. Add a record, choose a reusable field from the library or create a brand-new field, then enter its value. Different projects can have completely different fields.</p></div><div class="pss-editor-pill">' . esc_html( $record_count ) . ' records</div></div>';

		echo '<div class="pss-data-builder-toolbar">';
		echo '<div class="pss-data-builder-toolbar__copy"><strong>Add project data</strong><span>Choose an existing field or create a new one. You can add as many records as the project needs.</span></div>';
		echo '<div class="pss-data-builder-toolbar__actions"><input type="search" id="pss-quick-record-search" class="widefat" placeholder="Search fields…"><select id="pss-quick-record-source" class="widefat">';
		echo '<option value="">+ Add record from library…</option>';
		foreach ( $library as $source_token => $def ) {
			$record_key = sanitize_key( $def['record_key'] ?? '' );
			if ( ! $record_key || isset( $used[ $record_key ] ) ) continue;
			echo '<option value="' . esc_attr( $source_token ) . '">' . esc_html( ( $def['label'] ?? $record_key ) . ' — ' . ( $def['type'] ?? 'text' ) ) . '</option>';
		}
		echo '</select><button type="button" class="button button-secondary" id="pss-add-library-record">Add selected</button><button type="button" class="button button-primary" id="pss-add-local-field">+ New custom record</button></div>';
		echo '</div>';

		echo '<div id="pss-local-fields-list" class="pss-record-list">';
		foreach ( $local_defs as $index => $field ) {
			$key = sanitize_key( $field['key'] ?? '' );
			$value = array_key_exists( $key, $local_values ) ? $local_values[ $key ] : get_field_value( $project_id, $key, '' );
			self::render_local_field_row( $index, $field, $value );
		}
		echo '</div>';
		echo '<div id="pss-local-field-empty" class="pss-empty-panel"' . ( empty( $local_defs ) ? '' : ' style="display:none"' ) . '><strong>No project data records yet.</strong><span>Use the library selector above or add a custom record. Nothing is mandatory.</span></div>';

		echo '<div class="pss-card-data-panel">';
		echo '<div class="pss-section-head"><div><h3>Card display fields</h3><p>Pick any core field or custom field and decide exactly what appears on Project Showcase cards.</p></div><span class="pss-field-library__count">Per project</span></div>';
		self::render_card_field_picker( $project_id, $defs );
		echo '</div>';
		echo '</div>';
	}

	private static function render_local_field_row( $index, $field, $value = '' ) {
		$type = sanitize_key( $field['type'] ?? 'text' );
		$label = (string) ( $field['label'] ?? '' );
		$key = sanitize_key( $field['key'] ?? sanitize_title( $label ) );
		$options = (array) ( $field['options'] ?? array() );
		$subfields = is_array( $field['subfields'] ?? null ) ? $field['subfields'] : array();
		$source = sanitize_key( $field['source'] ?? 'custom' );
		$source_key = sanitize_key( $field['source_key'] ?? '' );
		if ( ! $source_key && in_array( $source, array( 'global', 'core' ), true ) ) {
			$source_key = 'core' === $source && 0 === strpos( $key, 'core_' ) ? substr( $key, 5 ) : $key;
		}
		$source_token = 'global' === $source ? 'global:' . $source_key : ( 'core' === $source ? 'core:' . $source_key : 'custom' );
		?>
		<div class="pss-local-field-builder" draggable="true" data-index="<?php echo esc_attr( $index ); ?>" data-current-value="<?php echo esc_attr( wp_json_encode( $value ) ); ?>">
			<div class="pss-local-field-builder__header"><div><span class="pss-editor-kicker">PROJECT RECORD</span><strong><?php echo esc_html( $label ?: 'New data record' ); ?></strong><span class="pss-record-source-badge"><?php echo 'custom' === $source ? 'Project-only field' : ( 'core' === $source ? 'Built-in field' : 'Reusable field' ); ?></span></div><div class="pss-local-field-actions"><button type="button" class="button-link pss-duplicate-local-field">Duplicate</button><button type="button" class="button-link-delete pss-remove-local-field">Remove</button></div></div>
			<div class="pss-grid-3 pss-record-source-row">
				<label>Field source<select class="pss-local-field-source widefat" name="pss_local_defs[<?php echo esc_attr( $index ); ?>][source]"><?php echo self::library_source_options( $source_token ); ?></select></label>
				<label>Label<input type="text" name="pss_local_defs[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" class="widefat"></label>
				<label>Key<input type="text" name="pss_local_defs[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" class="widefat" placeholder="e.g. ceiling_height"></label>
			</div>
			<div class="pss-grid-3">
				<label>Type<select class="pss-local-field-type" name="pss_local_defs[<?php echo esc_attr( $index ); ?>][type]"><?php self::type_options( $type ); ?></select></label>
				<label>Options / structure<textarea name="pss_local_defs[<?php echo esc_attr( $index ); ?>][options]" class="pss-local-options widefat" rows="3" placeholder="For Select / Multi Select: one option per line"><?php echo esc_textarea( implode( "\n", $options ) ); ?></textarea></label>
				<label>Description<input type="text" name="pss_local_defs[<?php echo esc_attr( $index ); ?>][description]" value="<?php echo esc_attr( $field['description'] ?? '' ); ?>" class="widefat"></label>
			</div>
			<?php if ( ! empty( $subfields ) ) : ?><input type="hidden" class="pss-local-subfields-json" name="pss_local_defs[<?php echo esc_attr( $index ); ?>][subfields_json]" value="<?php echo esc_attr( wp_json_encode( $subfields ) ); ?>"><?php else : ?><input type="hidden" class="pss-local-subfields-json" name="pss_local_defs[<?php echo esc_attr( $index ); ?>][subfields_json]" value="[]"><?php endif; ?>
			<div class="pss-local-field-structure" data-existing-options="<?php echo esc_attr( implode( "\n", $options ) ); ?>" data-existing-subfields="<?php echo esc_attr( wp_json_encode( $subfields ) ); ?>"></div>
			<div class="pss-local-field-value"><label>Value</label><?php self::render_editor( 'pss_local_field[' . $key . ']', $type, $value, $field ); ?></div>
		</div>
		<?php
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
		switch ( $type ) {
			case 'textarea':
				echo '<textarea' . $attr . 'rows="4" class="widefat">' . esc_textarea( $value ) . '</textarea>';
				break;
			case 'number':
				echo '<input type="number" step="any"' . $attr . 'value="' . esc_attr( $value ) . '" class="widefat">';
				break;
			case 'date':
				echo '<input type="date"' . $attr . 'value="' . esc_attr( $value ) . '" class="widefat">';
				break;
			case 'url':
				echo '<input type="url"' . $attr . 'value="' . esc_attr( $value ) . '" class="widefat">';
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
			case 'multi_select':
				$vals = is_array( $value ) ? $value : array();
				echo '<select multiple name="' . esc_attr( $name ) . '[]" class="widefat" size="5">';
				foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
					echo '<option value="' . esc_attr( $option ) . '" ' . selected( in_array( $option, $vals, true ), true, false ) . '>' . esc_html( $option ) . '</option>';
				}
				echo '</select>';
				break;
			case 'image':
				echo '<div class="pss-media-field"><input type="hidden" class="pss-media-id" data-media-type="image"' . $attr . 'value="' . esc_attr( absint( $value ) ) . '"><button type="button" class="button pss-single-media">Choose Image</button><span class="pss-media-current">' . esc_html( $value ? 'ID ' . absint( $value ) : '' ) . '</span></div>';
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
				echo '<input type="text" class="widefat"' . $attr . 'value="' . esc_attr( $value ) . '">';
		}
	}

	private static function render_subfield( $base, $field, $value = '' ) {
		$type = $field['type'] ?? 'text';
		switch ( $type ) {
			case 'textarea': echo '<textarea class="widefat" name="' . esc_attr( $base ) . '" rows="2">' . esc_textarea( $value ) . '</textarea>'; break;
			case 'number': echo '<input type="number" step="any" class="widefat" name="' . esc_attr( $base ) . '" value="' . esc_attr( $value ) . '">'; break;
			case 'date': echo '<input type="date" class="widefat" name="' . esc_attr( $base ) . '" value="' . esc_attr( $value ) . '">'; break;
			case 'url': echo '<input type="url" class="widefat" name="' . esc_attr( $base ) . '" value="' . esc_attr( $value ) . '">'; break;
			default: echo '<input type="text" class="widefat" name="' . esc_attr( $base ) . '" value="' . esc_attr( $value ) . '">';
		}
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
		foreach ( $defs as $field ) {
			$key   = sanitize_key( $field['key'] );
			$type  = $field['type'];
			$value = $input[ $key ] ?? '';
			$value = self::sanitize_value_by_type( $value, $type, $field );
			if ( '' === $value || array() === $value ) {
				delete_post_meta( $post_id, '_pss_field_' . $key );
			} else {
				update_post_meta( $post_id, '_pss_field_' . $key, $value );
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
			if ( '' !== $value && array() !== $value ) $local_values[ $key ] = $value;
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
		if ( 'image' === $type ) return absint( $value );
		if ( 'multi_select' === $type ) return array_values( array_filter( array_map( 'sanitize_text_field', (array) $value ) ) );
		if ( 'toggle' === $type ) return empty( $value ) ? 0 : 1;
		if ( in_array( $type, array( 'number' ), true ) ) return is_numeric( $value ) ? (float) $value : '';
		if ( in_array( $type, array( 'date' ), true ) ) return sanitize_text_field( $value );
		if ( 'url' === $type ) return esc_url_raw( $value );
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
