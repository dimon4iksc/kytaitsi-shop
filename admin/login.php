<?php
session_start();
require __DIR__ . '/../includes/db.php';
$pdo = db();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
    $stmt->execute([$u]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($p, $user['password_hash'])) {
        $_SESSION['admin_id'] = $user['id'];
        header('Location: /admin/index.php');
        exit;
    }
    $error = 'Невірний логін або пароль.';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Вхід в адмінку — КИТАЙЦІ SHOP</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Manrope:wght@400;600&display=swap" rel="stylesheet">
<style>
  body{background:#17151B; color:#F4F0E6; font-family:'Manrope',sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; margin:0;}
  .box{background:#211E26; padding:44px; width:340px; border:1px solid rgba(244,240,230,0.12);}
  h1{font-family:'Fraunces',serif; font-size:22px; margin:0 0 26px;}
  label{display:block; font-size:13px; color:#8C877D; margin-bottom:6px;}
  input{width:100%; padding:11px; margin-bottom:16px; background:#17151B; border:1px solid rgba(244,240,230,0.16); color:#fff; box-sizing:border-box;}
  button{width:100%; padding:12px; background:#A9812F; color:#fff; border:none; font-weight:600; cursor:pointer;}
  .err{color:#E08A93; font-size:13.5px; margin-bottom:14px;}
</style>
</head>
<body>
  <form class="box" method="post">
    <h1>КИТАЙЦІ SHOP · Адмінка</h1>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <label>Логін</label>
    <input type="text" name="username" required autofocus>
    <label>Пароль</label>
    <input type="password" name="password" required>
    <button type="submit">Увійти</button>
  </form>
</body>
</html>
