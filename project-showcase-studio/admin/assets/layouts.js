(function(){
  var list=document.getElementById('pss-condition-list'); if(!list)return;
  var data=window.PSSConditionData||{};
  var fieldOps={
    equals:'Equals',not_equals:'Does not equal',contains:'Contains',not_contains:'Does not contain',
    greater:'Greater than',less:'Less than',greater_equal:'Greater or equal',less_equal:'Less or equal',empty:'Is empty',not_empty:'Is not empty'
  };
  function esc(v){return String(v==null?'':v).replace(/[&<>"]/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));}
  function valueOptions(type,current){
    var rows=data[type]||[]; var html='';
    rows.forEach(function(r){html+='<option value="'+esc(r.id)+'" '+(String(r.id)===String(current)?'selected':'')+'>'+esc(r.name)+'</option>';});
    if(!html && type==='all') html='<option value="">All Projects</option>';
    return html;
  }
  function fieldOptions(current){
    return (data.field||[]).map(function(r){return '<option value="'+esc(r.id)+'" '+(String(r.id)===String(current)?'selected':'')+'>'+esc(r.name)+'</option>';}).join('');
  }
  function operatorOptions(current){
    return Object.keys(fieldOps).map(function(k){return '<option value="'+k+'" '+(k===current?'selected':'')+'>'+fieldOps[k]+'</option>';}).join('');
  }
  function syncRow(row){
    var type=row.querySelector('.pss-condition-type')?.value||'all';
    var value=row.querySelector('.pss-condition-value');
    var field=row.querySelector('.pss-condition-field');
    var operator=row.querySelector('.pss-condition-operator');
    var fieldValue=row.querySelector('.pss-condition-field-value');
    var current=value?.dataset.current||value?.value||'';
    if(value)value.innerHTML=valueOptions(type,current);
    if(field){ field.style.display=type==='field'?'':'none'; field.disabled=type!=='field'; field.innerHTML=fieldOptions(field.dataset.current||field.value||''); }
    if(operator){ operator.style.display=type==='field'?'':'none'; operator.disabled=type!=='field'; }
    if(fieldValue){ fieldValue.style.display=type==='field'?'':'none'; fieldValue.disabled=type!=='field'; }
    if(value){ value.style.display=type==='field'||type==='all'?'none':''; value.disabled=type==='field'||type==='all'; }
    var hidePriority=type==='all'; var priority=row.querySelector('input[name*="[priority]"]'); if(priority){priority.style.display=hidePriority?'none':'';priority.disabled=hidePriority;}
    row.dataset.synced='1';
  }
  function addRow(i){
    var html='<div class="pss-condition-row" data-index="'+i+'">'+
      '<select name="pss_conditions['+i+'][mode]"><option value="include">Include</option><option value="exclude">Exclude</option></select>'+
      '<select name="pss_conditions['+i+'][type]" class="pss-condition-type">'+
      '<option value="all">All Projects</option><option value="project">Specific Project</option><option value="category">Category</option><option value="style">Style</option><option value="location">Location</option><option value="type">Project Type</option><option value="field">Custom Field</option>'+
      '</select>'+
      '<select name="pss_conditions['+i+'][value]" class="pss-condition-value" data-current=""></select>'+
      '<select name="pss_conditions['+i+'][field_key]" class="pss-condition-field" data-current=""></select>'+ 
      '<select name="pss_conditions['+i+'][operator]" class="pss-condition-operator">'+operatorOptions('equals')+'</select>'+ 
      '<input type="text" name="pss_conditions['+i+'][field_value]" class="pss-condition-field-value" placeholder="Field value">'+
      '<input type="number" name="pss_conditions['+i+'][priority]" value="10" min="0" max="10000" title="Additional priority">'+
      '<button type="button" class="button-link-delete pss-remove-condition">Remove</button></div>';
    list.insertAdjacentHTML('beforeend',html); syncRow(list.lastElementChild);
  }
  document.querySelectorAll('.pss-condition-row').forEach(syncRow);
  document.getElementById('pss-add-condition').addEventListener('click',function(){addRow(list.querySelectorAll('.pss-condition-row').length);});
  list.addEventListener('change',function(e){
    if(e.target.classList.contains('pss-condition-type')){
      var row=e.target.closest('.pss-condition-row');
      var value=row.querySelector('.pss-condition-value'); if(value)value.dataset.current='';
      syncRow(row);
    }
  });
  list.addEventListener('click',function(e){
    if(e.target.classList.contains('pss-remove-condition')){e.preventDefault();e.target.closest('.pss-condition-row')?.remove();}
  });
})();
