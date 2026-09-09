<?php
session_start();
require __DIR__ . '/includes/db.php';
$pdo = db();

$catSlug = $_GET['cat'] ?? '';
$categories = $pdo->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE p.is_active = 1";
$params = [];
if ($catSlug) {
    $sql .= " AND c.slug = ?";
    $params[] = $catSlug;
}
$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentCatName = 'Весь каталог';
foreach ($categories as $c) if ($c['slug'] === $catSlug) $currentCatName = $c['name'];

$pageTitle = $currentCatName . ' — КИТАЙЦІ SHOP';
$active = $catSlug ?: '';
include __DIR__ . '/includes/header.php';
?>
<style>
  .catalog-head{padding:56px 0 32px; border-bottom:1px solid var(--line);}
  .catalog-head h1{font-size:38px; margin-bottom:10px;}
  .catalog-head p{color:var(--stone); font-size:14.5px;}
  .catalog-body{padding:48px 0 96px; display:grid; grid-template-columns:220px 1fr; gap:48px;}
  .filters{border-right:1px solid var(--line); padding-right:24px;}
  .filters h4{font-size:13px; text-transform:none; color:var(--stone); margin-bottom:14px; font-weight:600;}
  .filters a{
    display:block; padding:9px 0; font-size:14.5px; color:var(--ink-soft);
    border-bottom:1px solid var(--line);
  }
  .filters a.active{color:var(--brass); font-weight:600;}
  .grid-products{display:grid; grid-template-columns:repeat(3,1fr); gap:28px;}
  .card{background:#fff; border:1px solid var(--line);}
  .card-media{aspect-ratio:4/5; background:linear-gradient(150deg, var(--ivory-dim), #fff 70%); position:relative; display:flex; align-items:center; justify-content:center; border-bottom:1px solid var(--line);}
  .card-media .ph{font-family:'Fraunces',serif; color:var(--stone); font-size:14px; padding:0 16px; text-align:center;}
  .card-media .sale-tag{position:absolute; top:14px; left:14px;}
  .card-body{padding:20px;}
  .card-cat{font-size:12px; color:var(--stone); margin-bottom:6px; display:block;}
  .card-name{font-size:16.5px; font-weight:600; margin-bottom:10px; display:block; font-family:'Fraunces',serif;}
  .card-prices{display:flex; align-items:center;}
  .empty{padding:60px 0; color:var(--stone); text-align:center;}
  @media (max-width: 860px){
    .catalog-body{grid-template-columns:1fr;}
    .filters{border-right:none; border-bottom:1px solid var(--line); padding-bottom:20px; display:flex; gap:18px; overflow-x:auto;}
    .filters a{border-bottom:none; white-space:nowrap;}
    .grid-products{grid-template-columns:repeat(2,1fr);}
  }
</style>

<section class="catalog-head">
  <div class="wrap">
    <h1><?= htmlspecialchars($currentCatName) ?></h1>
    <p><?= count($products) ?> товар(ів)</p>
  </div>
</section>

<section class="wrap catalog-body">
  <aside class="filters">
    <h4>Категорії</h4>
    <a href="/catalog.php" class="<?= $catSlug===''?'active':'' ?>">Всі товари</a>
    <?php foreach ($categories as $c): ?>
      <a href="/catalog.php?cat=<?= urlencode($c['slug']) ?>" class="<?= $catSlug===$c['slug']?'active':'' ?>"><?= htmlspecialchars($c['name']) ?></a>
    <?php endforeach; ?>
  </aside>
  <div>
    <?php if (!$products): ?>
      <div class="empty">Товарів поки немає в цій категорії.</div>
    <?php else: ?>
      <div class="grid-products">
        <?php foreach ($products as $p): ?>
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
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
