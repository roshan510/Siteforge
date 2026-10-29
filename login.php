<?php require 'includes/core.php'; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check(); $s=db()->prepare('SELECT * FROM site_users WHERE username=?');$s->execute([trim($_POST['username']??'')]);$u=$s->fetch();
 if($u&&password_verify($_POST['password']??'',$u['pass_hash'])){session_regenerate_id(true);unset($u['pass_hash']);$_SESSION['u']=$u;audit('login');header('Location: index.php');exit;} $err='Invalid username or password.';}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SiteForge Login</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head>
<body class="login-bg"><form method="post" class="card login-card p-4 shadow-lg"><h3 class="text-center mb-1">🏗️ SiteForge</h3><p class="text-center text-muted">Construction Site Management</p>
<?php if($err) echo '<div class="alert alert-danger py-2">'.e($err).'</div>'; echo csrf_field(); ?>
<input class="form-control mb-3" name="username" placeholder="Username" required autofocus><input type="password" class="form-control mb-3" name="password" placeholder="Password" required><button class="btn btn-warning w-100 fw-semibold">Sign in</button></form></body></html>
