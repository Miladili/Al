(function(){
  'use strict';

  const types = {
    text:'Text', textarea:'Textarea', number:'Number', date:'Date', url:'URL', image:'Image',
    gallery:'Gallery', select:'Select', multi_select:'Multi Select', toggle:'Toggle',
    repeater:'Repeater', group:'Group', table:'Table', icon_value:'Icon + Title + Value'
  };

  const esc = (v) => String(v == null ? '' : v).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
  const json = (v) => { try { return JSON.stringify(v == null ? '' : v); } catch(e) { return '""'; } };
  const projectLibrary = () => window.PSSProjectFieldLibrary || {};
  const globalFieldOptions = () => window.PSSFieldOptions || {};
  const optionHtml = (selected) => Object.keys(types).map(k => `<option value="${esc(k)}" ${k===selected?'selected':''}>${esc(types[k])}</option>`).join('');

  function libraryOptions(selected){
    let html = '<option value="custom">Create a new field for this project</option>';
    Object.keys(projectLibrary()).forEach(token => {
      const def = projectLibrary()[token] || {};
      html += `<option value="${esc(token)}" ${token===selected?'selected':''}>${esc(def.label||def.key||token)} — ${esc(def.type||'text')}</option>`;
    });
    return html;
  }

  function localFieldKey(row){
    const input = row.querySelector('input[name$="[key]"]');
    return (input?.value||'').trim().toLowerCase().replace(/[^a-z0-9_]+/g,'_').replace(/^_+|_+$/g,'') || ('new_'+(row.dataset.index||0));
  }
  function localFieldValueName(row){ return 'pss_local_field[' + localFieldKey(row) + ']'; }
  function localOptions(row){
    const ta = row.querySelector('.pss-local-options');
    return (ta?.value||'').split(/\r?\n/).map(v=>v.trim()).filter(Boolean);
  }
  function currentValue(row){
    try { return JSON.parse(row.dataset.currentValue || '""'); } catch(e) { return ''; }
  }
  function setCurrentValue(row, value){ row.dataset.currentValue = json(value); }

  function renderSimpleInput(name, type, value, extra){
    const v = value == null ? '' : value;
    if(type==='textarea') return `<textarea class="widefat" name="${esc(name)}" rows="4">${esc(v)}</textarea>`;
    if(type==='number') return `<input type="number" step="any" class="widefat" name="${esc(name)}" value="${esc(v)}">`;
    if(type==='date') return `<input type="date" class="widefat" name="${esc(name)}" value="${esc(v)}">`;
    if(type==='url') return `<input type="url" class="widefat" name="${esc(name)}" value="${esc(v)}">`;
    if(type==='toggle') return `<label class="pss-switch"><input type="hidden" name="${esc(name)}" value="0"><input type="checkbox" name="${esc(name)}" value="1" ${String(v)==='1' || v===true?'checked':''}><span>Enabled</span></label>`;
    if(type==='select') return `<select class="widefat" name="${esc(name)}"><option value="">— Select —</option>${(extra||[]).map(o=>`<option value="${esc(o)}" ${String(v)===String(o)?'selected':''}>${esc(o)}</option>`).join('')}</select>`;
    if(type==='multi_select') return `<select class="widefat" multiple size="5" name="${esc(name)}[]">${(extra||[]).map(o=>`<option value="${esc(o)}" ${Array.isArray(v)&&v.map(String).includes(String(o))?'selected':''}>${esc(o)}</option>`).join('')}</select>`;
    if(type==='image') return `<div class="pss-media-field"><input type="hidden" class="pss-media-id" name="${esc(name)}" value="${esc(v)}"><button type="button" class="button pss-single-media">Choose Image</button><span class="pss-media-current">${v?'ID '+esc(v):''}</span></div>`;
    if(type==='gallery') return `<div class="pss-media-field"><input type="hidden" class="pss-media-id" name="${esc(name)}" value="${esc(Array.isArray(v)?v.join(','):v)}"><button type="button" class="button pss-gallery-media">Choose Gallery</button><span class="pss-media-current">${v && (Array.isArray(v)?v.length:v)?'Selected':''}</span></div>`;
    return `<input type="text" class="widefat" name="${esc(name)}" value="${esc(v)}">`;
  }

  function subfieldRow(type, data){
    data = data || {};
    return `<div class="pss-local-subfield-row"><input class="pss-local-subfield-label" type="text" value="${esc(data.label||'')}" placeholder="Label"><input class="pss-local-subfield-key" type="text" value="${esc(data.key||'')}" placeholder="key">${type!=='table'?`<select class="pss-local-subfield-type">${optionHtml(data.type||'text')}</select>`:''}<button type="button" class="button-link-delete pss-remove-local-subfield">Remove</button></div>`;
  }

  function readLocalSubfields(row){
    const box = row.querySelector('.pss-local-structure');
    if(!box) return [];
    const type = row.querySelector('.pss-local-field-type')?.value || 'text';
    const data = [...box.querySelectorAll('.pss-local-subfield-row')].map(r=>({
      label:r.querySelector('.pss-local-subfield-label')?.value||'',
      key:r.querySelector('.pss-local-subfield-key')?.value||'',
      type:type==='table'?'text':(r.querySelector('.pss-local-subfield-type')?.value||'text')
    })).filter(r=>r.label&&r.key);
    const hidden = row.querySelector('.pss-local-subfields-json');
    if(hidden) hidden.value = JSON.stringify(data);
    return data;
  }

  function renderStructure(row){
    const area = row.querySelector('.pss-local-field-structure');
    if(!area) return;
    const type = row.querySelector('.pss-local-field-type')?.value || 'text';
    let data = [];
    try { data = JSON.parse(area.dataset.existingSubfields || row.querySelector('.pss-local-subfields-json')?.value || '[]') || []; } catch(e) {}
    let html = '';
    if(['repeater','group','table'].includes(type)){
      html = `<div class="pss-local-structure"><div class="pss-subfields-builder__head"><div><strong>${type==='table'?'Table columns':'Subfields'}</strong><span class="description">Define the fields stored inside this record.</span></div><button type="button" class="button pss-add-local-subfield">+ Add subfield</button></div><div class="pss-local-subfields-list">${data.map(r=>subfieldRow(type,r)).join('')}</div></div>`;
    } else if(type==='icon_value'){
      html = '<div class="pss-local-structure"><div class="pss-helper-chip">Each item has Icon, Title and Value. Add rows in the value editor below.</div></div>';
    }
    area.innerHTML = html;
    const hidden = row.querySelector('.pss-local-subfields-json');
    if(hidden) hidden.value = JSON.stringify(data);
  }

  function renderValueEditor(row){
    const wrap = row.querySelector('.pss-local-field-value'); if(!wrap) return;
    const type = row.querySelector('.pss-local-field-type')?.value || 'text';
    const name = localFieldValueName(row);
    const value = currentValue(row);
    let html = '<label>Value</label>';
    if(['repeater','group','table','icon_value'].includes(type)){
      let subfields=[]; try{subfields=JSON.parse(row.querySelector('.pss-local-subfields-json')?.value||'[]')||[]}catch(e){}
      if(type==='group'){
        html += '<div class="pss-structured-group-editor">';
        subfields.forEach(sf=>{
          const key = sf.key || '';
          html += `<div class="pss-structured-group-field"><label>${esc(sf.label||key)}</label>${renderSimpleInput(name+'['+esc(key)+']',sf.type||'text',value&&typeof value==='object'?value[key]:'',localOptions(row))}</div>`;
        });
        html += '</div>';
      } else if(type==='repeater'){
        const rows=Array.isArray(value)?value:[];
        html += `<div class="pss-repeater"><div class="pss-repeater-list">${rows.map((r,i)=>repeaterRowHtml(name,i,subfields,r)).join('')}</div><button type="button" class="button pss-add-repeater">+ Add item</button></div>`;
      } else if(type==='table'){
        const rows=Array.isArray(value)?value:[];
        html += `<div class="pss-data-table"><table><thead><tr>${subfields.map(sf=>`<th>${esc(sf.label||sf.key)}</th>`).join('')}<th></th></tr></thead><tbody>${rows.map((r,i)=>tableRowHtml(name,i,subfields,r)).join('')}</tbody></table><button type="button" class="button pss-add-table-row">+ Add row</button></div>`;
      } else {
        const rows=Array.isArray(value)?value:[];
        html += `<div class="pss-icon-value-editor"><div class="pss-icon-value-list">${rows.map((r,i)=>iconRowHtml(name,i,r)).join('')}</div><button type="button" class="button pss-add-icon-value">+ Add item</button></div>`;
      }
    } else {
      html += renderSimpleInput(name,type,value,localOptions(row));
    }
    wrap.innerHTML = html;
    bindMedia(wrap);
  }

  function repeaterRowHtml(name,index,subfields,row){
    row=row||{};
    return `<div class="pss-repeater-row"><div class="pss-repeater-row__head"><strong>Item ${index+1}</strong><button type="button" class="button-link-delete pss-remove-repeater">Remove</button></div><div class="pss-repeater-fields">${subfields.map(sf=>`<label>${esc(sf.label||sf.key)}${renderSimpleInput(name+'['+index+']['+esc(sf.key)+']',sf.type||'text',row[sf.key],[])}</label>`).join('')}</div></div>`;
  }
  function tableRowHtml(name,index,columns,row){
    row=row||{};
    return `<tr>${columns.map(col=>`<td><input type="text" class="widefat" name="${esc(name+'['+index+']['+(col.key||'')+']')}" value="${esc(row[col.key]||'')}"></td>`).join('')}<td><button type="button" class="button-link-delete pss-remove-table-row">Remove</button></td></tr>`;
  }
  function iconRowHtml(name,index,row){
    row=row||{};
    return `<div class="pss-icon-value-row"><input type="text" name="${esc(name+'['+index+'][icon]')}" value="${esc(row.icon||'')}" placeholder="Icon"><input type="text" name="${esc(name+'['+index+'][title]')}" value="${esc(row.title||'')}" placeholder="Title"><input type="text" name="${esc(name+'['+index+'][value]')}" value="${esc(row.value||'')}" placeholder="Value"><button type="button" class="button-link-delete pss-remove-icon-value">Remove</button></div>`;
  }

  function updateRecordSource(row, token){
    const def = token && projectLibrary()[token] ? projectLibrary()[token] : null;
    const label = row.querySelector('input[name*="[label]"]');
    const keyInput = row.querySelector('input[name*="[key]"]');
    const type = row.querySelector('.pss-local-field-type');
    const options = row.querySelector('.pss-local-options');
    const desc = row.querySelector('input[name*="[description]"]');
    const structure = row.querySelector('.pss-local-structure');
    if(def){
      row.dataset.source = def.source || 'global';
      if(label) label.value = def.label || '';
      if(keyInput) keyInput.value = def.record_key || def.key || '';
      if(type) type.value = def.type || 'text';
      if(options) options.value = (def.options||[]).join('\n');
      if(desc) desc.value = def.description || '';
      const hidden=row.querySelector('.pss-local-subfields-json'); if(hidden) hidden.value=JSON.stringify(def.subfields||[]);
      if(structure){structure.dataset.existingSubfields=JSON.stringify(def.subfields||[]);structure.dataset.existingOptions=(def.options||[]).join('\n');}
      setCurrentValue(row,'');
    } else {
      row.dataset.source='custom';
      if(label) label.value=''; if(keyInput) keyInput.value=''; if(type) type.value='text'; if(options) options.value=''; if(desc) desc.value='';
      const hidden=row.querySelector('.pss-local-subfields-json'); if(hidden) hidden.value='[]';
      if(structure){structure.dataset.existingSubfields='[]';structure.dataset.existingOptions='';}
      setCurrentValue(row,'');
    }
    renderStructure(row); syncLocalValueName(row); renderValueEditor(row);
  }

  function bindLibrarySource(row){
    const select=row.querySelector('.pss-local-field-source'); if(!select||select.dataset.bound==='1') return;
    select.dataset.bound='1'; select.addEventListener('change',()=>updateRecordSource(row,select.value));
  }

  function updateRecordCount(){
    const list=document.getElementById('pss-local-fields-list'); const pill=document.querySelector('.pss-field-editor-hero .pss-editor-pill');
    const count=list?list.querySelectorAll('.pss-local-field-builder').length:0;
    if(list&&pill) pill.textContent=count+' records';
    const empty=document.getElementById('pss-local-field-empty'); if(empty) empty.style.display=count?'none':'';
  }

  function nextRecordIndex(list){
    return [...list.querySelectorAll('.pss-local-field-builder')].reduce((max,row)=>Math.max(max,parseInt(row.dataset.index||'0',10)||0),-1)+1;
  }

  function addLocalField(sourceToken){
    const list=document.getElementById('pss-local-fields-list'); if(!list)return;
    const idx=nextRecordIndex(list);
    const def=sourceToken&&projectLibrary()[sourceToken]?projectLibrary()[sourceToken]:null;
    const row=document.createElement('div'); row.className='pss-local-field-builder'; row.draggable=true; row.dataset.index=idx; row.dataset.currentValue='""'; row.dataset.prefix=`pss_local_defs[${idx}]`;
    const selected=sourceToken||'custom';
    const sourceLabel=def ? (def.label||'Data record') : 'New project data record';
    row.innerHTML=`<div class="pss-local-field-builder__header"><div><span class="pss-editor-kicker">PROJECT RECORD</span><strong>${esc(sourceLabel)}</strong><span class="pss-record-source-badge">${def?esc(def.source==='core'?'Built-in field':'Reusable field'):'Project-only field'}</span></div><div class="pss-local-field-actions"><button type="button" class="button-link pss-duplicate-local-field">Duplicate</button><button type="button" class="button-link-delete pss-remove-local-field">Remove</button></div></div><div class="pss-grid-3 pss-record-source-row"><label>Field source<select class="pss-local-field-source widefat" name="pss_local_defs[${idx}][source]">${libraryOptions(selected)}</select></label><label>Label<input type="text" name="pss_local_defs[${idx}][label]" value="${esc(def?.label||'')}" class="widefat"></label><label>Key<input type="text" name="pss_local_defs[${idx}][key]" value="${esc(def?.record_key||'')}" class="widefat" placeholder="e.g. ceiling_height"></label></div><div class="pss-grid-3"><label>Type<select class="pss-local-field-type" name="pss_local_defs[${idx}][type]">${optionHtml(def?.type||'text')}</select></label><label>Options / structure<textarea name="pss_local_defs[${idx}][options]" class="pss-local-options widefat" rows="3" placeholder="For Select / Multi Select: one option per line">${esc((def?.options||[]).join('\n'))}</textarea></label><label>Description<input type="text" name="pss_local_defs[${idx}][description]" value="${esc(def?.description||'')}" class="widefat"></label></div><input type="hidden" class="pss-local-subfields-json" name="pss_local_defs[${idx}][subfields_json]" value='${esc(json(def?.subfields||[]))}'><div class="pss-local-field-structure" data-existing-options="${esc((def?.options||[]).join('\n'))}" data-existing-subfields="${esc(json(def?.subfields||[]))}"></div><div class="pss-local-field-value"><label>Value</label></div>`;
    list.appendChild(row);
    if(document.getElementById('pss-local-field-empty')) document.getElementById('pss-local-field-empty').style.display='none';
    bindLocalRow(row); renderStructure(row); renderValueEditor(row); updateRecordCount(); row.scrollIntoView({behavior:'smooth',block:'center'});
  }

  function duplicateLocalField(row){
    const list=document.getElementById('pss-local-fields-list'); if(!row||!list)return;
    const clone=row.cloneNode(true); const idx=nextRecordIndex(list);
    clone.dataset.index=idx; clone.dataset.prefix=`pss_local_defs[${idx}]`;
    clone.querySelectorAll('[name]').forEach(el=>{el.name=el.name.replace(/pss_local_defs\[[^\]]+\]/,'pss_local_defs['+idx+']');});
    const source=clone.querySelector('.pss-local-field-source'); if(source){source.value='custom';}
    const label=clone.querySelector('input[name*="[label]"]'); const key=clone.querySelector('input[name*="[key]"]');
    const oldLabel=label?.value||'Project record'; const oldKey=localFieldKey(row);
    if(label) label.value='Copy of '+oldLabel; if(key) key.value=oldKey+'_copy';
    clone.querySelector('.pss-record-source-badge')?.replaceChildren(document.createTextNode('Project-only field'));
    clone.querySelectorAll('[name^="pss_local_field["]').forEach(el=>{const old=el.name; const pos=old.indexOf(']'); if(pos>=0) el.name='pss_local_field['+localFieldKey(clone)+']'+old.substring(pos+1);});
    list.appendChild(clone); bindLocalRow(clone); renderStructure(clone); renderValueEditor(clone); updateRecordCount(); clone.scrollIntoView({behavior:'smooth',block:'center'});
  }

  function bindLocalRow(row){
    bindLibrarySource(row);
    const type=row.querySelector('.pss-local-field-type');
    if(type && type.dataset.bound!=='1'){
      type.dataset.bound='1'; type.addEventListener('change',()=>{renderStructure(row); renderValueEditor(row);});
    }
    row.querySelectorAll('input[name*="[key]"]').forEach(el=>{el.addEventListener('input',()=>{syncLocalValueName(row);});});
    row.querySelectorAll('input,textarea,select').forEach(el=>{if(el.dataset.recordBound==='1')return;el.dataset.recordBound='1';el.addEventListener('input',()=>{if(el.classList.contains('pss-local-options')){const structure=row.querySelector('.pss-local-structure');if(structure)structure.dataset.existingOptions=el.value;} readLocalSubfields(row);});el.addEventListener('change',()=>readLocalSubfields(row));});
  }

  function syncLocalValueName(row){
    const key=localFieldKey(row);
    row.querySelectorAll('[name^="pss_local_field["]').forEach(el=>{
      const old=el.name; const pos=old.indexOf(']'); if(pos<0)return;
      el.name='pss_local_field['+key+']'+old.substring(pos+1);
    });
  }

  function bindMedia(root){
    if(!root || !window.wp || !wp.media)return;
    root.querySelectorAll('.pss-single-media').forEach(btn=>{if(btn.dataset.bound)return;btn.dataset.bound='1';btn.addEventListener('click',e=>{e.preventDefault();const input=btn.parentNode.querySelector('.pss-media-id');const frame=wp.media({title:'Choose image',button:{text:'Use image'},multiple:false});frame.on('select',()=>{const a=frame.state().get('selection').first().toJSON();if(input)input.value=a.id;const cur=btn.parentNode.querySelector('.pss-media-current');if(cur)cur.textContent='ID '+a.id;});frame.open();});});
    root.querySelectorAll('.pss-gallery-media,.pss-media-button').forEach(btn=>{if(btn.dataset.bound)return;btn.dataset.bound='1';btn.addEventListener('click',e=>{e.preventDefault();const selector=btn.dataset.target?document.querySelector(btn.dataset.target):btn.parentNode.querySelector('.pss-media-id');const frame=wp.media({title:'Choose images',button:{text:'Use images'},multiple:true});frame.on('select',()=>{const ids=frame.state().get('selection').map(a=>a.toJSON().id);if(selector)selector.value=ids.join(',');const cur=btn.parentNode.querySelector('.pss-media-current');if(cur)cur.textContent=ids.length+' image(s)';});frame.open();});});
  }

  function cardPicker(){
    const list=document.getElementById('pss-card-field-picker'), select=document.getElementById('pss-card-field-add'), add=document.getElementById('pss-add-card-field');
    if(!list||!select||!add||add.dataset.bound==='1')return; add.dataset.bound='1';
    add.addEventListener('click',()=>{const opt=select.options[select.selectedIndex];if(!opt||!opt.value)return;const row=document.createElement('div');row.className='pss-card-field-row';row.innerHTML=`<input type="hidden" name="pss_card_fields[]" value="${esc(opt.value)}"><span class="pss-drag-handle">⋮⋮</span><span class="pss-card-field-name">${esc(opt.text.split(' — ')[0])}</span><span class="pss-card-field-key">${esc(opt.value)}</span><button type="button" class="button-link pss-card-remove">Remove</button>`;list.appendChild(row);opt.remove();});
  }

  function bindProjectEditor(){
    document.querySelectorAll('.pss-local-field-builder').forEach(row=>{bindLocalRow(row);renderStructure(row);renderValueEditor(row);});
    document.getElementById('pss-add-local-field')?.addEventListener('click',e=>{e.preventDefault();addLocalField('');});
    document.getElementById('pss-add-library-record')?.addEventListener('click',e=>{e.preventDefault();const select=document.getElementById('pss-quick-record-source');const token=select?.value||'';if(!token)return;addLocalField(token);const opt=select.options[select.selectedIndex];opt?.remove();select.selectedIndex=0;});
    const search=document.getElementById('pss-quick-record-search'), select=document.getElementById('pss-quick-record-source');
    search?.addEventListener('input',()=>{const term=(search.value||'').toLowerCase().trim();if(!select)return;[...select.options].forEach((opt,i)=>{if(i===0){opt.hidden=false;return;}opt.hidden=!!term&&!opt.text.toLowerCase().includes(term);});});
    updateRecordCount(); cardPicker(); bindMedia(document);
  }

  document.addEventListener('click',function(e){
    if(e.target.classList.contains('pss-remove-local-field')){e.preventDefault();const row=e.target.closest('.pss-local-field-builder');const token=row?.querySelector('.pss-local-field-source')?.value||'custom';row?.remove();const select=document.getElementById('pss-quick-record-source');if(select&&token&&token!=='custom'&&projectLibrary()[token]&&!Array.from(select.options).some(o=>o.value===token)){const def=projectLibrary()[token];const opt=document.createElement('option');opt.value=token;opt.textContent=(def.label||def.key||token)+' — '+(def.type||'text');select.appendChild(opt);}updateRecordCount();}
    if(e.target.classList.contains('pss-duplicate-local-field')){e.preventDefault();duplicateLocalField(e.target.closest('.pss-local-field-builder'));}
    if(e.target.classList.contains('pss-add-local-subfield')){e.preventDefault();const row=e.target.closest('.pss-local-field-builder');const box=row?.querySelector('.pss-local-structure');if(box){const type=row.querySelector('.pss-local-field-type')?.value||'text';box.querySelector('.pss-local-subfields-list')?.insertAdjacentHTML('beforeend',subfieldRow(type));readLocalSubfields(row);}}
    if(e.target.classList.contains('pss-remove-local-subfield')){e.preventDefault();const row=e.target.closest('.pss-local-field-builder');e.target.closest('.pss-local-subfield-row')?.remove();if(row)readLocalSubfields(row);}
    if(e.target.classList.contains('pss-add-repeater')){e.preventDefault();const wrap=e.target.closest('.pss-repeater');const rowEl=wrap?.querySelector('.pss-repeater-row');const parent=wrap?.querySelector('.pss-repeater-list');if(!wrap||!parent)return;const sourceRow=wrap.closest('.pss-local-field-builder');const subfields=JSON.parse(sourceRow?.querySelector('.pss-local-subfields-json')?.value||'[]');const idx=parent.querySelectorAll('.pss-repeater-row').length;parent.insertAdjacentHTML('beforeend',repeaterRowHtml(localFieldValueName(sourceRow),idx,subfields,{}));}
    if(e.target.classList.contains('pss-remove-repeater')){e.preventDefault();e.target.closest('.pss-repeater-row')?.remove();}
    if(e.target.classList.contains('pss-add-table-row')){e.preventDefault();const wrap=e.target.closest('.pss-data-table');const tbody=wrap?.querySelector('tbody');const rowEl=wrap?.closest('.pss-local-field-builder');if(!wrap||!tbody||!rowEl)return;const cols=JSON.parse(rowEl.querySelector('.pss-local-subfields-json')?.value||'[]');const idx=tbody.querySelectorAll('tr').length;tbody.insertAdjacentHTML('beforeend',tableRowHtml(localFieldValueName(rowEl),idx,cols,{}));}
    if(e.target.classList.contains('pss-remove-table-row')){e.preventDefault();e.target.closest('tr')?.remove();}
    if(e.target.classList.contains('pss-add-icon-value')){e.preventDefault();const wrap=e.target.closest('.pss-icon-value-editor');const list=wrap?.querySelector('.pss-icon-value-list');const row=wrap?.closest('.pss-local-field-builder');if(!wrap||!list||!row)return;const idx=list.querySelectorAll('.pss-icon-value-row').length;list.insertAdjacentHTML('beforeend',iconRowHtml(localFieldValueName(row),idx,{}));}
    if(e.target.classList.contains('pss-remove-icon-value')){e.preventDefault();e.target.closest('.pss-icon-value-row')?.remove();}
    if(e.target.classList.contains('pss-card-remove')){e.preventDefault();e.target.closest('.pss-card-field-row')?.remove();}
  });

  // Reusable field-library page helpers.
  function fieldLibrarySubfieldRow(type,data){return `<div class="pss-subfield-row"><input type="text" class="pss-subfield-label" value="${esc(data?.label||'')}" placeholder="Label"><input type="text" class="pss-subfield-key" value="${esc(data?.key||'')}" placeholder="key">${type!=='table'?`<select class="pss-subfield-type">${optionHtml(data?.type||'text')}</select>`:''}<button type="button" class="button-link-delete pss-remove-subfield">Remove</button></div>`;}
  function syncDefinition(builder){
    const box=builder.querySelector('.pss-subfields-builder'); if(box){const type=builder.querySelector('.pss-field-type')?.value||'text';const data=[...box.querySelectorAll('.pss-subfield-row')].map(r=>({label:r.querySelector('.pss-subfield-label')?.value||'',key:r.querySelector('.pss-subfield-key')?.value||'',type:type==='table'?'text':(r.querySelector('.pss-subfield-type')?.value||'text')})).filter(r=>r.label&&r.key);const hidden=builder.querySelector('.pss-subfields-json');if(hidden)hidden.value=JSON.stringify(data);}
  }
  function refreshDefinition(builder){
    const type=builder.querySelector('.pss-field-type')?.value||'text', area=builder.querySelector('.pss-field-type-options'); if(!area)return;
    let data=[]; try{data=JSON.parse(area.querySelector('.pss-subfields-json')?.value||'[]')||[]}catch(e){}
    let html='';
    if(type==='select'||type==='multi_select') html+=`<label>Options (one per line)<textarea name="fields[${builder.dataset.index}][options]" rows="4"></textarea></label>`;
    if(['repeater','group','table'].includes(type)){html+=`<div class="pss-subfields-builder"><div class="pss-subfields-builder__head"><strong>${type==='table'?'Table Columns':'Subfields'}</strong><button type="button" class="button pss-add-subfield">+ Add</button></div><input type="hidden" class="pss-subfields-json" name="fields[${builder.dataset.index}][subfields]" value="${esc(json(data))}"><div class="pss-subfields-list">${data.map(r=>fieldLibrarySubfieldRow(type,r)).join('')}</div></div>`;}
    if(type==='icon_value') html+='<p class="description">This field stores repeatable Icon + Title + Value rows.</p>';
    area.innerHTML=html;
  }
  function bindFieldLibraryPage(){
    const list=document.getElementById('pss-field-list'); if(!list)return;
    document.querySelectorAll('.pss-field-builder').forEach(b=>{b.querySelector('.pss-field-type')?.addEventListener('change',()=>refreshDefinition(b));});
    document.getElementById('pss-add-field')?.addEventListener('click',()=>{const idx=list.children.length;const d=document.createElement('div');d.className='pss-field-builder';d.dataset.index=idx;d.innerHTML=`<div class="pss-field-builder__head"><strong>Field</strong><button type="button" class="button-link-delete pss-remove-field">Remove</button></div><div class="pss-grid-3"><label>Label<input type="text" name="fields[${idx}][label]"></label><label>Key<input type="text" name="fields[${idx}][key]"></label><label>Type<select class="pss-field-type" name="fields[${idx}][type]">${optionHtml('text')}</select></label></div><label>Description<input type="text" name="fields[${idx}][description]"></label><label><input type="checkbox" name="fields[${idx}][required]" value="1"> Required</label><div class="pss-field-type-options"></div>`;list.appendChild(d);d.querySelector('.pss-field-type')?.addEventListener('change',()=>refreshDefinition(d));});
    list.addEventListener('click',e=>{if(e.target.classList.contains('pss-remove-field'))e.target.closest('.pss-field-builder')?.remove();if(e.target.classList.contains('pss-add-subfield')){const b=e.target.closest('.pss-field-builder');const box=b?.querySelector('.pss-subfields-builder');const type=b?.querySelector('.pss-field-type')?.value||'text';box?.querySelector('.pss-subfields-list')?.insertAdjacentHTML('beforeend',fieldLibrarySubfieldRow(type));syncDefinition(b);}});
  }

  let draggedRecord=null;
  document.addEventListener('dragstart',e=>{const row=e.target.closest('.pss-local-field-builder');if(!row)return;draggedRecord=row;row.classList.add('is-dragging');});
  document.addEventListener('dragend',e=>{const row=e.target.closest('.pss-local-field-builder');if(row)row.classList.remove('is-dragging');draggedRecord=null;});
  document.addEventListener('dragover',e=>{const target=e.target.closest('.pss-local-field-builder');if(!draggedRecord||!target||target===draggedRecord)return;e.preventDefault();const list=target.parentElement;if(!list)return;const rect=target.getBoundingClientRect();list.insertBefore(draggedRecord,e.clientY<rect.top+rect.height/2?target:target.nextSibling);});

  document.addEventListener('DOMContentLoaded',function(){
    try { bindProjectEditor(); } catch (err) { if (window.console) console.error('[PSS] project editor', err); }
    try { bindFieldLibraryPage(); } catch (err) { if (window.console) console.error('[PSS] field library', err); }
  });
})();
