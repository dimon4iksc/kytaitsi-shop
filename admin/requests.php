<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();
$pdo = db();

$requests = $pdo->query("SELECT * FROM orders WHERE status = 'custom_request' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Індивідуальні запити';
$activeNav = 'requests';
include __DIR__ . '/../includes/admin-header.php';
?>
<h1>Індивідуальні запити</h1>
<p style="color:#8C877D; margin-top:-14px; margin-bottom:24px;">Клієнти, які не знайшли товар у каталозі й лишили опис / посилання.</p>

<?php if (!$requests): ?>
  <p style="color:#8C877D;">Запитів поки немає.</p>
<?php else: ?>
<table>
  <thead><tr><th>Клієнт</th><th>Телефон</th><th>Опис запиту</th><th>Дата</th></tr></thead>
  <tbody>
  <?php foreach ($requests as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['customer_name']) ?></td>
      <td><?= htmlspecialchars($r['phone']) ?></td>
      <td><?= nl2br(htmlspecialchars($r['comment'])) ?></td>
      <td style="font-size:12.5px; color:#8C877D;"><?= htmlspecialchars($r['created_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
