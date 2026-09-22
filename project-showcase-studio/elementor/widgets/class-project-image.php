<?php
namespace PSS\Elementor\Widgets;
class Project_Image extends Base {
	public function get_name() { return 'pss_project_image'; }
	public function get_title() { return 'Project Image'; }
	public function get_icon() { return 'eicon-image'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section('content',array('label'=>'Image'));
		$this->add_control('image_size',array('label'=>'Image Size','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'full','options'=>array('thumbnail'=>'Thumbnail','medium_large'=>'Medium Large','large'=>'Large','full'=>'Full')));
		$this->add_control('overlay',array('label'=>'Overlay','type'=>\Elementor\Controls_Manager::SWITCHER));
		$this->end_controls_section();
	}
	protected function render() {
		$s=$this->get_settings_for_display();$id=$this->project_id($s);if(!$id)return;$url=get_the_post_thumbnail_url($id,$s['image_size']??'full');if(!$url)return;echo '<div class="pss-project-image'.(!empty($s['overlay'])?' pss-project-image--overlay':'').'"><img src="'.esc_url($url).'" alt="'.esc_attr(get_the_title($id)).'">'.(!empty($s['overlay'])?'<span></span>':'').'</div>';
	}
}
