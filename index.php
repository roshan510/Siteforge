<?php require 'includes/core.php'; need(['manager','accountant','supervisor']); $d=db(); top('Dashboard');
$q=fn($s)=>$d->query($s)->fetchColumn();
$cards=[['Projects',$q('SELECT COUNT(*) FROM build_projects'),'building','primary'],['Active',$q("SELECT COUNT(*) FROM build_projects WHERE status='active'"),'activity','success'],['Workers',$q('SELECT COUNT(*) FROM labour_force'),'people','info'],['Present today',$q("SELECT COUNT(*) FROM attendance_log WHERE work_date=CURDATE() AND status IN('present','half')"),'calendar-check','secondary'],['Total expenses ₹',number_format($q('SELECT COALESCE(SUM(amount),0) FROM cost_entries')),'cash-coin','warning'],['Low stock',$q('SELECT COUNT(*) FROM stock_items WHERE qty_on_hand<=reorder_level'),'box-seam','danger']];
echo '<div class="row g-3 mb-4">'; foreach($cards as $c) echo '<div class="col-6 col-xl-2"><div class="card sf-card border-'.$c[3].'"><div class="card-body"><i class="bi bi-'.$c[2].' text-'.$c[3].' fs-3"></i><div class="fs-4 fw-bold">'.e($c[1]).'</div><div class="text-muted small">'.$c[0].'</div></div></div></div>'; echo '</div>';
$sp=$d->query('SELECT title,budget,spent,used_pct FROM v_project_spend')->fetchAll(); $pr=$d->query('SELECT title,progress_pct FROM build_projects WHERE status<>"archived"')->fetchAll();
$att=$d->query("SELECT work_date,SUM(status IN('present','half')) n FROM attendance_log GROUP BY work_date ORDER BY work_date DESC LIMIT 7")->fetchAll();?>
<div class="row g-3"><div class="col-lg-4"><div class="card p-3"><b>Budget vs spent</b><canvas id="c1"></canvas></div></div><div class="col-lg-4"><div class="card p-3"><b>Progress %</b><canvas id="c2"></canvas></div></div><div class="col-lg-4"><div class="card p-3"><b>Attendance (7 days)</b><canvas id="c3"></canvas></div></div></div>
<h5 class="mt-4">Alerts</h5><ul class="list-group"><?php
foreach($d->query('SELECT item_name,qty_on_hand,unit FROM stock_items WHERE qty_on_hand<=reorder_level') as $r) echo '<li class="list-group-item list-group-item-danger">Low stock: '.e($r['item_name']).' ('.e($r['qty_on_hand'].' '.$r['unit']).')</li>';
foreach($sp as $r) if($r['used_pct']>=90) echo '<li class="list-group-item list-group-item-warning">Budget '.e($r['used_pct']).'% used: '.e($r['title']).'</li>';
foreach($d->query('SELECT name FROM equipment_assets WHERE next_service<CURDATE()') as $r) echo '<li class="list-group-item list-group-item-warning">Maintenance overdue: '.e($r['name']).'</li>';
foreach($d->query("SELECT title FROM build_projects WHERE status='active' AND due_date<CURDATE()") as $r) echo '<li class="list-group-item list-group-item-danger">Past deadline: '.e($r['title']).'</li>'; ?></ul>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script>
const sp=<?=json_encode($sp)?>,pr=<?=json_encode($pr)?>,at=<?=json_encode(array_reverse($att))?>;
new Chart(c1,{type:'bar',data:{labels:sp.map(x=>x.title),datasets:[{label:'Budget',data:sp.map(x=>x.budget),backgroundColor:'#1f3a5f'},{label:'Spent',data:sp.map(x=>x.spent),backgroundColor:'#f5a623'}]}});
new Chart(c2,{type:'doughnut',data:{labels:pr.map(x=>x.title),datasets:[{data:pr.map(x=>x.progress_pct),backgroundColor:['#f5a623','#1f3a5f','#2e9e6b']}]}});
new Chart(c3,{type:'line',data:{labels:at.map(x=>x.work_date),datasets:[{label:'Present',data:at.map(x=>x.n),borderColor:'#2e9e6b',tension:.3}]}});
</script><?php bottom();
