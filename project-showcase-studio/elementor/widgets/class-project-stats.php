<?php
namespace PSS\Elementor\Widgets;

class Project_Stats extends Base {
    public function get_name() { return 'pss_project_stats'; }
    public function get_title() { return 'Project Stats'; }
    public function get_icon() { return 'eicon-counter-circle'; }
    protected function register_controls() {
        $this->add_source_controls();
        $this->start_controls_section('content', array('label'=>'Stats','tab'=>\Elementor\Controls_Manager::TAB_CONTENT));
        $this->add_control('fields', array('label'=>'Field keys','type'=>\Elementor\Controls_Manager::TEXTAREA,'placeholder'=>'area, year, location, cabinet_material','description'=>'One key per line. Core fields and custom fields are supported.'));
        $this->add_responsive_control('columns', array('label'=>'Columns','type'=>\Elementor\Controls_Manager::NUMBER,'default'=>4,'tablet_default'=>2,'mobile_default'=>1,'min'=>1,'max'=>6));
        $this->end_controls_section();
    }
    protected function render() {
        $s=$this->get_settings_for_display();$id=$this->project_id($s);if(!$id)return;$keys=array_filter(array_map(function($key){ $key=trim((string)$key); return 0===strpos($key,'core:') ? 'core:'.sanitize_key(substr($key,5)) : sanitize_key($key); },preg_split('/[,\n]+/',(string)($s['fields']??''))));if(!$keys)$keys=array('core:year','core:area','core:location','core:type');
        echo '<div class="pss-project-stats" style="--pss-stats-cols:'.esc_attr(absint($s['columns']??4)).'">';foreach($keys as $key){$data=\PSS\get_project_card_field($id,strpos($key,'core:')===0?$key:$key);$value=\PSS\field_value_text($data['value']??'');if(''===$value)continue;echo '<div class="pss-project-stat"><span>'.esc_html($data['label']??$key).'</span><strong>'.esc_html($value).'</strong></div>';}echo '</div>';
    }
}
