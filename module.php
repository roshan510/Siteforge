<?php require 'includes/core.php'; $M=require 'includes/modules.php'; $k=$_GET['m']??''; isset($M[$k])||die('Unknown module'); $c=$M[$k]; need($c['roles']);
$d=db(); $pk=$c['pk']; $t=$c['t']; $msg=''; $err='';
if(isset($_GET['csv'])){header('Content-Type: text/csv');header("Content-Disposition: attachment; filename=$k.csv");$o=fopen('php://output','w');fputcsv($o,$c['cols']);foreach($d->query($c['sql']) as $r)fputcsv($o,array_map(fn($x)=>$r[$x],$c['cols']));exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();try{$a=$_POST['act']??'';
 if($a==='del'){ if(!empty($c['archive']))$d->prepare("UPDATE $t SET status='archived' WHERE $pk=?")->execute([$_POST['id']]); else $d->prepare("DELETE FROM $t WHERE $pk=?")->execute([$_POST['id']]); audit("$a $t #".$_POST['id']); $msg='Done.';}
 else{$v=[];foreach($c['f'] as $f=>$x){$s=trim($_POST[$f]??'');$v[$f]=$s===''?null:$s;}
  if($a==='add'){$d->prepare("INSERT INTO $t(".implode(',',array_keys($v)).') VALUES('.implode(',',array_fill(0,count($v),'?')).')')->execute(array_values($v));}
  else{$d->prepare("UPDATE $t SET ".implode(',',array_map(fn($f)=>"$f=?",array_keys($v)))." WHERE $pk=?")->execute([...array_values($v),$_POST['id']]);}
  audit("$a $t"); $msg='Saved successfully.';}
}catch(PDOException $x){$err='Could not save: check required fields, duplicates, negative values or linked records.';}}
$edit=null; if(isset($_GET['edit'])){$s=$d->prepare("SELECT * FROM $t WHERE $pk=?");$s->execute([$_GET['edit']]);$edit=$s->fetch();}
top($c['title']); if($msg)echo '<div class="alert alert-success">'.e($msg).'</div>'; if($err)echo '<div class="alert alert-danger">'.e($err).'</div>';
echo '<form method="post" class="card p-3 mb-4"><div class="row g-2">'.csrf_field().'<input type="hidden" name="act" value="'.($edit?'edit':'add').'"><input type="hidden" name="id" value="'.e($edit[$pk]??'').'">';
foreach($c['f'] as $f=>[$lb,$ty]){$val=$edit[$f]??''; echo '<div class="col-md-3"><label class="form-label small">'.e($lb).'</label>';
 if(str_starts_with($ty,'sel:')){echo '<select class="form-select" name="'.$f.'">';foreach(explode(',',substr($ty,4)) as $o)echo '<option '.($o==$val?'selected':'').'>'.e($o).'</option>';echo '</select>';}
 elseif(str_starts_with($ty,'fk:')){[,$ft,$fi,$fl]=explode(':',$ty);echo '<select class="form-select" name="'.$f.'"><option value="">—</option>';foreach($d->query("SELECT $fi i,$fl l FROM $ft") as $o)echo '<option value="'.e($o['i']).'" '.($o['i']==$val?'selected':'').'>'.e($o['l']).'</option>';echo '</select>';}
 else echo '<input class="form-control" name="'.$f.'" type="'.($ty=='number'?'number" step="0.01" min="0':$ty).'" value="'.e($val).'">';
 echo '</div>';}
echo '<div class="col-12"><button class="btn btn-warning">'.($edit?'Update':'Add').'</button> '.($edit?'<a class="btn btn-light" href="?m='.$k.'">Cancel</a>':'').'</div></div></form>';
echo '<div class="d-flex mb-2"><input class="form-control w-auto" placeholder="Search..." oninput="for(const r of document.querySelectorAll(\'#tb tbody tr\'))r.hidden=!r.textContent.toLowerCase().includes(this.value.toLowerCase())"><a class="btn btn-outline-secondary ms-auto" href="?m='.$k.'&csv=1"><i class="bi bi-download"></i> CSV</a></div>';
echo '<div class="table-responsive card"><table id="tb" class="table table-hover mb-0"><thead><tr>';foreach($c['cols'] as $h)echo '<th>'.e(ucwords(str_replace('_',' ',$h))).'</th>';echo '<th></th></tr></thead><tbody>';
foreach($d->query($c['sql']) as $r){echo '<tr>';foreach($c['cols'] as $h)echo '<td>'.e($r[$h]).'</td>';
 echo '<td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?m='.$k.'&edit='.$r[$pk].'">Edit</a> <form method="post" class="d-inline" onsubmit="return confirm(\'Sure?\')">'.csrf_field().'<input type="hidden" name="act" value="del"><input type="hidden" name="id" value="'.$r[$pk].'"><button class="btn btn-sm btn-outline-danger">'.(!empty($c['archive'])?'Archive':'Delete').'</button></form></td></tr>';}
echo '</tbody></table></div>'; bottom();
