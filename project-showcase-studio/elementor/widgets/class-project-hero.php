<?php
namespace PSS\Elementor\Widgets;

class Project_Hero extends Base {
    public function get_name() { return 'pss_project_hero'; }
    public function get_title() { return 'Project Hero'; }
    public function get_icon() { return 'eicon-cover-image'; }
    protected function register_controls() {
        $this->add_source_controls();
        $this->start_controls_section('content', array('label'=>'Hero','tab'=>\Elementor\Controls_Manager::TAB_CONTENT));
        $this->add_control('show_image', array('label'=>'Show image','type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
        $this->add_control('show_subtitle', array('label'=>'Show subtitle','type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
        $this->add_control('eyebrow', array('label'=>'Eyebrow','type'=>\Elementor\Controls_Manager::TEXT,'default'=>'Project'));
        $this->add_control('cta', array('label'=>'CTA text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>'View project'));
        $this->add_control('cta_link', array('label'=>'CTA link','type'=>\Elementor\Controls_Manager::HIDDEN,'default'=>''));
        $this->end_controls_section();
        $this->start_controls_section('style', array('label'=>'Style','tab'=>\Elementor\Controls_Manager::TAB_STYLE));
        $this->add_responsive_control('min_height', array('label'=>'Min height','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px','vh'),'range'=>array('px'=>array('min'=>240,'max'=>1000),'vh'=>array('min'=>30,'max'=>100)),'default'=>array('size'=>640,'unit'=>'px'),'selectors'=>array('{{WRAPPER}} .pss-project-hero'=>'min-height: {{SIZE}}{{UNIT}};')));
        $this->add_control('overlay', array('label'=>'Overlay','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>array('{{WRAPPER}} .pss-project-hero__veil'=>'background: linear-gradient(180deg, rgba(0,0,0,0) 25%, {{VALUE}} 100%);')));
        $this->end_controls_section();
    }
    protected function render() {
        $s=$this->get_settings_for_display();$id=$this->project_id($s);if(!$id)return;$image=get_the_post_thumbnail_url($id,'full');$subtitle=\PSS\get_meta($id,'_pss_subtitle');
        echo '<section class="pss-project-hero">';if($image&&!empty($s['show_image']))echo '<img class="pss-project-hero__image" src="'.esc_url($image).'" alt="'.esc_attr(get_the_title($id)).'">';echo '<span class="pss-project-hero__veil"></span><div class="pss-project-hero__content"><span class="pss-project-hero__eyebrow">'.esc_html($s['eyebrow']??'Project').'</span><h1>'.esc_html(get_the_title($id)).'</h1>';if(!empty($s['show_subtitle'])&&$subtitle)echo '<p>'.esc_html($subtitle).'</p>';if(!empty($s['cta']))echo '<a class="pss-project-hero__cta" href="'.esc_url(get_permalink($id)).'">'.esc_html($s['cta']).'<span>↗</span></a>';echo '</div></section>';
    }
}
