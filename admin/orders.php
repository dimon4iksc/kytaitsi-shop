<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_admin();
$pdo = db();

if (isset($_GET['status_id'], $_GET['status_val'])) {
    $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$_GET['status_val'], (int)$_GET['status_id']]);
    header('Location: /admin/orders.php');
    exit;
}

$orders = $pdo->query("SELECT * FROM orders WHERE status != 'custom_request' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Замовлення';
$activeNav = 'orders';
include __DIR__ . '/../includes/admin-header.php';
?>
<h1>Замовлення</h1>

<?php if (!$orders): ?>
  <p style="color:#8C877D;">Замовлень поки немає.</p>
<?php else: ?>
<table>
  <thead><tr><th>#</th><th>Клієнт</th><th>Телефон</th><th>Місто / відділення</th><th>Товари</th><th>Сума</th><th>Статус</th><th>Дата</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): $items = json_decode($o['items_json'], true) ?: []; ?>
    <tr>
      <td>#<?= $o['id'] ?></td>
      <td><?= htmlspecialchars($o['customer_name']) ?><?php if ($o['comment']): ?><div style="font-size:12px; color:#8C877D;"><?= htmlspecialchars($o['comment']) ?></div><?php endif; ?></td>
      <td><?= htmlspecialchars($o['phone']) ?></td>
      <td><?= htmlspecialchars($o['city']) ?><?php if($o['np_branch']): ?>, відд. <?= htmlspecialchars($o['np_branch']) ?><?php endif; ?></td>
      <td>
        <?php foreach ($items as $it): ?>
          <div style="font-size:13px;"><?= htmlspecialchars($it['product_name']) ?> (<?= htmlspecialchars(trim(($it['size']?:'').' '.($it['color']?:''))) ?>) × <?= $it['qty'] ?></div>
        <?php endforeach; ?>
      </td>
      <td><?= money($o['total']) ?></td>
      <td>
        <form method="get" style="display:inline;">
          <input type="hidden" name="status_id" value="<?= $o['id'] ?>">
          <select name="status_val" onchange="this.form.submit()">
            <?php foreach (['new'=>'Нове','confirmed'=>'Підтверджено','shipped'=>'Відправлено','done'=>'Виконано','cancelled'=>'Скасовано'] as $k=>$v): ?>
              <option value="<?= $k ?>" <?= $o['status']===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </td>
      <td style="font-size:12.5px; color:#8C877D;"><?= htmlspecialchars($o['created_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
