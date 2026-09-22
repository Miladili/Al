<?php
namespace PSS\Elementor\Widgets;

class Project_Breadcrumbs extends Base {
    public function get_name() { return 'pss_project_breadcrumbs'; }
    public function get_title() { return 'Project Breadcrumbs'; }
    public function get_icon() { return 'eicon-product-breadcrumbs'; }
    protected function register_controls() { $this->start_controls_section('content', array('label'=>'Breadcrumbs','tab'=>\Elementor\Controls_Manager::TAB_CONTENT)); $this->add_control('home_label', array('label'=>'Home label','type'=>\Elementor\Controls_Manager::TEXT,'default'=>'Home')); $this->add_control('projects_label', array('label'=>'Projects label','type'=>\Elementor\Controls_Manager::TEXT,'default'=>'Projects')); $this->end_controls_section(); }
    protected function render() {$id=\PSS\get_project_id();if(!$id)return;echo '<nav class="pss-project-breadcrumbs" aria-label="Breadcrumb"><a href="'.esc_url(home_url('/')).'">'.esc_html($this->get_settings_for_display()['home_label']??'Home').'</a><span> / </span><a href="'.esc_url(get_post_type_archive_link(\PSS_PROJECT_CPT)).'">'.esc_html($this->get_settings_for_display()['projects_label']??'Projects').'</a><span> / </span><strong>'.esc_html(get_the_title($id)).'</strong></nav>';}
}
