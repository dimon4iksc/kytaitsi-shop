<?php
session_start();
require __DIR__ . '/includes/db.php';
$pdo = db();

// Handle remove / qty update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $key = $_POST['key'] ?? '';
    if ($_POST['action'] === 'remove' && isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    } elseif ($_POST['action'] === 'update' && isset($_SESSION['cart'][$key])) {
        $newQty = max(1, (int)($_POST['qty'] ?? 1));
        $_SESSION['cart'][$key]['qty'] = $newQty;
    }
    header('Location: /cart.php');
    exit;
}

// Handle checkout submit
$orderSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $branch = trim($_POST['np_branch'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($name && $phone && !empty($_SESSION['cart'])) {
        $items = array_values($_SESSION['cart']);
        $total = 0;
        foreach ($items as $it) $total += $it['price'] * $it['qty'];

        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, city, np_branch, comment, items_json, total) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$name, $phone, $city, $branch, $comment, json_encode($items, JSON_UNESCAPED_UNICODE), $total]);

        $_SESSION['cart'] = [];
        $orderSuccess = true;
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $it) $total += $it['price'] * $it['qty'];

$pageTitle = 'Кошик — КИТАЙЦІ SHOP';
include __DIR__ . '/includes/header.php';
?>
<style>
  .cart-wrap{padding:56px 0 100px;}
  .cart-wrap h1{font-size:32px; margin-bottom:36px;}
  .cart-layout{display:grid; grid-template-columns:1.4fr 1fr; gap:56px; align-items:start;}
  table.cart-table{width:100%; border-collapse:collapse;}
  table.cart-table th{text-align:left; font-size:12.5px; color:var(--stone); font-weight:600; padding-bottom:14px; border-bottom:1px solid var(--line);}
  table.cart-table td{padding:18px 0; border-bottom:1px solid var(--line); font-size:14.5px; vertical-align:middle;}
  .item-name{font-family:'Fraunces',serif; font-weight:600; font-size:15.5px; margin-bottom:4px;}
  .item-opts{font-size:13px; color:var(--stone);}
  .qty-form{display:inline-flex; align-items:center; gap:8px;}
  .qty-form input{width:44px; text-align:center; border:1px solid var(--line); padding:6px 0; font-family:'Manrope',sans-serif;}
  .remove-link{color:var(--burgundy); font-size:13px; border:none; background:none; cursor:pointer; font-family:'Manrope',sans-serif; padding:0;}
  .cart-summary{background:#fff; border:1px solid var(--line); padding:28px;}
  .cart-summary h3{font-size:18px; margin-bottom:20px;}
  .summary-row{display:flex; justify-content:space-between; font-size:14.5px; margin-bottom:12px;}
  .summary-row.total{font-size:19px; font-family:'Fraunces',serif; font-weight:600; border-top:1px solid var(--line); padding-top:16px; margin-top:6px;}
  .field{margin-bottom:16px;}
  .field label{display:block; font-size:13px; color:var(--stone); margin-bottom:6px;}
  .field input, .field textarea{
    width:100%; padding:11px 12px; border:1px solid var(--line); font-family:'Manrope',sans-serif; font-size:14.5px;
    background:var(--ivory);
  }
  .empty-cart{padding:60px 0; text-align:center; color:var(--stone);}
  .success-box{
    background:#fff; border:1px solid var(--brass); padding:48px; text-align:center; max-width:560px; margin:0 auto;
  }
  .success-box h2{margin-bottom:14px;}
</style>

<section class="wrap cart-wrap">
  <?php if ($orderSuccess): ?>
    <div class="success-box">
      <h2>Замовлення прийнято!</h2>
      <p>Ми зв'яжемось із вами найближчим часом для підтвердження. Дякуємо за замовлення в КИТАЙЦІ SHOP.</p>
      <a href="/catalog.php" class="btn btn-primary" style="margin-top:22px;">Продовжити покупки</a>
    </div>
  <?php else: ?>
    <h1>Кошик</h1>
    <?php if (!$cart): ?>
      <div class="empty-cart">Кошик порожній. <a href="/catalog.php">Перейти до каталогу →</a></div>
    <?php else: ?>
      <div class="cart-layout">
        <div>
          <table class="cart-table">
            <thead><tr><th>Товар</th><th>Ціна</th><th>Кількість</th><th>Разом</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($cart as $key => $it): ?>
              <tr>
                <td>
                  <div class="item-name"><a href="/product.php?slug=<?= urlencode($it['slug']) ?>"><?= htmlspecialchars($it['product_name']) ?></a></div>
                  <div class="item-opts"><?= htmlspecialchars(trim(($it['size']?:'').' '.($it['color']?:''))) ?></div>
                </td>
                <td><?= money($it['price']) ?></td>
                <td>
                  <form class="qty-form" method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                    <input type="text" name="qty" value="<?= $it['qty'] ?>" onchange="this.form.submit()">
                  </form>
                </td>
                <td><?= money($it['price'] * $it['qty']) ?></td>
                <td>
                  <form method="post">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                    <button type="submit" class="remove-link">Прибрати</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="cart-summary">
          <h3>Оформлення замовлення</h3>
          <div class="summary-row"><span>Товарів</span><span><?= array_sum(array_column($cart,'qty')) ?></span></div>
          <div class="summary-row total"><span>Разом</span><span><?= money($total) ?></span></div>
          <form method="post" style="margin-top:22px;">
            <input type="hidden" name="place_order" value="1">
            <div class="field"><label>Ім'я та прізвище*</label><input type="text" name="customer_name" required></div>
            <div class="field"><label>Телефон*</label><input type="tel" name="phone" required placeholder="+380"></div>
            <div class="field"><label>Місто</label><input type="text" name="city"></div>
            <div class="field"><label>Відділення Нової Пошти</label><input type="text" name="np_branch"></div>
            <div class="field"><label>Коментар</label><textarea name="comment" rows="3"></textarea></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Підтвердити замовлення</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
