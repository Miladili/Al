<?php
namespace PSS\Elementor\Widgets;

class Project_CTA extends Base {
    public function get_name() { return 'pss_project_cta'; }
    public function get_title() { return 'Project CTA'; }
    public function get_icon() { return 'eicon-button'; }
    protected function register_controls() {
        $this->add_source_controls();
        $this->start_controls_section('content', array('label'=>'CTA','tab'=>\Elementor\Controls_Manager::TAB_CONTENT));
        $this->add_control('text', array('label'=>'Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>'View project')); 
        $this->add_control('icon', array('label'=>'Icon','type'=>\Elementor\Controls_Manager::TEXT,'default'=>'↗'));
        $this->end_controls_section();
    }
    protected function render() {$s=$this->get_settings_for_display();$id=$this->project_id($s);if(!$id)return;echo '<a class="pss-project-cta" href="'.esc_url(get_permalink($id)).'"><span>'.esc_html($s['text']??'View project').'</span><b>'.esc_html($s['icon']??'↗').'</b></a>';}
}
