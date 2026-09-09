<?php /** @var string $pageTitle */ ?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Адмінка') ?> — КИТАЙЦІ SHOP</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --ink:#17151B; --ivory:#F4F0E6; --brass:#A9812F; --burgundy:#6E1F2B; --stone:#8C877D; --line:#E4DFD1; }
  *{box-sizing:border-box;}
  body{margin:0; font-family:'Manrope',sans-serif; background:var(--ivory); color:var(--ink);}
  a{color:inherit; text-decoration:none;}
  .admin-shell{display:grid; grid-template-columns:220px 1fr; min-height:100vh;}
  .admin-nav{background:var(--ink); color:var(--ivory); padding:28px 20px;}
  .admin-nav .brand{font-family:'Fraunces',serif; font-weight:700; font-size:18px; margin-bottom:30px; display:block;}
  .admin-nav a{display:block; padding:10px 12px; font-size:14.5px; border-radius:3px; margin-bottom:4px; color:rgba(244,240,230,0.75);}
  .admin-nav a:hover, .admin-nav a.active{background:rgba(244,240,230,0.08); color:#fff;}
  .admin-nav .logout{margin-top:40px; color:var(--stone);}
  .admin-main{padding:36px 44px;}
  h1{font-family:'Fraunces',serif; font-size:26px; margin:0 0 26px;}
  .btn{display:inline-flex; align-items:center; padding:10px 20px; font-size:14px; font-weight:600; border:1px solid var(--ink); border-radius:3px; cursor:pointer; background:none;}
  .btn-primary{background:var(--ink); color:#fff;}
  .btn-danger{border-color:var(--burgundy); color:var(--burgundy);}
  table{width:100%; border-collapse:collapse; background:#fff; border:1px solid var(--line);}
  th{text-align:left; font-size:12px; color:var(--stone); padding:12px 16px; border-bottom:1px solid var(--line); font-weight:600;}
  td{padding:12px 16px; border-bottom:1px solid var(--line); font-size:14px;}
  .field{margin-bottom:18px;}
  .field label{display:block; font-size:13px; color:var(--stone); margin-bottom:6px;}
  .field input, .field select, .field textarea{width:100%; padding:10px 12px; border:1px solid var(--line); font-family:'Manrope',sans-serif; font-size:14px; background:#fff;}
  .card{background:#fff; border:1px solid var(--line); padding:26px; margin-bottom:24px;}
  .flash{background:#E9F5EA; border:1px solid #8FBF98; color:#2F5D36; padding:12px 16px; margin-bottom:20px; font-size:14px;}
  .variant-row{display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:12px; margin-bottom:10px; align-items:center;}
  .remove-row{color:var(--burgundy); background:none; border:none; cursor:pointer; font-size:13px;}
  .stock-low{color:var(--burgundy); font-weight:600;}
</style>
</head>
<body>
<div class="admin-shell">
  <nav class="admin-nav">
    <span class="brand">КИТАЙЦІ SHOP</span>
    <a href="/admin/index.php" class="<?= ($activeNav ?? '')==='products'?'active':'' ?>">Товари</a>
    <a href="/admin/categories.php" class="<?= ($activeNav ?? '')==='categories'?'active':'' ?>">Категорії</a>
    <a href="/admin/orders.php" class="<?= ($activeNav ?? '')==='orders'?'active':'' ?>">Замовлення</a>
    <a href="/admin/requests.php" class="<?= ($activeNav ?? '')==='requests'?'active':'' ?>">Індивідуальні запити</a>
    <a href="/" target="_blank">↗ Переглянути сайт</a>
    <a href="/admin/logout.php" class="logout">Вийти</a>
  </nav>
  <main class="admin-main">
