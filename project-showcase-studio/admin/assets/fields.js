(function(){
  'use strict';

  const types = window.PSSFieldTypes || {
    text:'Text', textarea:'Textarea', wysiwyg:'WYSIWYG', number:'Number', date:'Date', url:'URL', color:'Color',
    image:'Image', gallery:'Gallery', file:'File', video:'Video', map:'Map / Location',
    select:'Select', multi_select:'Multi Select', toggle:'Toggle', repeater:'Repeater',
    group:'Group', table:'Table', icon_value:'Icon + Title + Value'
  };

  const esc = (v) => String(v == null ? '' : v).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
  const json = (v) => { try { return JSON.stringify(v == null ? '' : v); } catch(e) { return '""'; } };
  const projectLibrary = () => window.PSSProjectFieldLibrary || {};
  const optionHtml = (selected) => Object.keys(types).map(k => `<option value="${esc(k)}" ${k===selected?'selected':''}>${esc(types[k])}</option>`).join('');

  function bindMedia(root){
    if(!root || !window.wp || !wp.media) return;
    root.querySelectorAll('.pss-single-media').forEach(btn=>{
      if(btn.dataset.bound) return; btn.dataset.bound='1';
      btn.addEventListener('click',e=>{
        e.preventDefault();
        const input=btn.parentNode.querySelector('.pss-media-id');
        const frame=wp.media({title:'Choose image',button:{text:'Use image'},multiple:false,library:{type:'image'}});
        frame.on('select',()=>{
          const a=frame.state().get('selection').first().toJSON();
          if(input) input.value=a.id;
          const cur=btn.parentNode.querySelector('.pss-media-current'); if(cur) cur.textContent='ID '+a.id;
          let img=btn.parentNode.querySelector('.pss-media-thumb');
          if(!img){ img=document.createElement('img'); img.className='pss-media-thumb'; btn.parentNode.appendChild(img); }
          img.src=a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
        });
        frame.open();
      });
    });
    root.querySelectorAll('.pss-gallery-media,.pss-media-button').forEach(btn=>{
      if(btn.dataset.bound) return; btn.dataset.bound='1';
      btn.addEventListener('click',e=>{
        e.preventDefault();
        const selector=btn.dataset.target?document.querySelector(btn.dataset.target):btn.parentNode.querySelector('.pss-media-id');
        const frame=wp.media({title:'Choose images',button:{text:'Use images'},multiple:true,library:{type:'image'}});
        frame.on('select',()=>{
          const ids=frame.state().get('selection').map(a=>a.toJSON().id);
          if(selector) selector.value=ids.join(',');
          const cur=btn.parentNode.querySelector('.pss-media-current'); if(cur) cur.textContent=ids.length+' image(s)';
        });
        frame.open();
      });
    });
    root.querySelectorAll('.pss-file-media').forEach(btn=>{
      if(btn.dataset.bound) return; btn.dataset.bound='1';
      btn.addEventListener('click',e=>{
        e.preventDefault();
        const input=btn.parentNode.querySelector('.pss-media-id');
        const frame=wp.media({title:'Choose file',button:{text:'Use file'},multiple:false});
        frame.on('select',()=>{ const a=frame.state().get('selection').first().toJSON(); if(input) input.value=a.id; const cur=btn.parentNode.querySelector('.pss-media-current'); if(cur) cur.textContent=a.filename||('ID '+a.id); });
        frame.open();
      });
    });
  }

  function cloneIndexed(template, index){
    const node=template.cloneNode(true);
    node.classList.remove('pss-template','pss-table-template');
    node.removeAttribute('aria-hidden');
    node.style.display='';
    node.querySelectorAll('[name]').forEach(el=>{ el.name=el.name.replace(/__INDEX__/g, String(index)); });
    return node;
  }

  function nextIndex(list, sel){ return list.querySelectorAll(sel).length; }

  function extraRow(idx, def, token){
    const label=def?.label||'New field';
    const key=(def?.record_key||def?.key||('custom_'+idx)).toString().replace(/[^a-z0-9_]+/gi,'_').toLowerCase();
    const type=def?.type||'text';
    const options=(def?.options||[]).join('\n');
    const sub=json(def?.subfields||[]);
    const src=token||'custom';
    let control='';
    if(type==='textarea'||type==='wysiwyg') control=`<textarea class="widefat" name="pss_local_field[${esc(key)}]" rows="${type==='wysiwyg'?8:4}"></textarea>`;
    else if(type==='number') control=`<input type="number" step="any" class="widefat" name="pss_local_field[${esc(key)}]" value="">`;
    else if(type==='date') control=`<input type="date" class="widefat" name="pss_local_field[${esc(key)}]" value="">`;
    else if(type==='url'||type==='video') control=`<input type="url" class="widefat" name="pss_local_field[${esc(key)}]" placeholder="https://">`;
    else if(type==='color') control=`<input type="color" name="pss_local_field[${esc(key)}]" value="#111111">`;
    else if(type==='toggle') control=`<label class="pss-switch"><input type="hidden" name="pss_local_field[${esc(key)}]" value="0"><input type="checkbox" name="pss_local_field[${esc(key)}]" value="1"><span>Enabled</span></label>`;
    else if(type==='select') control=`<select class="widefat" name="pss_local_field[${esc(key)}]"><option value="">— Select —</option>${(def?.options||[]).map(o=>`<option value="${esc(o)}">${esc(o)}</option>`).join('')}</select>`;
    else if(type==='image') control=`<div class="pss-media-field"><input type="hidden" class="pss-media-id" name="pss_local_field[${esc(key)}]" value=""><button type="button" class="button pss-single-media">Choose Image</button><span class="pss-media-current"></span></div>`;
    else if(type==='gallery') control=`<div class="pss-media-field"><input type="hidden" class="pss-media-id" name="pss_local_field[${esc(key)}]" value=""><button type="button" class="button pss-gallery-media">Choose Gallery</button><span class="pss-media-current"></span></div>`;
    else if(type==='file') control=`<div class="pss-media-field"><input type="hidden" class="pss-media-id" name="pss_local_field[${esc(key)}]" value=""><button type="button" class="button pss-file-media">Choose file</button><span class="pss-media-current"></span></div>`;
    else if(type==='map') control=`<div class="pss-map-field"><input type="text" class="widefat" name="pss_local_field[${esc(key)}][address]" placeholder="Address"><div class="pss-grid-3" style="margin-top:8px"><input type="text" name="pss_local_field[${esc(key)}][lat]" placeholder="Lat"><input type="text" name="pss_local_field[${esc(key)}][lng]" placeholder="Lng"></div></div>`;
    else control=`<input type="text" class="widefat" name="pss_local_field[${esc(key)}]" value="">`;
    const wrap=document.createElement('div');
    wrap.className='pss-value-row pss-value-row--extra';
    wrap.dataset.index=String(idx);
    wrap.innerHTML=`<input type="hidden" name="pss_local_defs[${idx}][source]" value="${esc(src)}"><input type="hidden" name="pss_local_defs[${idx}][label]" value="${esc(label)}"><input type="hidden" name="pss_local_defs[${idx}][key]" value="${esc(key)}"><input type="hidden" name="pss_local_defs[${idx}][type]" class="pss-local-field-type" value="${esc(type)}"><input type="hidden" name="pss_local_defs[${idx}][options]" class="pss-local-options" value="${esc(options)}"><input type="hidden" class="pss-local-subfields-json" name="pss_local_defs[${idx}][subfields_json]" value='${esc(sub)}'><label class="pss-value-row__label"><span>${esc(label)}</span><button type="button" class="button-link-delete pss-remove-local-field">Remove</button></label><div class="pss-local-field-value pss-value-row__control">${control}</div>`;
    return wrap;
  }

  function updateEmpty(){
    const list=document.getElementById('pss-local-fields-list');
    const empty=document.getElementById('pss-local-field-empty');
    const count=list?list.querySelectorAll('.pss-value-row--extra').length:0;
    if(empty) empty.style.display=count?'none':'';
  }

  function nextExtraIndex(){
    const list=document.getElementById('pss-local-fields-list');
    if(!list) return 0;
    return [...list.querySelectorAll('.pss-value-row--extra')].reduce((m,r)=>Math.max(m, parseInt(r.dataset.index||'0',10)||0),-1)+1;
  }

  function applyVisibility(){
    const values={};
    document.querySelectorAll('.pss-value-row[data-field-key]').forEach(row=>{
      const key=row.getAttribute('data-field-key');
      const input=row.querySelector('[name^="pss_field["]');
      values[key]=input ? (input.type==='checkbox'?(input.checked?'1':''):input.value) : '';
    });
    document.querySelectorAll('.pss-value-row[data-vis-enabled="1"]').forEach(row=>{
      const field=row.getAttribute('data-vis-field');
      const op=row.getAttribute('data-vis-op')||'equals';
      const expected=row.getAttribute('data-vis-value')||'';
      const actual=values[field]||'';
      let show=true;
      if(op==='empty') show=!actual;
      else if(op==='not_empty') show=!!actual;
      else if(op==='contains') show=actual.indexOf(expected)!==-1;
      else if(op==='not_contains') show=actual.indexOf(expected)===-1;
      else if(op==='not_equals') show=actual!==expected;
      else show=actual===expected;
      row.style.display=show?'':'none';
    });
  }

  function cardPicker(){
    const list=document.getElementById('pss-card-field-picker'), select=document.getElementById('pss-card-field-add'), add=document.getElementById('pss-add-card-field');
    if(!list||!select||!add||add.dataset.bound==='1') return; add.dataset.bound='1';
    add.addEventListener('click',()=>{
      const opt=select.options[select.selectedIndex]; if(!opt||!opt.value) return;
      const row=document.createElement('div'); row.className='pss-card-field-row';
      row.innerHTML=`<input type="hidden" name="pss_card_fields[]" value="${esc(opt.value)}"><span class="pss-drag-handle">⋮⋮</span><span class="pss-card-field-name">${esc(opt.text.split(' — ')[0])}</span><span class="pss-card-field-key">${esc(opt.value)}</span><button type="button" class="button-link-delete pss-card-remove">Remove</button>`;
      list.appendChild(row); opt.remove();
    });
  }

  function bindProjectEditor(){
    const list=document.getElementById('pss-local-fields-list');
    document.getElementById('pss-add-library-record')?.addEventListener('click',e=>{
      e.preventDefault();
      const select=document.getElementById('pss-quick-record-source');
      const token=select?.value||''; if(!token) return;
      const def=projectLibrary()[token];
      const row=extraRow(nextExtraIndex(), def, token);
      list?.appendChild(row); bindMedia(row); updateEmpty();
      const opt=select.options[select.selectedIndex]; opt?.remove(); select.selectedIndex=0;
      row.scrollIntoView({behavior:'smooth',block:'center'});
    });
    const dialog=document.getElementById('pss-create-field-dialog');
    document.getElementById('pss-add-local-field')?.addEventListener('click',e=>{ e.preventDefault(); if(dialog) dialog.hidden=!dialog.hidden; });
    document.getElementById('pss-create-field-cancel')?.addEventListener('click',e=>{ e.preventDefault(); if(dialog) dialog.hidden=true; });
    document.getElementById('pss-create-field-confirm')?.addEventListener('click',e=>{
      e.preventDefault();
      const label=document.getElementById('pss-new-label')?.value||'';
      let key=document.getElementById('pss-new-key')?.value||'';
      const type=document.getElementById('pss-new-type')?.value||'text';
      const options=(document.getElementById('pss-new-options')?.value||'').split(/\r?\n/).map(v=>v.trim()).filter(Boolean);
      if(!label) return;
      if(!key) key=label.toLowerCase().replace(/[^a-z0-9]+/g,'_');
      const row=extraRow(nextExtraIndex(), {label, key, record_key:key, type, options, subfields:[]}, 'custom');
      list?.appendChild(row); bindMedia(row); updateEmpty(); if(dialog) dialog.hidden=true;
      ['pss-new-label','pss-new-key','pss-new-options'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    });
    document.querySelector('.pss-cms-form')?.addEventListener('input', applyVisibility);
    document.querySelector('.pss-cms-form')?.addEventListener('change', applyVisibility);
    applyVisibility(); cardPicker(); bindMedia(document); updateEmpty();
  }

  document.addEventListener('click', function(e){
    if(e.target.classList.contains('pss-remove-local-field')){
      e.preventDefault(); e.target.closest('.pss-value-row--extra,.pss-local-field-builder')?.remove(); updateEmpty();
    }
    if(e.target.classList.contains('pss-add-repeater')){
      e.preventDefault();
      const wrap=e.target.closest('.pss-repeater'); const parent=wrap?.querySelector('.pss-repeater-list'); const tpl=wrap?.querySelector('.pss-template');
      if(parent && tpl){ parent.appendChild(cloneIndexed(tpl, parent.querySelectorAll('.pss-repeater-row:not(.pss-template)').length)); bindMedia(parent); }
    }
    if(e.target.classList.contains('pss-remove-repeater')){ e.preventDefault(); e.target.closest('.pss-repeater-row')?.remove(); }
    if(e.target.classList.contains('pss-add-table-row')){
      e.preventDefault();
      const wrap=e.target.closest('.pss-data-table'); const tbody=wrap?.querySelector('tbody'); const tpl=wrap?.querySelector('.pss-table-template');
      if(tbody && tpl) tbody.appendChild(cloneIndexed(tpl, tbody.querySelectorAll('tr:not(.pss-table-template)').length));
    }
    if(e.target.classList.contains('pss-remove-table-row')){ e.preventDefault(); e.target.closest('tr')?.remove(); }
    if(e.target.classList.contains('pss-add-icon-value')){
      e.preventDefault();
      const wrap=e.target.closest('.pss-icon-value-editor'); const list=wrap?.querySelector('.pss-icon-value-list'); const tpl=wrap?.querySelector('.pss-template');
      if(list && tpl) list.appendChild(cloneIndexed(tpl, list.querySelectorAll('.pss-icon-value-row:not(.pss-template)').length));
    }
    if(e.target.classList.contains('pss-remove-icon-value')){ e.preventDefault(); e.target.closest('.pss-icon-value-row')?.remove(); }
    if(e.target.classList.contains('pss-card-remove')){ e.preventDefault(); e.target.closest('.pss-card-field-row')?.remove(); }
    if(e.target.classList.contains('pss-visibility-enabled')){
      const box=e.target.closest('.pss-field-visibility')?.querySelector('.pss-visibility-rule');
      if(box) box.style.display=e.target.checked?'':'none';
    }
  });

  function fieldLibrarySubfieldRow(type,data){
    return `<div class="pss-subfield-row"><input type="text" class="pss-subfield-label" value="${esc(data?.label||'')}" placeholder="Label"><input type="text" class="pss-subfield-key" value="${esc(data?.key||'')}" placeholder="key">${type!=='table'?`<select class="pss-subfield-type">${optionHtml(data?.type||'text')}</select>`:''}<button type="button" class="button-link-delete pss-remove-subfield">Remove</button></div>`;
  }
  function syncDefinition(builder){
    const box=builder.querySelector('.pss-subfields-builder'); if(!box) return;
    const type=builder.querySelector('.pss-field-type')?.value||'text';
    const data=[...box.querySelectorAll('.pss-subfield-row')].map(r=>({label:r.querySelector('.pss-subfield-label')?.value||'',key:r.querySelector('.pss-subfield-key')?.value||'',type:type==='table'?'text':(r.querySelector('.pss-subfield-type')?.value||'text')})).filter(r=>r.label&&r.key);
    const hidden=builder.querySelector('.pss-subfields-json'); if(hidden) hidden.value=JSON.stringify(data);
  }
  function refreshDefinition(builder){
    const type=builder.querySelector('.pss-field-type')?.value||'text', area=builder.querySelector('.pss-field-type-options'); if(!area) return;
    let data=[]; try{data=JSON.parse(area.querySelector('.pss-subfields-json')?.value||'[]')||[]}catch(e){}
    let html='';
    if(type==='select'||type==='multi_select') html+=`<label>Options (one per line)<textarea name="fields[${builder.dataset.index}][options]" rows="4"></textarea></label>`;
    if(['repeater','group','table'].includes(type)){
      html+=`<div class="pss-subfields-builder"><div class="pss-subfields-builder__head"><strong>${type==='table'?'Table Columns':'Subfields'}</strong><button type="button" class="button pss-add-subfield">+ Add</button></div><input type="hidden" class="pss-subfields-json" name="fields[${builder.dataset.index}][subfields]" value="${esc(json(data))}"><div class="pss-subfields-list">${data.map(r=>fieldLibrarySubfieldRow(type,r)).join('')}</div></div>`;
    }
    if(type==='icon_value') html+='<p class="description">This field stores repeatable Icon + Title + Value rows.</p>';
    area.innerHTML=html;
  }
  function bindFieldLibraryPage(){
    const list=document.getElementById('pss-field-list'); if(!list) return;
    document.querySelectorAll('.pss-field-builder').forEach(b=>{ b.querySelector('.pss-field-type')?.addEventListener('change',()=>refreshDefinition(b)); });
    document.getElementById('pss-add-field')?.addEventListener('click',()=>{
      const idx=list.children.length; const d=document.createElement('div'); d.className='pss-field-builder'; d.dataset.index=idx;
      d.innerHTML=`<div class="pss-field-builder__head"><strong>Field</strong><button type="button" class="button-link-delete pss-remove-field">Remove</button></div><div class="pss-grid-3"><label>Label<input type="text" name="fields[${idx}][label]"></label><label>Key<input type="text" name="fields[${idx}][key]"></label><label>Type<select class="pss-field-type" name="fields[${idx}][type]">${optionHtml('text')}</select></label></div><label>Description<input type="text" name="fields[${idx}][description]"></label><label><input type="checkbox" name="fields[${idx}][required]" value="1"> Required</label><div class="pss-field-type-options"></div>`;
      list.appendChild(d); d.querySelector('.pss-field-type')?.addEventListener('change',()=>refreshDefinition(d));
    });
    const search=document.getElementById('pss-field-library-search');
    search?.addEventListener('input',()=>{
      const term=(search.value||'').toLowerCase().trim();
      list.querySelectorAll('.pss-field-builder').forEach(row=>{ row.style.display=!term||(row.textContent||'').toLowerCase().includes(term)?'':'none'; });
    });
    list.addEventListener('click',e=>{
      if(e.target.classList.contains('pss-remove-field')) e.target.closest('.pss-field-builder')?.remove();
      if(e.target.classList.contains('pss-add-subfield')){
        const b=e.target.closest('.pss-field-builder'); const box=b?.querySelector('.pss-subfields-builder'); const type=b?.querySelector('.pss-field-type')?.value||'text';
        box?.querySelector('.pss-subfields-list')?.insertAdjacentHTML('beforeend', fieldLibrarySubfieldRow(type)); syncDefinition(b);
      }
      if(e.target.classList.contains('pss-remove-subfield')){ e.target.closest('.pss-subfield-row')?.remove(); const b=e.target.closest('.pss-field-builder'); if(b) syncDefinition(b); }
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    try { bindProjectEditor(); } catch (err) { if (window.console) console.error('[PSS] project editor', err); }
    try { bindFieldLibraryPage(); } catch (err) { if (window.console) console.error('[PSS] field library', err); }
  });
})();
