<?php
/** @var string $pageTitle */
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) $cartCount += $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'КИТАЙЦІ SHOP') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#17151B;
    --ink-soft:#3A3742;
    --ivory:#F4F0E6;
    --ivory-dim:#E9E3D3;
    --brass:#A9812F;
    --brass-bright:#C79A3E;
    --burgundy:#6E1F2B;
    --stone:#8C877D;
    --line: rgba(23,21,27,0.12);
    --radius: 2px;
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;}
  body{
    background:var(--ivory);
    color:var(--ink);
    font-family:'Manrope',sans-serif;
    font-size:16px;
    line-height:1.55;
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,.serif{
    font-family:'Fraunces',serif;
    font-weight:600;
    letter-spacing:-0.01em;
    margin:0;
  }
  a{color:inherit;text-decoration:none;}
  img{max-width:100%;display:block;}
  .wrap{max-width:1180px;margin:0 auto;padding:0 32px;}

  /* Header */
  .site-header{
    position:sticky; top:0; z-index:50;
    background:rgba(244,240,230,0.92);
    backdrop-filter:blur(8px);
    border-bottom:1px solid var(--line);
  }
  .site-header .wrap{
    display:flex; align-items:center; justify-content:space-between;
    height:76px;
  }
  .logo{
    font-family:'Fraunces',serif;
    font-size:22px;
    font-weight:700;
    letter-spacing:0.01em;
  }
  .logo span{color:var(--brass);}
  nav.main-nav{display:flex; gap:36px;}
  nav.main-nav a{
    font-size:14.5px;
    color:var(--ink-soft);
    position:relative;
    padding:4px 0;
    transition:color .2s ease;
  }
  nav.main-nav a:hover{color:var(--ink);}
  nav.main-nav a.active{color:var(--ink);}
  nav.main-nav a.active::after{
    content:'';
    position:absolute; left:0; right:0; bottom:-3px;
    height:1px; background:var(--brass);
  }
  .header-actions{display:flex; align-items:center; gap:22px;}
  .cart-link{
    display:flex; align-items:center; gap:8px;
    font-size:14.5px;
    border:1px solid var(--ink);
    padding:9px 16px;
    border-radius:var(--radius);
    transition:background .2s ease, color .2s ease;
  }
  .cart-link:hover{background:var(--ink); color:var(--ivory);}
  .cart-badge{
    background:var(--burgundy); color:#fff;
    font-size:11.5px; font-weight:700;
    min-width:18px; height:18px; border-radius:50%;
    display:inline-flex; align-items:center; justify-content:center;
    padding:0 4px;
  }

  /* Footer */
  footer{
    margin-top:96px;
    border-top:1px solid var(--line);
    background:var(--ink);
    color:var(--ivory-dim);
    padding:56px 0 32px;
  }
  footer .wrap{display:flex; justify-content:space-between; flex-wrap:wrap; gap:32px;}
  footer .foot-col h4{font-family:'Fraunces',serif; color:#fff; font-weight:600; font-size:17px; margin-bottom:14px;}
  footer .foot-col p, footer .foot-col a{font-size:14px; color:var(--ivory-dim); display:block; margin-bottom:8px; line-height:1.7;}
  footer .foot-bottom{
    margin-top:40px; padding-top:24px; border-top:1px solid rgba(244,240,230,0.14);
    font-size:12.5px; color:var(--stone);
  }

  /* Buttons */
  .btn{
    display:inline-flex; align-items:center; justify-content:center;
    padding:14px 28px;
    font-size:14.5px; font-weight:600;
    border-radius:var(--radius);
    border:1px solid var(--ink);
    cursor:pointer;
    transition:background .2s ease, color .2s ease, transform .15s ease;
  }
  .btn-primary{background:var(--ink); color:var(--ivory);}
  .btn-primary:hover{background:var(--brass); border-color:var(--brass); color:#fff;}
  .btn-outline{background:transparent; color:var(--ink);}
  .btn-outline:hover{background:var(--ink); color:var(--ivory);}
  .btn:active{transform:scale(0.98);}
  .btn[disabled]{opacity:.45; cursor:not-allowed;}

  /* Generic */
  .eyebrow{font-size:13px; color:var(--brass); font-weight:600;}
  .price{font-family:'Fraunces',serif; font-weight:600; font-size:19px;}
  .price.old{
    font-family:'Manrope',sans-serif; font-weight:500; font-size:14px;
    color:var(--stone); text-decoration:line-through; margin-right:8px;
  }
  .badge-sale{
    background:var(--burgundy); color:#fff; font-size:11.5px; font-weight:700;
    padding:3px 9px; border-radius:2px; display:inline-block;
  }

  @media (max-width: 860px){
    nav.main-nav{display:none;}
    .site-header .wrap{height:64px;}
  }
</style>
</head>
<body>
<header class="site-header">
  <div class="wrap">
    <a href="/" class="logo">КИТАЙЦІ<span>.</span>SHOP</a>
    <nav class="main-nav">
      <a href="/" class="<?= ($active ?? '')==='home'?'active':'' ?>">Головна</a>
      <a href="/catalog.php?cat=odyag" class="<?= ($active ?? '')==='odyag'?'active':'' ?>">Одяг</a>
      <a href="/catalog.php?cat=vzuttya" class="<?= ($active ?? '')==='vzuttya'?'active':'' ?>">Взуття</a>
      <a href="/catalog.php?cat=aksesuary" class="<?= ($active ?? '')==='aksesuary'?'active':'' ?>">Аксесуари</a>
      <a href="/custom-request.php" class="<?= ($active ?? '')==='custom'?'active':'' ?>">Індивідуальне замовлення</a>
    </nav>
    <div class="header-actions">
      <a href="/cart.php" class="cart-link">
        Кошик <?php if ($cartCount>0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
    </div>
  </div>
</header>
