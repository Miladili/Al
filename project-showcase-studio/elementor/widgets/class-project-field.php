<?php
namespace PSS\Elementor\Widgets;

class Project_Field extends Base {
    public function get_name() { return 'pss_project_field'; }
    public function get_title() { return 'Project Field'; }
    public function get_icon() { return 'eicon-database'; }
    protected function register_controls() {
        $this->add_source_controls();
        $this->start_controls_section('field', array('label'=>'Field','tab'=>\Elementor\Controls_Manager::TAB_CONTENT));
        $this->add_control('field_key', array('label'=>'Field key','type'=>\Elementor\Controls_Manager::TEXT,'placeholder'=>'cabinet_material','description'=>'Works with reusable and project-only fields.')); 
        $this->add_control('show_label', array('label'=>'Show label','type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
        $this->add_control('empty_text', array('label'=>'Empty text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>''));
        $this->end_controls_section();
        $this->start_controls_section('style', array('label'=>'Style','tab'=>\Elementor\Controls_Manager::TAB_STYLE));
        $this->add_control('label_color', array('label'=>'Label color','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>array('{{WRAPPER}} .pss-project-field-widget__label'=>'color: {{VALUE}};')));
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), array('name'=>'value_typo','selector'=>'{{WRAPPER}} .pss-project-field-widget__value'));
        $this->end_controls_section();
    }
    protected function render() {
        $s=$this->get_settings_for_display();$id=$this->project_id($s);$key=sanitize_key($s['field_key']??'');if(!$id||!$key)return;
        $def=\PSS\get_field_definition($id,$key);$value=\PSS\get_field_value($id,$key,'');$text=\PSS\field_value_text($value);
        if(''===$text){if(''!==($s['empty_text']??''))echo '<span class="pss-project-field-widget__empty">'.esc_html($s['empty_text']).'</span>';return;}
        echo '<div class="pss-project-field-widget">';if(!empty($s['show_label']))echo '<span class="pss-project-field-widget__label">'.esc_html($def['label']??$key).'</span>';echo '<div class="pss-project-field-widget__value">'.esc_html($text).'</div></div>';
    }
}
