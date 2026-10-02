jQuery(function($){
const scan=$('#bilf-scan'),status=$('#bilf-status'),results=$('#bilf-results');
const esc=v=>$('<div>').text(v==null?'':String(v)).html();
function msg(m,c){status.removeAttr('hidden').removeClass('success error').addClass(c||'').html(m);}
scan.on('click',function(){
 scan.prop('disabled',true).text('Scanning...');results.attr('hidden',true).empty();msg('Scanning published blog posts...','');
 $.post(BILF.ajax,{action:'bilf_scan',nonce:BILF.nonce}).done(function(r){
  if(!r.success){msg(esc(r.data?.message||'Scan failed.'),'error');return;}
  $('#bilf-prefix').text(r.data.prefix);
  render(r.data);
  msg('Scan complete. Scanned <strong>'+r.data.posts_scanned+'</strong> posts and found <strong>'+r.data.issues.length+'</strong> matching links.',r.data.issues.length?'':'success');
 }).fail(()=>msg('The scan request failed. Check PHP/server limits and try again.','error'))
 .always(()=>scan.prop('disabled',false).text('Scan Blog Posts'));
});
function render(data){
 if(!data.issues.length){results.removeAttr('hidden').html('<div class="bilf-card"><h2>No matching broken blog links found</h2><p>Third-party URLs and normal main-page URLs are ignored.</p></div>');return;}
 let h='<div class="bilf-card"><div class="bilf-toolbar"><label><input type="checkbox" id="bilf-all"> <strong>Select All</strong></label><span id="bilf-count">0 selected</span><button class="button button-primary" id="bilf-fix" disabled>Change Selected Links</button></div><div class="bilf-table-wrap"><table class="widefat striped bilf-table"><thead><tr><th></th><th>Source Blog</th><th>Current Link</th><th>Matched Blog</th><th>Correct URL</th></tr></thead><tbody>';
 data.issues.forEach((x,i)=>{h+='<tr><td><input class="bilf-item" type="checkbox" value="'+i+'"></td><td><a target="_blank" rel="noopener" href="'+esc(x.source_url)+'">'+esc(x.source_title)+'</a></td><td><code>'+esc(x.old_url)+'</code></td><td><a target="_blank" rel="noopener" href="'+esc(x.new_url)+'">'+esc(x.target_title)+'</a><br><small>slug: '+esc(x.slug)+'</small></td><td><code>'+esc(x.new_url)+'</code></td></tr>';});
 h+='</tbody></table></div></div>';results.removeAttr('hidden').html(h);
 $('#bilf-all').on('change',function(){$('.bilf-item').prop('checked',this.checked);update();});
 $('.bilf-item').on('change',update);
 $('#bilf-fix').on('click',function(){
  const selected=[];$('.bilf-item:checked').each(function(){selected.push(data.issues[+this.value]);});
  if(!selected.length||!confirm('Change '+selected.length+' selected link(s)?'))return;
  const b=$(this);b.prop('disabled',true).text('Changing...');
  $.post(BILF.ajax,{action:'bilf_fix',nonce:BILF.nonce,items:JSON.stringify(selected)}).done(function(r){
   if(!r.success){msg(esc(r.data?.message||'Update failed.'),'error');return;}
   msg('<strong>'+r.data.changed+'</strong> link(s) changed. <strong>'+r.data.skipped+'</strong> skipped.',r.data.changed?'success':'');
   scan.trigger('click');
  }).fail(()=>msg('The update request failed.','error')).always(()=>b.prop('disabled',false).text('Change Selected Links'));
 });
}
function update(){const n=$('.bilf-item:checked').length,t=$('.bilf-item').length;$('#bilf-count').text(n+' selected');$('#bilf-fix').prop('disabled',!n);$('#bilf-all').prop('checked',n>0&&n===t);}
});
