<?php require 'includes/core.php'; need(['manager','supervisor']); $d=db(); $msg=$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check(); $i=(int)$_POST['item_id'];$p=(int)$_POST['project_id'];$ty=$_POST['txn_type'];$q=(float)$_POST['qty'];$uc=(float)$_POST['unit_cost'];
 try{ if($q<=0||$uc<0||!in_array($ty,['purchase','issue','return']))throw new Exception('Invalid quantity or type.');
  $d->beginTransaction(); $s=$d->prepare('SELECT qty_on_hand FROM stock_items WHERE item_id=? FOR UPDATE');$s->execute([$i]);$have=$s->fetchColumn();
  if($ty==='issue'&&$q>$have)throw new Exception("Insufficient stock: only $have available.");
  $d->prepare('UPDATE stock_items SET qty_on_hand=qty_on_hand'.($ty==='issue'?'-':'+').'? WHERE item_id=?')->execute([$q,$i]);
  $d->prepare('INSERT INTO stock_ledger(item_id,project_id,txn_type,qty,unit_cost,user_id) VALUES(?,?,?,?,?,?)')->execute([$i,$p,$ty,$q,$uc,$_SESSION['u']['user_id']]);
  if($ty==='purchase')$d->prepare("INSERT INTO cost_entries(project_id,category,amount,cost_date,description) VALUES(?,'material',?,CURDATE(),?)")->execute([$p,$q*$uc,'Purchase via stock ledger']);
  audit("stock $ty item#$i qty $q"); $d->commit(); $msg='Stock updated.';
 }catch(Exception $x){ if($d->inTransaction())$d->rollBack(); $err=$x instanceof PDOException?'Database error.':$x->getMessage(); }}
top('Stock Movement'); if($msg)echo '<div class="alert alert-success">'.e($msg).'</div>'; if($err)echo '<div class="alert alert-danger">'.e($err).'</div>';
$opt=fn($sql)=>implode('',array_map(fn($r)=>'<option value="'.e($r['i']).'">'.e($r['l']).'</option>',$d->query($sql)->fetchAll()));
echo '<form method="post" class="card p-3 mb-4"><div class="row g-2">'.csrf_field().'<div class="col-md-3"><select name="item_id" class="form-select">'.$opt("SELECT item_id i,CONCAT(item_name,' (',qty_on_hand,' ',unit,')') l FROM stock_items").'</select></div><div class="col-md-3"><select name="project_id" class="form-select">'.$opt('SELECT project_id i,title l FROM build_projects').'</select></div><div class="col-md-2"><select name="txn_type" class="form-select"><option>purchase</option><option>issue</option><option>return</option></select></div><div class="col-md-1"><input name="qty" type="number" step="0.01" min="0.01" class="form-control" placeholder="Qty" required></div><div class="col-md-2"><input name="unit_cost" type="number" step="0.01" min="0" value="0" class="form-control"></div><div class="col-md-1"><button class="btn btn-warning">Save</button></div></div></form>';
echo '<div class="card"><table class="table mb-0"><tr><th>Date</th><th>Item</th><th>Project</th><th>Type</th><th>Qty</th><th>Unit cost</th></tr>';
foreach($d->query('SELECT l.*,i.item_name,p.title FROM stock_ledger l JOIN stock_items i USING(item_id) LEFT JOIN build_projects p ON p.project_id=l.project_id ORDER BY txn_id DESC LIMIT 30') as $r)echo '<tr><td>'.e($r['txn_date']).'</td><td>'.e($r['item_name']).'</td><td>'.e($r['title']).'</td><td>'.e($r['txn_type']).'</td><td>'.e($r['qty']).'</td><td>'.e($r['unit_cost']).'</td></tr>';
echo '</table></div>'; bottom();
