<?php
namespace PSS\Elementor\Widgets;
class Project_Custom_Fields extends Base {
	public function get_name() { return 'pss_project_custom_fields'; }
	public function get_title() { return 'Project Custom Fields'; }
	public function get_icon() { return 'eicon-form-horizontal'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section('content',array('label'=>'Display'));
		$this->add_control('layout',array('label'=>'Layout','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'grid','options'=>array('grid'=>'Grid','list'=>'List','cards'=>'Cards')));
		$this->add_control('show_labels',array('label'=>'Show Labels','type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
		$this->end_controls_section();
	}
	protected function render() {
		$s=$this->get_settings_for_display(); $id=$this->project_id($s); if(!$id)return; $defs=\PSS\get_field_definitions($id); if(!$defs)return;
		echo '<div class="pss-custom-fields pss-custom-fields--'.esc_attr($s['layout']).'">'; foreach($defs as $field){$key=sanitize_key($field['key']); $value=\PSS\get_field_value($id,$key,''); if(''===$value || array() === $value)continue; echo '<div class="pss-custom-field">'; if('yes'===$s['show_labels'])echo '<span class="pss-custom-field__label">'.esc_html($field['label']).'</span>'; echo '<div class="pss-custom-field__value">'.\PSS\render_field_value($value,$field['type'],$field).'</div></div>';} echo '</div>';
	}
}
