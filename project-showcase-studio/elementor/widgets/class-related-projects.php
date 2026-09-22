<?php
namespace PSS\Elementor\Widgets;
class Related_Projects extends Base {
	public function get_name() { return 'pss_related_projects'; }
	public function get_title() { return 'Related Projects'; }
	public function get_icon() { return 'eicon-posts-grid'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section('content',array('label'=>'Projects'));
		$this->add_control('limit',array('label'=>'Limit','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>1,'max'=>12,'default'=>3));
		$this->add_control('layout',array('label'=>'Layout','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'cards','options'=>array('cards'=>'Cards','masonry'=>'Masonry')));
		$this->end_controls_section();
	}
	protected function render() {
		$s=$this->get_settings_for_display(); $id=$this->project_id($s); if(!$id)return; $terms=array(); foreach(array('pss_project_category','pss_project_style','pss_project_type') as $tax){$t=wp_get_post_terms($id,$tax,array('fields'=>'ids')); if($t)$terms[$tax]=$t;}
		$args=array('post_type'=>\PSS_PROJECT_CPT,'post_status'=>'publish','posts_per_page'=>absint($s['limit']??3),'post__not_in'=>array($id)); if($terms){$taxq=array('relation'=>'OR'); foreach($terms as $tax=>$ids)$taxq[]=array('taxonomy'=>$tax,'field'=>'term_id','terms'=>$ids); $args['tax_query']=$taxq;}
		$posts=get_posts($args); $settings=array('preset'=>'modern','animation'=>'reveal'); echo '<div class="pss-related-grid pss-related-grid--'.esc_attr($s['layout']).'">'.\PSS\RenderCards::cards($posts,$settings).'</div>';
	}
}
