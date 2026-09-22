<?php
namespace PSS\Elementor\Widgets;
class Project_Video extends Base {
	public function get_name() { return 'pss_project_video'; }
	public function get_title() { return 'Project Video'; }
	public function get_icon() { return 'eicon-video-camera'; }
	protected function register_controls() { $this->add_source_controls(); $this->start_controls_section('content',array('label'=>'Video'));$this->add_control('ratio',array('label'=>'Ratio','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'16-9','options'=>array('16-9'=>'16:9','4-3'=>'4:3','1-1'=>'1:1')));$this->end_controls_section(); }
	protected function render() {$s=$this->get_settings_for_display();$id=$this->project_id($s);if(!$id)return;$url=\PSS\get_meta($id,'_pss_video');if(!$url)return;$html=wp_oembed_get($url);if(!$html)$html='<video controls src="'.esc_url($url).'" preload="metadata"></video>';echo '<div class="pss-video pss-video--'.esc_attr($s['ratio']).'">'.$html.'</div>';}
}
