<?php
namespace PSS\Elementor\Widgets;
class Project_Gallery extends Base {
	public function get_name() { return 'pss_project_gallery'; }
	public function get_title() { return 'Project Gallery'; }
	public function get_icon() { return 'eicon-gallery-grid'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section('content',array('label'=>'Gallery'));
		$this->add_control('layout',array('label'=>'Layout','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'masonry','options'=>array('grid'=>'Grid','masonry'=>'Masonry','featured'=>'Featured','strip'=>'Horizontal')));
		$this->add_responsive_control('columns',array('label'=>'Columns','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>1,'max'=>6,'default'=>3,'tablet_default'=>2,'mobile_default'=>1));
		$this->add_control('lightbox',array('label'=>'Lightbox','type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
		$this->end_controls_section();
	}
	protected function render() {
		$s=$this->get_settings_for_display(); $id=$this->project_id($s); if(!$id)return; $ids=\PSS\get_gallery_ids($id); if(!$ids)return;
		$cols=max(1,absint($s['columns']??3)); echo '<div class="pss-gallery pss-gallery--'.esc_attr($s['layout']).'" style="--pss-cols:'.$cols.'">'; foreach($ids as $image_id){$url=wp_get_attachment_image_url($image_id,'large'); if(!$url)continue; $full=wp_get_attachment_image_url($image_id,'full')?:$url; $tag='yes'===$s['lightbox']?'<a href="'.esc_url($full).'" class="pss-lightbox-link">':'<div>'; echo $tag . '<img loading="lazy" src="'.esc_url($url).'" alt="">' . ('yes'===$s['lightbox']?'</a>':'</div>');} echo '</div>';
	}
}
