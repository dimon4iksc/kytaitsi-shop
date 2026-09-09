<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name) {
        $slug = unique_slug($pdo, 'categories', $name);
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM categories')->fetchColumn();
        $pdo->prepare('INSERT INTO categories (name, slug, sort_order) VALUES (?,?,?)')->execute([$name, $slug, $maxOrder + 1]);
    }
    header('Location: /admin/categories.php?saved=1');
    exit;
}

if (isset($_GET['delete'])) {
    $cid = (int)$_GET['delete'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id=?');
    $stmt->execute([$cid]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$cid]);
        header('Location: /admin/categories.php?deleted=1');
    } else {
        header('Location: /admin/categories.php?blocked=1');
    }
    exit;
}

$categories = $pdo->query("
  SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id) as product_count
  FROM categories c ORDER BY sort_order
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Категорії';
$activeNav = 'categories';
include __DIR__ . '/../includes/admin-header.php';
?>
<h1>Категорії</h1>
<?php if (isset($_GET['saved'])): ?><div class="flash">Категорію додано.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="flash">Категорію видалено.</div><?php endif; ?>
<?php if (isset($_GET['blocked'])): ?><div class="flash" style="background:#FBEAEA; border-color:#D99; color:#8A2E2E;">Не можна видалити категорію, в якій є товари.</div><?php endif; ?>

<div class="card" style="max-width:420px;">
  <form method="post">
    <div class="field">
      <label>Нова категорія</label>
      <input type="text" name="name" required placeholder="Напр. Спортивний одяг">
    </div>
    <button type="submit" class="btn btn-primary">Додати категорію</button>
  </form>
</div>

<table>
  <thead><tr><th>Назва</th><th>Товарів</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($categories as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['name']) ?></td>
      <td><?= (int)$c['product_count'] ?></td>
      <td><a href="/admin/categories.php?delete=<?= $c['id'] ?>" onclick="return confirm('Видалити категорію?')" style="color:#6E1F2B;">Видалити</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
