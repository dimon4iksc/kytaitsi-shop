<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();
$pdo = db();

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM products WHERE id=?')->execute([(int)$_GET['delete']]);
    header('Location: /admin/index.php?deleted=1');
    exit;
}

$products = $pdo->query("
  SELECT p.*, c.name as cat_name,
    (SELECT SUM(stock) FROM product_variants WHERE product_id=p.id) as total_stock
  FROM products p JOIN categories c ON c.id=p.category_id
  ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Товари';
$activeNav = 'products';
include __DIR__ . '/../includes/admin-header.php';
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
  <h1 style="margin:0;">Товари</h1>
  <a href="/admin/product-edit.php" class="btn btn-primary">+ Додати товар</a>
</div>

<?php if (isset($_GET['saved'])): ?><div class="flash">Товар збережено.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="flash">Товар видалено.</div><?php endif; ?>

<table>
  <thead><tr><th>Назва</th><th>Категорія</th><th>Ціна</th><th>Залишок</th><th>Активний</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($products as $p): ?>
    <tr>
      <td><?= htmlspecialchars($p['name']) ?></td>
      <td><?= htmlspecialchars($p['cat_name']) ?></td>
      <td>
        <?php if ($p['discount_price']): ?>
          <span style="text-decoration:line-through; color:#8C877D;"><?= money($p['price']) ?></span> <?= money($p['discount_price']) ?>
        <?php else: ?>
          <?= money($p['price']) ?>
        <?php endif; ?>
      </td>
      <td class="<?= ((int)$p['total_stock'] <= 3) ? 'stock-low' : '' ?>"><?= (int)$p['total_stock'] ?> шт.</td>
      <td><?= $p['is_active'] ? 'Так' : 'Ні' ?></td>
      <td>
        <a href="/admin/product-edit.php?id=<?= $p['id'] ?>">Редагувати</a>
        &nbsp;·&nbsp;
        <a href="/admin/index.php?delete=<?= $p['id'] ?>" onclick="return confirm('Видалити товар?')" style="color:#6E1F2B;">Видалити</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
