<?php
session_start();
require __DIR__ . '/includes/db.php';
$pdo = db();

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM products p JOIN categories c ON c.id=p.category_id WHERE p.slug=? AND p.is_active=1");
$stmt->execute([$slug]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Товар не знайдено';
    include __DIR__ . '/includes/header.php';
    echo '<div class="wrap" style="padding:80px 0;">Товар не знайдено. <a href="/catalog.php">До каталогу</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$vStmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id=? ORDER BY id');
$vStmt->execute([$product['id']]);
$variants = $vStmt->fetchAll(PDO::FETCH_ASSOC);

$sizes = array_values(array_unique(array_column($variants, 'size')));
$colors = array_values(array_unique(array_column($variants, 'color')));

$pageTitle = $product['name'] . ' — КИТАЙЦІ SHOP';
$active = $product['cat_slug'];
include __DIR__ . '/includes/header.php';
?>
<style>
  .pdp{padding:56px 0 100px; display:grid; grid-template-columns:1fr 1fr; gap:64px;}
  .pdp-media{aspect-ratio:4/5; background:linear-gradient(150deg, var(--ivory-dim), #fff 70%); border:1px solid var(--line); display:flex; align-items:center; justify-content:center;}
  .pdp-media .ph{font-family:'Fraunces',serif; color:var(--stone); font-size:16px; text-align:center; padding:0 40px;}
  .pdp-info .breadcrumb{font-size:13px; color:var(--stone); margin-bottom:18px;}
  .pdp-info h1{font-size:32px; margin-bottom:14px;}
  .pdp-info .prices{margin-bottom:24px; display:flex; align-items:baseline; gap:2px;}
  .pdp-info .price{font-size:26px;}
  .pdp-info .desc{color:var(--ink-soft); margin-bottom:30px; max-width:460px;}
  .opt-group{margin-bottom:24px;}
  .opt-group label{font-size:13px; color:var(--stone); display:block; margin-bottom:10px;}
  .opt-swatches{display:flex; flex-wrap:wrap; gap:10px;}
  .opt-swatches button{
    border:1px solid var(--ink); background:#fff; padding:9px 16px; font-size:14px;
    cursor:pointer; border-radius:var(--radius); font-family:'Manrope',sans-serif;
  }
  .opt-swatches button.sel{background:var(--ink); color:var(--ivory);}
  .opt-swatches button[disabled]{opacity:.3; text-decoration:line-through; cursor:not-allowed;}
  .stock-note{font-size:13px; color:var(--stone); margin-bottom:26px;}
  .qty-row{display:flex; align-items:center; gap:18px; margin-bottom:24px;}
  .qty-box{display:flex; border:1px solid var(--ink);}
  .qty-box button{width:38px; background:none; border:none; font-size:16px; cursor:pointer;}
  .qty-box input{width:44px; text-align:center; border:none; border-left:1px solid var(--ink); border-right:1px solid var(--ink); font-family:'Manrope',sans-serif;}
  @media (max-width: 860px){ .pdp{grid-template-columns:1fr;} }
</style>

<section class="wrap pdp">
  <div class="pdp-media">
    <?php if (!empty($product['image'])): ?>
      <img src="/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
    <?php else: ?>
      <span class="ph"><?= htmlspecialchars($product['name']) ?></span>
    <?php endif; ?>
  </div>
  <div class="pdp-info">
    <div class="breadcrumb"><a href="/catalog.php?cat=<?= urlencode($product['cat_slug']) ?>"><?= htmlspecialchars($product['cat_name']) ?></a> / <?= htmlspecialchars($product['name']) ?></div>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="prices">
      <?php if ($product['discount_price']): ?>
        <span class="price old"><?= money($product['price']) ?></span>
        <span class="price"><?= money($product['discount_price']) ?></span>
        <span class="badge-sale" style="margin-left:10px;">Знижка</span>
      <?php else: ?>
        <span class="price"><?= money($product['price']) ?></span>
      <?php endif; ?>
    </div>
    <p class="desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <form method="post" action="/cart-add.php" id="pdpForm">
      <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

      <?php if (count($sizes) > 1 || $sizes[0] !== ''): ?>
      <div class="opt-group">
        <label>Розмір</label>
        <div class="opt-swatches" id="sizeSwatches">
          <?php foreach ($sizes as $s): ?>
            <button type="button" data-size="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (count($colors) > 1 || $colors[0] !== ''): ?>
      <div class="opt-group">
        <label>Колір</label>
        <div class="opt-swatches" id="colorSwatches">
          <?php foreach ($colors as $c): ?>
            <button type="button" data-color="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <input type="hidden" name="variant_id" id="variantIdInput" value="">
      <div class="stock-note" id="stockNote">Оберіть варіант, щоб побачити наявність.</div>

      <div class="qty-row">
        <div class="qty-box">
          <button type="button" onclick="stepQty(-1)">−</button>
          <input type="text" name="qty" id="qtyInput" value="1" readonly>
          <button type="button" onclick="stepQty(1)">+</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" id="addBtn" disabled style="width:100%;">Оберіть варіант</button>
    </form>
  </div>
</section>

<script>
const variants = <?= json_encode($variants) ?>;
let selSize = null, selColor = null;

function refresh(){
  const sizeBtns = document.querySelectorAll('#sizeSwatches button');
  const colorBtns = document.querySelectorAll('#colorSwatches button');

  sizeBtns.forEach(b => b.classList.toggle('sel', b.dataset.size === selSize));
  colorBtns.forEach(b => b.classList.toggle('sel', b.dataset.color === selColor));

  const needSize = sizeBtns.length > 0;
  const needColor = colorBtns.length > 0;

  const stockNote = document.getElementById('stockNote');
  const addBtn = document.getElementById('addBtn');
  const variantInput = document.getElementById('variantIdInput');

  if ((needSize && !selSize) || (needColor && !selColor)) {
    stockNote.textContent = 'Оберіть варіант, щоб побачити наявність.';
    addBtn.disabled = true;
    addBtn.textContent = 'Оберіть варіант';
    variantInput.value = '';
    return;
  }

  const match = variants.find(v => (!needSize || v.size === selSize) && (!needColor || v.color === selColor));
  if (!match) {
    stockNote.textContent = 'Такого поєднання немає в наявності.';
    addBtn.disabled = true;
    addBtn.textContent = 'Немає в наявності';
    variantInput.value = '';
    return;
  }

  variantInput.value = match.id;
  if (match.stock > 0) {
    stockNote.textContent = 'В наявності: ' + match.stock + ' шт.';
    addBtn.disabled = false;
    addBtn.textContent = 'Додати в кошик';
  } else {
    stockNote.textContent = 'Немає в наявності.';
    addBtn.disabled = true;
    addBtn.textContent = 'Немає в наявності';
  }
}

document.querySelectorAll('#sizeSwatches button').forEach(b => b.addEventListener('click', () => { selSize = b.dataset.size; refresh(); }));
document.querySelectorAll('#colorSwatches button').forEach(b => b.addEventListener('click', () => { selColor = b.dataset.color; refresh(); }));

function stepQty(delta){
  const input = document.getElementById('qtyInput');
  let v = parseInt(input.value || '1') + delta;
  if (v < 1) v = 1;
  input.value = v;
}

refresh();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
