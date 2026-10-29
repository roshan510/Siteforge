<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']); session_start();
function db(){static $p; return $p??=new PDO('mysql:host=localhost;dbname=siteforge_cms;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf_field(){$_SESSION['t']??=bin2hex(random_bytes(16));return '<input type="hidden" name="t" value="'.$_SESSION['t'].'">';}
function csrf_check(){hash_equals($_SESSION['t']??'',$_POST['t']??'')||die('Invalid security token');}
function need($roles=[]){if(empty($_SESSION['u'])){header('Location: login.php');exit;} $r=$_SESSION['u']['role']; if($r!=='admin'&&!in_array($r,$roles)){http_response_code(403);die('Access denied');}}
function audit($a){db()->prepare('INSERT INTO audit_trail(user_id,action) VALUES(?,?)')->execute([$_SESSION['u']['user_id']??null,$a]);}
function top($title){$M=require __DIR__.'/modules.php';$r=$_SESSION['u']['role'];$cur=$_GET['m']??basename($_SERVER['SCRIPT_NAME'],'.php');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> · SiteForge</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head><body>
<nav class="navbar sf-top px-3"><button class="btn btn-sm text-white d-lg-none" onclick="document.body.classList.toggle('side-open')"><i class="bi bi-list fs-4"></i></button><span class="fw-bold text-white"><i class="bi bi-bricks text-warning"></i> SiteForge</span><span class="ms-auto text-white-50 small"><?=e($_SESSION['u']['full_name'])?> (<?=e($r)?>) <a class="btn btn-sm btn-outline-warning ms-2" href="logout.php">Logout</a></span></nav>
<aside class="sf-side"><a href="index.php" class="<?=$cur=='index'?'on':''?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
<?php foreach($M as $k=>$m) if($r==='admin'||in_array($r,$m['roles'])) echo '<a class="'.($cur==$k?'on':'').'" href="module.php?m='.$k.'"><i class="bi bi-'.$m['icon'].'"></i> '.e($m['title']).'</a>';
if(in_array($r,['admin','manager','supervisor'])) echo '<a class="'.($cur=='stock_move'?'on':'').'" href="stock_move.php"><i class="bi bi-arrow-left-right"></i> Stock Movement</a>';
if(in_array($r,['admin','accountant','manager'])) echo '<a class="'.($cur=='payroll'?'on':'').'" href="payroll.php"><i class="bi bi-wallet2"></i> Payroll</a>'; ?></aside><main class="sf-main"><h4 class="mb-3"><?=e($title)?></h4>
<?php }
function bottom(){echo '</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>';}
