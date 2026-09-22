jQuery(function($){
  $('.pss-tab-link').on('click',function(){var tab=$(this).data('tab');$('.pss-tab-link').removeClass('is-active');$(this).addClass('is-active');$('.pss-tab').removeClass('is-active');$('.pss-tab[data-tab="'+tab+'"]').addClass('is-active');});
  function chooseMedia(target,multiple){var frame=wp.media({title:PSSAdmin.mediaTitle,multiple:multiple,library:{type:'image'}});frame.on('select',function(){var ids=[];frame.state().get('selection').each(function(att){ids.push(att.id);});$(target).val(ids.join(','));if(!multiple){$(target).trigger('change');}});frame.open();}
  $(document).on('click','.pss-media-button',function(){chooseMedia($(this).data('target'),true);});
  $(document).on('click','.pss-single-media',function(){chooseMedia($(this).siblings('input[type=hidden]'),false);});
  $(document).on('click','.pss-gallery-media',function(){chooseMedia($(this).siblings('input[type=hidden]'),true);});
});
