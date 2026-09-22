<?php
namespace PSS\Elementor\Widgets;
class Project_Navigation extends Base {
	public function get_name() { return 'pss_project_navigation'; }
	public function get_title() { return 'Project Navigation'; }
	public function get_icon() { return 'eicon-post-navigation'; }
	protected function register_controls() { $this->add_source_controls(); }
	protected function render() {
		$s=$this->get_settings_for_display();$id=$this->project_id($s);if(!$id)return; $current=$GLOBALS['post']??null; $GLOBALS['post']=get_post($id); setup_postdata($GLOBALS['post']); $prev=get_previous_post(true);$next=get_next_post(true); wp_reset_postdata(); $GLOBALS['post']=$current; echo '<nav class="pss-project-nav">'; echo $prev?'<a href="'.esc_url(get_permalink($prev)).'"><span>Previous</span><strong>'.esc_html(get_the_title($prev)).'</strong></a>':'<span></span>'; echo '<a class="pss-project-nav__back" href="'.esc_url(get_post_type_archive_link(\PSS_PROJECT_CPT)).'">All Projects</a>'; echo $next?'<a href="'.esc_url(get_permalink($next)).'" class="pss-project-nav__next"><span>Next</span><strong>'.esc_html(get_the_title($next)).'</strong></a>':'<span></span>'; echo '</nav>';
	}
}
