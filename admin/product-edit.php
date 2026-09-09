<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();
$pdo = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = null;
$variants = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) { header('Location: /admin/index.php'); exit; }

    $vStmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id=? ORDER BY id');
    $vStmt->execute([$id]);
    $variants = $vStmt->fetchAll(PDO::FETCH_ASSOC);
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $discountRaw = trim($_POST['discount_price'] ?? '');
    $discount = $discountRaw !== '' ? (float)$discountRaw : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (!$name) $errors[] = 'Вкажіть назву товару.';
    if (!$categoryId) $errors[] = 'Оберіть категорію.';
    if ($price <= 0) $errors[] = 'Вкажіть коректну ціну.';
    if ($discount !== null && $discount >= $price) $errors[] = 'Ціна зі знижкою має бути меншою за звичайну ціну.';

    $sizes = $_POST['variant_size'] ?? [];
    $colors = $_POST['variant_color'] ?? [];
    $stocks = $_POST['variant_stock'] ?? [];
    $variantRows = [];
    foreach ($sizes as $i => $s) {
        $s = trim($s);
        $c = trim($colors[$i] ?? '');
        $st = (int)($stocks[$i] ?? 0);
        if ($s === '' && $c === '') continue; // skip fully empty row
        $variantRows[] = [$s, $c, $st];
    }
    if (!$variantRows) $errors[] = 'Додайте хоча б один варіант (розмір/колір/залишок).';

    // Image upload
    $imagePath = $product['image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Дозволені формати зображення: jpg, png, webp.';
        } else {
            $fname = 'p_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
            $dest = __DIR__ . '/../uploads/' . $fname;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imagePath = 'uploads/' . $fname;
            } else {
                $errors[] = 'Не вдалось завантажити зображення.';
            }
        }
    }

    if (!$errors) {
        if ($product) {
            $slug = unique_slug($pdo, 'products', $name, $product['id']);
            $stmt = $pdo->prepare('UPDATE products SET category_id=?, name=?, slug=?, description=?, price=?, discount_price=?, image=?, is_active=? WHERE id=?');
            $stmt->execute([$categoryId, $name, $slug, $description, $price, $discount, $imagePath, $isActive, $product['id']]);
            $pid = $product['id'];
        } else {
            $slug = unique_slug($pdo, 'products', $name);
            $stmt = $pdo->prepare('INSERT INTO products (category_id, name, slug, description, price, discount_price, image, is_active) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$categoryId, $name, $slug, $description, $price, $discount, $imagePath, $isActive]);
            $pid = $pdo->lastInsertId();
        }

        $pdo->prepare('DELETE FROM product_variants WHERE product_id=?')->execute([$pid]);
        $vStmt = $pdo->prepare('INSERT INTO product_variants (product_id, size, color, stock) VALUES (?,?,?,?)');
        foreach ($variantRows as $row) {
            $vStmt->execute([$pid, $row[0], $row[1], $row[2]]);
        }

        header('Location: /admin/index.php?saved=1');
        exit;
    }

    // Re-populate for re-render on error
    $product = $product ?? [];
    $product['name'] = $name;
    $product['category_id'] = $categoryId;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['discount_price'] = $discount;
    $product['is_active'] = $isActive;
    $product['image'] = $imagePath;
    $variants = [];
    foreach ($sizes as $i => $s) {
        $variants[] = ['size'=>$s, 'color'=>$colors[$i] ?? '', 'stock'=>$stocks[$i] ?? 0];
    }
}

$pageTitle = $id ? 'Редагувати товар' : 'Новий товар';
$activeNav = 'products';
include __DIR__ . '/../includes/admin-header.php';
?>
<h1><?= $id ? 'Редагувати товар' : 'Новий товар' ?></h1>

<?php foreach ($errors as $e): ?><div class="flash" style="background:#FBEAEA; border-color:#D99; color:#8A2E2E;"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="card">
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <div class="field">
      <label>Назва товару*</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
    </div>
    <div class="field">
      <label>Категорія*</label>
      <select name="category_id" required>
        <option value="">— оберіть —</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (($product['category_id'] ?? null) == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="field">
    <label>Опис</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
  </div>

  <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
    <div class="field">
      <label>Ціна (грн)*</label>
      <input type="number" step="1" name="price" required value="<?= htmlspecialchars((string)($product['price'] ?? '')) ?>">
    </div>
    <div class="field">
      <label>Ціна зі знижкою (грн)</label>
      <input type="number" step="1" name="discount_price" value="<?= htmlspecialchars((string)($product['discount_price'] ?? '')) ?>">
    </div>
    <div class="field">
      <label>Фото товару</label>
      <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
      <?php if (!empty($product['image'])): ?><div style="font-size:12.5px; color:#8C877D; margin-top:6px;">Поточне: <?= htmlspecialchars($product['image']) ?></div><?php endif; ?>
    </div>
  </div>

  <div class="field">
    <label><input type="checkbox" name="is_active" style="width:auto; margin-right:8px;" <?= (($product['is_active'] ?? 1) ? 'checked' : '') ?>> Показувати на сайті</label>
  </div>

  <hr style="border:none; border-top:1px solid #E4DFD1; margin:24px 0;">

  <label style="display:block; font-size:13px; color:#8C877D; margin-bottom:10px;">Варіанти (розмір / колір / залишок)</label>
  <div id="variantRows">
    <?php if ($variants): foreach ($variants as $v): ?>
      <div class="variant-row">
        <input type="text" name="variant_size[]" placeholder="Розмір (напр. M, 42)" value="<?= htmlspecialchars($v['size']) ?>">
        <input type="text" name="variant_color[]" placeholder="Колір" value="<?= htmlspecialchars($v['color']) ?>">
        <input type="number" name="variant_stock[]" placeholder="Залишок" value="<?= htmlspecialchars((string)$v['stock']) ?>">
        <button type="button" class="remove-row" onclick="this.parentElement.remove()">✕</button>
      </div>
    <?php endforeach; else: ?>
      <div class="variant-row">
        <input type="text" name="variant_size[]" placeholder="Розмір (напр. M, 42)">
        <input type="text" name="variant_color[]" placeholder="Колір">
        <input type="number" name="variant_stock[]" placeholder="Залишок">
        <button type="button" class="remove-row" onclick="this.parentElement.remove()">✕</button>
      </div>
    <?php endif; ?>
  </div>
  <button type="button" class="btn" onclick="addVariantRow()" style="margin-bottom:24px;">+ Додати варіант</button>

  <div>
    <button type="submit" class="btn btn-primary">Зберегти товар</button>
    <a href="/admin/index.php" class="btn" style="margin-left:10px;">Скасувати</a>
  </div>
</form>

<script>
function addVariantRow(){
  const wrap = document.getElementById('variantRows');
  const row = document.createElement('div');
  row.className = 'variant-row';
  row.innerHTML = `
    <input type="text" name="variant_size[]" placeholder="Розмір (напр. M, 42)">
    <input type="text" name="variant_color[]" placeholder="Колір">
    <input type="number" name="variant_stock[]" placeholder="Залишок">
    <button type="button" class="remove-row" onclick="this.parentElement.remove()">✕</button>
  `;
  wrap.appendChild(row);
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
