(function(){
  function initBeforeAfter(){document.querySelectorAll('.pss-before-after input[type="range"]').forEach(function(input){if(input.dataset.pssBound)return;input.dataset.pssBound='1';var wrap=input.closest('.pss-before-after'),clip=wrap&&wrap.querySelector('.pss-before-after__clip');input.addEventListener('input',function(){if(clip)clip.style.width=this.value+'%';});});}
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
      function tick(){
        if(skip()){track.style.transform='';return;}
        var rect=root.getBoundingClientRect();
        var total=Math.max(1,root.offsetHeight-(pin.offsetHeight||window.innerHeight));
        var p=ease((-rect.top)/total,cfg.easing||'smooth');
        if(cfg.direction==='rtl')p=1-p;
        var x=-(root._pssTravel||0)*p;
        track.style.transform='translate3d('+x+'px,0,0)';
        root.style.setProperty('--pss-p',String(p));
        root.classList.toggle('is-active',p>0&&p<1);
      }
      layout();tick();
      window.addEventListener('resize',function(){layout();tick();},{passive:true});
      window.addEventListener('scroll',tick,{passive:true});
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
  function start(){initScroll();magnetic();}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
  document.addEventListener('pss:showcase-updated',magnetic);
})();
