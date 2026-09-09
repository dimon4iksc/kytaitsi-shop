<?php
session_start();
require __DIR__ . '/includes/db.php';
$pdo = db();

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($name && $phone && $desc) {
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, city, np_branch, comment, items_json, total, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $phone, '', '', $desc, json_encode([], JSON_UNESCAPED_UNICODE), 0, 'custom_request']);
        $sent = true;
    }
}

$pageTitle = 'Індивідуальне замовлення — КИТАЙЦІ SHOP';
$active = 'custom';
include __DIR__ . '/includes/header.php';
?>
<style>
  .req-wrap{padding:64px 0 110px; max-width:640px; margin:0 auto;}
  .req-wrap h1{font-size:32px; margin-bottom:14px;}
  .req-wrap p.lead{color:var(--ink-soft); margin-bottom:36px;}
  .field{margin-bottom:18px;}
  .field label{display:block; font-size:13px; color:var(--stone); margin-bottom:6px;}
  .field input, .field textarea{
    width:100%; padding:12px 14px; border:1px solid var(--line); font-family:'Manrope',sans-serif; font-size:14.5px;
    background:#fff;
  }
  .success-box{background:#fff; border:1px solid var(--brass); padding:44px; text-align:center;}
</style>

<section class="wrap req-wrap">
  <?php if ($sent): ?>
    <div class="success-box">
      <h2 style="margin-bottom:12px;">Запит прийнято!</h2>
      <p>Ми знайдемо цей товар і зв'яжемось із вами щодо ціни та термінів.</p>
      <a href="/" class="btn btn-primary" style="margin-top:20px;">На головну</a>
    </div>
  <?php else: ?>
    <h1>Не знайшли потрібне?</h1>
    <p class="lead">Скиньте посилання, фото чи просто опишіть товар — знайдемо, домовимось з постачальником і привеземо особисто.</p>
    <form method="post">
      <div class="field"><label>Ім'я та прізвище*</label><input type="text" name="customer_name" required></div>
      <div class="field"><label>Телефон*</label><input type="tel" name="phone" required placeholder="+380"></div>
      <div class="field"><label>Опис товару / посилання*</label><textarea name="description" rows="5" required placeholder="Наприклад: посилання на товар, назва бренду, розмір, колір..."></textarea></div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Надіслати запит</button>
    </form>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
