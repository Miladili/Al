(function(){
  function initBeforeAfter(){document.querySelectorAll('.pss-before-after input[type="range"]').forEach(function(input){if(input.dataset.pssBound)return;input.dataset.pssBound='1';var wrap=input.closest('.pss-before-after'),clip=wrap&&wrap.querySelector('.pss-before-after__clip');function apply(v){if(!wrap)return;wrap.style.setProperty('--pss-ba-pos',v+'%');if(clip){if(wrap.classList.contains('pss-before-after--vertical')){clip.style.height=v+'%';clip.style.width='100%';}else{clip.style.width=v+'%';clip.style.height='100%';}}}input.addEventListener('input',function(){apply(this.value);});apply(input.value);});}
  function lightbox(){document.querySelectorAll('.pss-lightbox-link').forEach(function(a){if(a.dataset.pssBound)return;a.dataset.pssBound='1';a.addEventListener('click',function(e){e.preventDefault();var overlay=document.createElement('div');overlay.className='pss-lightbox-overlay';overlay.innerHTML='<button aria-label="Close">×</button><img src="'+a.href+'" alt="">';document.body.appendChild(overlay);overlay.addEventListener('click',function(ev){if(ev.target===overlay||ev.target.tagName==='BUTTON')overlay.remove();});});});}
  function requestShowcase(wrap,append){if(!window.PSSFront)return;var base=JSON.parse(wrap.dataset.settings||'{}');var page=append?((parseInt(wrap.dataset.page||'1',10))+1):1;base.page=page;var search=wrap.querySelector('.pss-filter-search');if(search)base.search=search.value;wrap.querySelectorAll('[data-pss-filter]').forEach(function(s){base[s.getAttribute('data-pss-filter')]=s.value;});var form=new URLSearchParams();form.append('action','pss_filter_projects');form.append('nonce',PSSFront.nonce);form.append('settings',JSON.stringify(base));var button=wrap.querySelector('.pss-load-more');if(button)button.disabled=true;fetch(PSSFront.ajaxUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:form.toString()}).then(function(r){return r.text();}).then(function(html){var grid=wrap.querySelector('.pss-showcase__grid');if(!grid)return;if(append){grid.insertAdjacentHTML('beforeend',html);wrap.dataset.page=String(page);}else{grid.innerHTML=html;wrap.dataset.page='1';}if(button)button.disabled=false;lightbox();motion();});}
  function filters(){document.querySelectorAll('.pss-showcase').forEach(function(wrap){wrap.querySelectorAll('[data-pss-filter]').forEach(function(s){s.addEventListener('change',function(){requestShowcase(wrap,false);});});var search=wrap.querySelector('.pss-filter-search');if(search)search.addEventListener('input',function(){clearTimeout(search._t);search._t=setTimeout(function(){requestShowcase(wrap,false);},350);});var button=wrap.querySelector('.pss-load-more');if(button)button.addEventListener('click',function(){requestShowcase(wrap,true);});});}
  function motion(){document.querySelectorAll('.pss-card--anim-parallax').forEach(function(card){if(card.dataset.pssMotionBound)return;card.dataset.pssMotionBound='1';var img=card.querySelector('.pss-card__media img');if(!img)return;card.addEventListener('pointermove',function(e){if(window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;var r=card.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;img.style.transform='translate('+(-x*7)+'px,'+(-y*7)+'px) scale(1.045)';});card.addEventListener('pointerleave',function(){img.style.transform='';});});}
document.addEventListener('DOMContentLoaded',function(){initBeforeAfter();lightbox();filters();motion();});
})();
(function(){
  function revealMasks(){document.querySelectorAll('.pss-card--anim-mask').forEach(function(card){if(card.dataset.pssMask==='1')return;card.dataset.pssMask='1';if('IntersectionObserver' in window){var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){card.classList.add('is-revealed');io.unobserve(card);}});},{threshold:.14});io.observe(card);}else card.classList.add('is-revealed');});}
  function tilt(){document.querySelectorAll('.pss-card--anim-tilt').forEach(function(card){if(card.dataset.pssTilt==='1')return;card.dataset.pssTilt='1';var link=card.querySelector('.pss-card__link');if(!link)return;card.addEventListener('pointermove',function(e){if(window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;var r=card.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;link.style.transform='perspective(900px) rotateX('+(-y*5)+'deg) rotateY('+(x*6)+'deg) translateY(-3px)';});card.addEventListener('pointerleave',function(){link.style.transform='';});});}
  document.addEventListener('DOMContentLoaded',function(){revealMasks();tilt();});
})();

(function(){
  function initProductCards(){
    document.querySelectorAll('.pss-product-card[data-transition-speed]').forEach(function(card){
      var media=card.querySelector('.pss-product-card__media');
      if(!media || card.dataset.pssProductInit==='1') return;
      card.dataset.pssProductInit='1';
      var slides=Array.prototype.slice.call(media.querySelectorAll('.pss-product-card__slide'));
      if(!slides.length) return;
      var dots=Array.prototype.slice.call(media.querySelectorAll('.pss-product-card__dots span'));
      var mode=(card.className.match(/pss-product-card--transition-([a-z_]+)/)||[])[1]||'hover';
      var speed=Math.max(150,parseInt(card.getAttribute('data-transition-speed')||'550',10));
      var index=0, timer=null;
      function show(next){
        index=(next+slides.length)%slides.length;
        slides.forEach(function(img,i){img.classList.toggle('is-active',i===index);img.setAttribute('aria-hidden',i===index?'false':'true');});
        dots.forEach(function(dot,i){dot.classList.toggle('is-active',i===index);});
      }
      function start(){if(timer||slides.length<2)return;timer=setInterval(function(){show(index+1);},Math.max(1200,speed*3));}
      function stop(){if(timer){clearInterval(timer);timer=null;}}
      dots.forEach(function(dot,i){dot.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();show(i);});});
      if(mode==='auto' || mode==='hover_or_auto') start();
      card.addEventListener('mouseenter',function(){if(mode==='hover'||mode==='hover_or_auto'){show(1);if(mode==='hover_or_auto')stop();}});
      card.addEventListener('mouseleave',function(){if(mode==='hover'){show(0);}else if(mode==='hover_or_auto'){stop();start();}});
      show(0);
    });
  }
  function start(){initProductCards();}
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',start); else start();
  document.addEventListener('pss:showcase-updated',start);
})();
(function(){
  function revealProjectBlocks(){
    var nodes=document.querySelectorAll('.pss-project-specs--anim-soft,.pss-project-specs--anim-up,.pss-project-tags--anim-soft,.pss-project-features');
    if(!nodes.length)return;
    var show=function(el){el.classList.add('is-visible');};
    if(!('IntersectionObserver' in window)){nodes.forEach(show);return;}
    var io=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(entry.isIntersecting){show(entry.target);io.unobserve(entry.target);}});},{threshold:.12});
    nodes.forEach(function(n){io.observe(n);});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',revealProjectBlocks);else revealProjectBlocks();
})();
(function(){
  function reduced(){return window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;}
  function mobile(){return window.matchMedia&&window.matchMedia('(max-width:767px)').matches;}
  function ease(t,type){
    t=Math.min(1,Math.max(0,t));
    if(type==='linear')return t;
    if(type==='cinematic')return t<.5?8*t*t*t*t:1-Math.pow(-2*t+2,4)/2;
    return t<.5?4*t*t*t:1-Math.pow(-2*t+2,3)/2;
  }
  function initScroll(){
    document.querySelectorAll('[data-pss-scroll]').forEach(function(root){
      if(root.dataset.pssScrollBound==='1')return;
      root.dataset.pssScrollBound='1';
      var cfg={};
      try{cfg=JSON.parse(root.getAttribute('data-pss-scroll')||'{}');}catch(e){cfg={};}
      var pin=root.querySelector('.pss-scroll__pin');
      var track=root.querySelector('.pss-scroll__track');
      var viewport=root.querySelector('.pss-scroll__viewport');
      if(!pin||!track||!viewport)return;
      if(window.gsap&&window.ScrollTrigger)return;
      function skip(){
        return (cfg.reduced!==false&&reduced())||(cfg.mobile==='stack'&&mobile())||(cfg.mobile==='swipe'&&mobile());
      }
      function layout(){
        if(skip()){root.style.height='';track.style.transform='';return;}
        var travel=Math.max(0,track.scrollWidth-viewport.clientWidth);
        var pinH=pin.offsetHeight||window.innerHeight;
        var extra=(Math.max(80,parseInt(cfg.distance||180,10))/100)*window.innerHeight;
        var pinMul=Math.max(.6,parseFloat(cfg.pin||1));
        var speed=Math.max(.4,parseFloat(cfg.speed||1));
        root._pssTravel=travel;
        root.style.height=Math.round(pinH+(travel*pinMul/speed)+extra)+'px';
      }
      var currentP=0, raf=0;
      function targetP(){
        var rect=root.getBoundingClientRect();
        var total=Math.max(1,root.offsetHeight-(pin.offsetHeight||window.innerHeight));
        var p=ease((-rect.top)/total,cfg.easing||'smooth');
        if(cfg.direction==='rtl')p=1-p;
        if(cfg.snap){
          var panels=Math.max(1,(track.children||[]).length-1);
          p=Math.round(p*panels)/panels;
        }
        return Math.min(1,Math.max(0,p));
      }
      function apply(p){
        var x=-(root._pssTravel||0)*p;
        track.style.transform='translate3d('+x+'px,0,0)';
        root.style.setProperty('--pss-p',String(p));
        root.classList.toggle('is-active',p>0&&p<1);
        var panels=track.querySelectorAll('.pss-scroll__panel');
        panels.forEach(function(panel,i){
          var n=Math.max(1,panels.length-1);
          var dist=Math.abs((i/n)-p);
          panel.style.setProperty('--pss-panel-p',String(1-Math.min(1,dist*1.6)));
        });
      }
      function tick(){
        if(skip()){track.style.transform='';return;}
        var goal=targetP();
        var scrub=Math.max(0,Math.min(.6,parseFloat(cfg.scrub||0)));
        if(!scrub){currentP=goal;apply(currentP);return;}
        currentP+=(goal-currentP)*(1-scrub);
        if(Math.abs(goal-currentP)<0.001)currentP=goal;
        apply(currentP);
        if(currentP!==goal){cancelAnimationFrame(raf);raf=requestAnimationFrame(tick);}
      }
      layout();tick();
      window.addEventListener('resize',function(){layout();tick();},{passive:true});
      window.addEventListener('scroll',function(){cancelAnimationFrame(raf);raf=requestAnimationFrame(tick);},{passive:true});
    });
  }
  function magnetic(){
    document.querySelectorAll('.pss-card--anim-magnetic').forEach(function(card){
      if(card.dataset.pssMag==='1')return;
      card.dataset.pssMag='1';
      var link=card.querySelector('.pss-card__link');
      if(!link)return;
      card.addEventListener('pointermove',function(e){
        if(reduced())return;
        var r=card.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
        link.style.transform='translate('+x*12+'px,'+y*10+'px)';
      });
      card.addEventListener('pointerleave',function(){link.style.transform='';});
    });
  }
  function start(){initScroll();magnetic();
    document.querySelectorAll('.pss-reveal').forEach(function(el){
      if(el.dataset.pssRev==='1')return;el.dataset.pssRev='1';
      if(!('IntersectionObserver' in window)){el.classList.add('is-visible');return;}
      var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){el.classList.add('is-visible');io.unobserve(el);}});},{threshold:.2});
      io.observe(el);
    });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
  document.addEventListener('pss:showcase-updated',magnetic);
})();

(function(){
  function cssNum(el, name, fallback){
    var v=parseFloat(window.getComputedStyle(el).getPropertyValue(name));
    return isNaN(v)?fallback:v;
  }
  function initSlider(root){
    if(root.dataset.pssSliderBound==='1')return;
    root.dataset.pssSliderBound='1';
    var cfg={};
    try{cfg=JSON.parse(root.getAttribute('data-pss-slider')||'{}');}catch(e){cfg={};}
    var viewport=root.querySelector('.pss-slider__viewport');
    var track=root.querySelector('.pss-slider__track');
    if(!viewport||!track)return;
    var slides=Array.prototype.slice.call(track.children).filter(function(n){return n.classList&&n.classList.contains('pss-card');});
    if(!slides.length)return;
    var index=0, timer=null, startX=0, startY=0, dragging=false, delta=0;
    var dotsWrap=root.querySelector('.pss-slider__dots');
    var progress=root.querySelector('.pss-slider__progress span');
    var vertical=cfg.direction==='vertical';
    var fade=cfg.transition==='fade';
    function perView(){return Math.max(1, cssNum(root,'--pss-slides', parseFloat(cfg.visible||1)));}
    function gap(){return cssNum(root,'--pss-gap', 18);}
    function maxIndex(){
      var vis=perView();
      return cfg.loop ? slides.length : Math.max(0, Math.ceil(slides.length-vis));
    }
    function buildDots(){
      if(!dotsWrap)return;
      dotsWrap.innerHTML='';
      slides.forEach(function(_,i){
        var b=document.createElement('button');
        b.type='button';
        b.className='pss-slider__dot'+(i===index?' is-active':'');
        b.setAttribute('aria-label','Go to slide '+(i+1));
        b.addEventListener('click',function(){go(i,true);});
        dotsWrap.appendChild(b);
      });
    }
    function apply(){
      slides.forEach(function(slide,i){slide.classList.toggle('is-active', i===index);});
      if(dotsWrap){
        Array.prototype.forEach.call(dotsWrap.children,function(d,i){d.classList.toggle('is-active', i===index);});
      }
      if(progress){progress.style.width=((index+1)/slides.length*100)+'%';}
      if(fade){track.style.transform='none';return;}
      var vis=perView();
      var g=gap();
      var size=(vertical?viewport.clientHeight:viewport.clientWidth);
      var step=(size - g*(vis-1))/vis + g;
      var offset=index*step;
      if(cfg.center){offset=index*step - (size-step)/2;}
      track.style.transform=vertical?('translate3d(0,'+(-offset)+'px,0)'):('translate3d('+(-offset)+'px,0,0)');
    }
    function go(i,restart){
      var max=maxIndex();
      if(cfg.loop){
        index=(i+slides.length)%slides.length;
      }else{
        index=Math.max(0, Math.min(max, i));
      }
      apply();
      if(restart) restartAuto();
    }
    function next(){go(index+1,false);}
    function prev(){go(index-1,false);}
    function stopAuto(){if(timer){clearInterval(timer);timer=null;}}
    function restartAuto(){
      stopAuto();
      if(!cfg.autoplay)return;
      timer=setInterval(next, Math.max(1200, parseInt(cfg.speed||4200,10)));
    }
    var prevBtn=root.querySelector('.pss-slider__arrow--prev');
    var nextBtn=root.querySelector('.pss-slider__arrow--next');
    if(prevBtn)prevBtn.addEventListener('click',function(){go(index-1,true);});
    if(nextBtn)nextBtn.addEventListener('click',function(){go(index+1,true);});
    if(cfg.keyboard){
      root.setAttribute('tabindex','0');
      root.addEventListener('keydown',function(e){
        if(e.key==='ArrowRight'||e.key==='ArrowDown'){e.preventDefault();go(index+1,true);}
        if(e.key==='ArrowLeft'||e.key==='ArrowUp'){e.preventDefault();go(index-1,true);}
      });
    }
    if(cfg.drag){
      var base=0;
      viewport.addEventListener('pointerdown',function(e){
        dragging=true;startX=e.clientX;startY=e.clientY;delta=0;stopAuto();
        track.style.transition='none';
        var vis=perView(), g=gap(), size=(vertical?viewport.clientHeight:viewport.clientWidth);
        var step=(size - g*(vis-1))/vis + g;
        base=-(index*step);
        if(cfg.center){base=-(index*step - (size-step)/2);}
        try{viewport.setPointerCapture(e.pointerId);}catch(err){}
      });
      viewport.addEventListener('pointermove',function(e){
        if(!dragging)return;
        delta=vertical?(e.clientY-startY):(e.clientX-startX);
        if(fade)return;
        var x=base+delta;
        track.style.transform=vertical?('translate3d(0,'+x+'px,0)'):('translate3d('+x+'px,0,0)');
      });
      function endDrag(){
        if(!dragging)return;
        dragging=false;
        track.style.transition='';
        if(Math.abs(delta)>40){delta<0?next():prev();}else{apply();}
        restartAuto();
      }
      viewport.addEventListener('pointerup',endDrag);
      viewport.addEventListener('pointercancel',endDrag);
    }
    if(cfg.wheel){
      var wlock=0;
      viewport.addEventListener('wheel',function(e){
        var d=vertical?e.deltaY:e.deltaX||e.deltaY;
        if(Math.abs(d)<8)return;
        e.preventDefault();
        if(Date.now()-wlock<420)return;
        wlock=Date.now();
        d>0?go(index+1,true):go(index-1,true);
      },{passive:false});
    }
    if(cfg.pause){
      root.addEventListener('mouseenter',stopAuto);
      root.addEventListener('mouseleave',restartAuto);
    }
    window.addEventListener('resize',function(){apply();},{passive:true});
    buildDots();
    apply();
    restartAuto();
  }
  function start(){document.querySelectorAll('[data-pss-slider]').forEach(initSlider);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
  document.addEventListener('pss:showcase-updated',start);
})();

(function(){
  function reduced(){return window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;}
  function follow(){
    document.querySelectorAll('[data-pss-follow]').forEach(function(el){
      if(el.dataset.pssFollowBound==='1')return;
      el.dataset.pssFollowBound='1';
      var host=el.closest('.pss-card')||el;
      host.addEventListener('pointermove',function(e){
        if(reduced())return;
        var r=host.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
        el.style.transform='translate('+x*16+'px,'+y*12+'px)';
      });
      host.addEventListener('pointerleave',function(){el.style.transform='';});
    });
  }
  function sticky(){
    document.querySelectorAll('[data-pss-sticky]').forEach(function(root){
      if(root.dataset.pssStickyBound==='1')return;
      root.dataset.pssStickyBound='1';
      var frames=root.querySelectorAll('.pss-sticky__frame');
      var steps=root.querySelectorAll('.pss-sticky__step');
      var bar=root.querySelector('.pss-sticky__progress span');
      if(!steps.length)return;
      function set(i){
        frames.forEach(function(img,n){img.classList.toggle('is-active',n===i);});
        steps.forEach(function(st,n){st.classList.toggle('is-active',n===i);});
        if(bar) bar.style.width=((i+1)/steps.length*100)+'%';
      }
      if('IntersectionObserver' in window){
        var io=new IntersectionObserver(function(es){
          es.forEach(function(e){if(e.isIntersecting){set(parseInt(e.target.getAttribute('data-step')||'0',10));}});
        },{threshold:.55});
        steps.forEach(function(st){io.observe(st);});
      }
      set(0);
      if(window.gsap&&window.ScrollTrigger){
        try{
          window.gsap.registerPlugin(window.ScrollTrigger);
          window.ScrollTrigger.create({
            trigger:root,
            start:'top top',
            end:'bottom bottom',
            onUpdate:function(self){if(bar) bar.style.width=(self.progress*100)+'%';}
          });
        }catch(err){}
      }
    });
  }
  function gsapScroll(){
    if(!window.gsap||!window.ScrollTrigger)return;
    try{window.gsap.registerPlugin(window.ScrollTrigger);}catch(e){return;}
    document.querySelectorAll('[data-pss-scroll]').forEach(function(root){
      if(root.dataset.pssGsap==='1')return;
      var pin=root.querySelector('.pss-scroll__pin');
      var track=root.querySelector('.pss-scroll__track');
      var viewport=root.querySelector('.pss-scroll__viewport');
      var bar=root.querySelector('.pss-scroll__progress span');
      if(!pin||!track||!viewport)return;
      var cfg={};try{cfg=JSON.parse(root.getAttribute('data-pss-scroll')||'{}');}catch(e){cfg={};}
      if((cfg.reduced!==false&&reduced())||(window.matchMedia&&window.matchMedia('(max-width:767px)').matches&&cfg.mobile==='stack'))return;
      root.dataset.pssGsap='1';
      var travel=Math.max(0,track.scrollWidth-viewport.clientWidth);
      if(!travel)return;
      var tween=window.gsap.to(track,{x:cfg.direction==='rtl'?travel:-travel,ease:'none',scrollTrigger:{
        trigger:root,pin:pin,scrub:cfg.scrub?parseFloat(cfg.scrub):true,end:function(){return '+='+(travel*Math.max(.6,parseFloat(cfg.pin||1))+((parseInt(cfg.distance||180,10)/100)*window.innerHeight));},
        snap:cfg.snap?(1/Math.max(1,(track.children.length||1)-1)):false,
        onUpdate:function(self){if(bar) bar.style.width=(self.progress*100)+'%'; root.style.setProperty('--pss-p',String(self.progress));}
      }});
      root._pssGsap=tween;
    });
  }
  function progressNative(){
    document.querySelectorAll('[data-pss-scroll]').forEach(function(root){
      var bar=root.querySelector('.pss-scroll__progress span');
      if(!bar||root.dataset.pssGsap==='1')return;
      var p=parseFloat(root.style.getPropertyValue('--pss-p')||'0');
      bar.style.width=(Math.min(1,Math.max(0,p))*100)+'%';
    });
  }
  function start(){follow();sticky();gsapScroll();progressNative();}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
  window.addEventListener('scroll',progressNative,{passive:true});
  document.addEventListener('pss:showcase-updated',follow);
})();
