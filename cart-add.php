<?php
session_start();
require __DIR__ . '/includes/db.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /'); exit; }

$variantId = (int)($_POST['variant_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

$stmt = $pdo->prepare("
  SELECT v.*, p.name as product_name, p.slug, p.price, p.discount_price
  FROM product_variants v JOIN products p ON p.id = v.product_id
  WHERE v.id = ?
");
$stmt->execute([$variantId]);
$variant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$variant) { header('Location: /catalog.php'); exit; }

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$key = (string)$variantId;
if (isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key]['qty'] += $qty;
} else {
    $_SESSION['cart'][$key] = [
        'variant_id' => $variantId,
        'product_name' => $variant['product_name'],
        'slug' => $variant['slug'],
        'size' => $variant['size'],
        'color' => $variant['color'],
        'price' => $variant['discount_price'] ?: $variant['price'],
        'qty' => $qty,
    ];
}

header('Location: /cart.php');
