<?php // Run ONCE in browser, then DELETE this file.
require 'includes/core.php'; $p=db();
if($p->query('SELECT COUNT(*) FROM site_users')->fetchColumn()>0) die('Users already exist.');
foreach([['Site Admin','admin','Admin@123','admin'],['Meera Manager','manager','Manager@123','manager'],['Arjun Accounts','accountant','Account@123','accountant'],['Sagar Supervisor','supervisor','Super@123','supervisor']] as $u)
 $p->prepare('INSERT INTO site_users(full_name,username,pass_hash,role) VALUES(?,?,?,?)')->execute([$u[0],$u[1],password_hash($u[2],PASSWORD_DEFAULT),$u[3]]);
$p->exec('UPDATE build_projects SET manager_id=2'); echo 'Users created. Delete setup_users.php now.';
