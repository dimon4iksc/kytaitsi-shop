<?php
session_start();
require __DIR__ . '/includes/db.php';
$pdo = db();
$pageTitle = 'КИТАЙЦІ SHOP — одяг, взуття, аксесуари';
$active = 'home';

$featured = $pdo->query("
  SELECT p.*, c.name as cat_name, c.slug as cat_slug,
    (SELECT MIN(stock) FROM product_variants WHERE product_id=p.id) as min_stock
  FROM products p JOIN categories c ON c.id=p.category_id
  WHERE p.is_active=1 ORDER BY p.id DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<style>
  .hero{
    position:relative;
    background:var(--ink);
    color:var(--ivory);
    overflow:hidden;
    padding:0;
  }
  .hero .wrap{
    display:grid;
    grid-template-columns:1.1fr 0.9fr;
    align-items:center;
    gap:64px;
    min-height:560px;
    padding-top:40px; padding-bottom:40px;
  }
  .hero-eyebrow{
    color:var(--brass-bright); font-size:13.5px; font-weight:600;
    letter-spacing:0.02em; margin-bottom:22px; display:block;
  }
  .hero h1{
    font-size:56px; line-height:1.04; color:#fff;
    margin-bottom:26px;
  }
  .hero h1 em{ font-style:italic; color:var(--brass-bright); }
  .hero p.lead{
    font-size:17px; color:rgba(244,240,230,0.75); max-width:420px; margin-bottom:34px;
  }
  .hero-ctas{display:flex; gap:16px;}
  .hero-visual{
    position:relative;
    aspect-ratio:4/5;
    background:
      linear-gradient(160deg, rgba(169,129,47,0.35), rgba(23,21,27,0) 55%),
      repeating-linear-gradient(135deg, rgba(244,240,230,0.05) 0 2px, transparent 2px 26px);
    border:1px solid rgba(244,240,230,0.18);
    display:flex; align-items:flex-end; justify-content:center;
    padding:28px;
  }
  .hero-visual .tag{
    background:var(--ivory); color:var(--ink);
    padding:16px 20px; width:100%;
    font-size:13.5px;
    display:flex; justify-content:space-between; align-items:center;
  }
  .hero-visual .tag strong{font-family:'Fraunces',serif; font-size:16px;}
  .hero-stats{
    display:flex; gap:0; border-top:1px solid rgba(244,240,230,0.14);
  }
  .hero-stats .wrap{
    min-height:auto; display:flex; gap:56px; padding-top:26px; padding-bottom:26px;
    grid-template-columns:none;
  }
  .stat b{font-family:'Fraunces',serif; font-size:26px; display:block; color:#fff;}
  .stat span{font-size:13px; color:var(--stone);}

  .section{padding:96px 0;}
  .section-head{
    display:flex; justify-content:space-between; align-items:flex-end;
    margin-bottom:44px;
  }
  .section-head h2{font-size:34px;}
  .section-head a{font-size:14px; border-bottom:1px solid var(--ink); padding-bottom:2px;}

  .grid-products{
    display:grid; grid-template-columns:repeat(3, 1fr); gap:28px;
  }
  .card{
    background:#fff;
    border:1px solid var(--line);
  }
  .card-media{
    aspect-ratio:4/5;
    background:linear-gradient(150deg, var(--ivory-dim), #fff 70%);
    position:relative;
    display:flex; align-items:center; justify-content:center;
    border-bottom:1px solid var(--line);
  }
  .card-media .ph{font-family:'Fraunces',serif; color:var(--stone); font-size:14px;}
  .card-media .sale-tag{position:absolute; top:14px; left:14px;}
  .card-body{padding:20px;}
  .card-cat{font-size:12px; color:var(--stone); margin-bottom:6px; display:block;}
  .card-name{font-size:16.5px; font-weight:600; margin-bottom:10px; display:block; font-family:'Fraunces',serif;}
  .card-prices{display:flex; align-items:center;}

  .categories-strip{
    display:grid; grid-template-columns:repeat(3,1fr); gap:24px;
  }
  .cat-card{
    padding:40px 28px; background:#fff; border:1px solid var(--line);
    display:flex; flex-direction:column; justify-content:space-between; min-height:190px;
    transition:border-color .2s ease;
  }
  .cat-card:hover{border-color:var(--brass);}
  .cat-card h3{font-size:22px; margin-bottom:8px;}
  .cat-card span{color:var(--stone); font-size:13.5px;}
  .cat-card .arrow{font-size:24px; align-self:flex-end; color:var(--brass);}

  .wow-band{
    background:var(--ivory-dim);
    border-top:1px solid var(--line); border-bottom:1px solid var(--line);
    padding:80px 0;
    text-align:center;
  }
  .wow-band h2{font-size:32px; max-width:640px; margin:0 auto 18px;}
  .wow-band p{max-width:520px; margin:0 auto 30px; color:var(--ink-soft);}

  @media (max-width: 900px){
    .hero .wrap{grid-template-columns:1fr; min-height:auto; padding:56px 0;}
    .hero h1{font-size:38px;}
    .grid-products{grid-template-columns:repeat(2,1fr);}
    .categories-strip{grid-template-columns:1fr;}
    .hero-stats .wrap{gap:32px; flex-wrap:wrap;}
  }
</style>

<section class="hero">
  <div class="wrap">
    <div>
      <span class="hero-eyebrow">Оригінальний товар · під замовлення</span>
      <h1>Одяг, взуття й аксесуари,<br><em>яких немає в інших</em>.</h1>
      <p class="lead">Привозимо оригінальні речі напряму від постачальників. Не знайшли потрібне в каталозі — знайдемо особисто під ваш запит.</p>
      <div class="hero-ctas">
        <a href="/catalog.php" class="btn btn-primary" style="background:var(--ivory); color:var(--ink); border-color:var(--ivory);">Дивитись каталог</a>
        <a href="/custom-request.php" class="btn btn-outline" style="color:var(--ivory); border-color:rgba(244,240,230,0.4);">Індивідуальний запит</a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="tag">
        <span>Оверсайз худі "Basic"</span>
        <strong>990 грн</strong>
      </div>
    </div>
  </div>
  <div class="hero-stats">
    <div class="wrap">
      <div class="stat"><b>3–7 днів</b><span>до відправки Новою Поштою</span></div>
      <div class="stat"><b>100%</b><span>оригінальний товар</span></div>
      <div class="stat"><b>0 грн</b><span>консультація й підбір розміру</span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head">
      <h2>Категорії</h2>
    </div>
    <div class="categories-strip">
      <a class="cat-card" href="/catalog.php?cat=odyag">
        <div><h3>Одяг</h3><span>Худі, кардигани, база</span></div>
        <span class="arrow">→</span>
      </a>
      <a class="cat-card" href="/catalog.php?cat=vzuttya">
        <div><h3>Взуття</h3><span>Кросівки, черевики</span></div>
        <span class="arrow">→</span>
      </a>
      <a class="cat-card" href="/catalog.php?cat=aksesuary">
        <div><h3>Аксесуари</h3><span>Сумки, кепки, дрібниці</span></div>
        <span class="arrow">→</span>
      </a>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="wrap">
    <div class="section-head">
      <h2>Нові надходження</h2>
      <a href="/catalog.php">Весь каталог →</a>
    </div>
    <div class="grid-products">
      <?php foreach ($featured as $p): ?>
        <a class="card" href="/product.php?slug=<?= urlencode($p['slug']) ?>">
          <div class="card-media">
            <?php if ($p['discount_price']): ?><span class="badge-sale sale-tag">Знижка</span><?php endif; ?>
            <?php if (!empty($p['image'])): ?>
              <img src="/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <span class="ph"><?= htmlspecialchars($p['name']) ?></span>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <span class="card-cat"><?= htmlspecialchars($p['cat_name']) ?></span>
            <span class="card-name"><?= htmlspecialchars($p['name']) ?></span>
            <div class="card-prices">
              <?php if ($p['discount_price']): ?>
                <span class="price old"><?= money($p['price']) ?></span>
                <span class="price"><?= money($p['discount_price']) ?></span>
              <?php else: ?>
                <span class="price"><?= money($p['price']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="wow-band">
  <div class="wrap" style="display:block;">
    <h2>Не знайшли те, що шукали?</h2>
    <p>Скиньте посилання, фото чи просто опис товару — знайдемо, домовимось з постачальником і привеземо особисто під ваше замовлення.</p>
    <a href="/custom-request.php" class="btn btn-primary">Залишити індивідуальний запит</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
