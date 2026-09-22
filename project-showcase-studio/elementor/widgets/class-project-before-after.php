<?php
namespace PSS\Elementor\Widgets;
class Project_Before_After extends Base {
	public function get_name() { return 'pss_project_before_after'; }
	public function get_title() { return 'Project Before / After'; }
	public function get_icon() { return 'eicon-image-before-after'; }
	protected function register_controls() { $this->add_source_controls(); $this->start_controls_section('content',array('label'=>'Before / After'));$this->add_responsive_control('height',array('label'=>'Height','type'=>\Elementor\Controls_Manager::NUMBER,'default'=>520,'mobile_default'=>360));$this->end_controls_section(); }
	protected function render() {$s=$this->get_settings_for_display();$id=$this->project_id($s);if(!$id)return;$before=absint(\PSS\get_meta($id,'_pss_before',0));$after=absint(\PSS\get_meta($id,'_pss_after',0));$b=wp_get_attachment_image_url($before,'full');$a=wp_get_attachment_image_url($after,'full');if(!$b||!$a)return;echo '<div class="pss-before-after" style="--pss-ba-height:'.absint($s['height']??520).'px"><img src="'.esc_url($a).'" alt="After"><div class="pss-before-after__clip"><img src="'.esc_url($b).'" alt="Before"></div><input type="range" min="0" max="100" value="50" aria-label="Before After position"></div>';}
}
