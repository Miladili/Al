<?php
namespace PSS\Elementor\Widgets;
class Project_Meta extends Base {
	public function get_name() { return 'pss_project_meta'; }
	public function get_title() { return 'Project Info'; }
	public function get_icon() { return 'eicon-post-info'; }
	protected function register_controls() {
		$this->add_source_controls();
		$this->start_controls_section( 'content', array( 'label' => 'Content' ) );
		$this->add_control( 'layout', array( 'label' => 'Layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'inline', 'options' => array( 'inline'=>'Inline', 'stacked'=>'Stacked', 'grid'=>'Grid' ) ) );
		$this->add_control( 'fields', array( 'label'=>'Fields', 'type'=>\Elementor\Controls_Manager::SELECT2, 'multiple'=>true, 'default'=>array('type','location','year','area','style'), 'options'=>array('type'=>'Project Type','location'=>'Location','year'=>'Year','area'=>'Area','style'=>'Style','designer'=>'Designer','architect'=>'Architect','client'=>'Client','duration'=>'Duration','services'=>'Services','materials'=>'Materials','colors'=>'Colors','features'=>'Features') ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s=$this->get_settings_for_display(); $id=$this->project_id($s); if(!$id) return; $fields=$s['fields']??array();
		$values=array('type'=>\PSS\get_project_taxonomy_value($id,'pss_project_type'),'location'=>\PSS\get_project_taxonomy_value($id,'pss_project_location'),'year'=>\PSS\get_meta($id,'_pss_year'),'area'=>\PSS\get_meta($id,'_pss_area'),'style'=>\PSS\get_project_taxonomy_value($id,'pss_project_style'),'designer'=>\PSS\get_meta($id,'_pss_designer'),'architect'=>\PSS\get_meta($id,'_pss_architect'),'client'=>\PSS\get_meta($id,'_pss_client'),'duration'=>\PSS\get_meta($id,'_pss_duration'),'services'=>\PSS\get_meta($id,'_pss_services'),'materials'=>\PSS\get_meta($id,'_pss_materials'),'colors'=>\PSS\get_meta($id,'_pss_colors'),'features'=>\PSS\get_meta($id,'_pss_features'));
		$labels=array('type'=>'Type','location'=>'Location','year'=>'Year','area'=>'Area','style'=>'Style','designer'=>'Designer','architect'=>'Architect','client'=>'Client','duration'=>'Duration','services'=>'Services','materials'=>'Materials','colors'=>'Colors','features'=>'Features');
		echo '<div class="pss-project-meta pss-project-meta--' . esc_attr($s['layout']) . '">'; foreach($fields as $key){if(empty($values[$key]))continue; echo '<div class="pss-meta-item"><span>' . esc_html($labels[$key]) . '</span><strong>' . esc_html($values[$key]) . '</strong></div>';} echo '</div>';
	}
}
