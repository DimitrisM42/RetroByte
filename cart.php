<?php
// cart.php
session_start();

$isAjax = (
  !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) || ($_POST['ajax'] ?? null) === '1' || ($_GET['ajax'] ?? null) === '1';


require_once __DIR__ . '/config/db.php';

function euro($n) {
  return '€' . number_format((float)$n, 2, '.', ',');
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? null;

if ($action === 'update') {
  $qtyMap = $_POST['qty'] ?? [];
  foreach ($qtyMap as $pid => $qty) {
    $pid = (int)$pid;
    $qty = max(0, (int)$qty);
    if ($qty === 0) unset($_SESSION['cart'][$pid]);
    else $_SESSION['cart'][$pid] = $qty;
  }

  if ($isAjax) {
    $cart = $_SESSION['cart'];
    $ids = array_map('intval', array_keys($cart));
    $itemsTotals = [];
    $subtotal = 0.0;

    if (!empty($ids)) {
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
      $stmt->execute($ids);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $r) {
        $pid = (int)$r['id'];
        $qty = (int)$cart[$pid];
        $line = $qty * (float)$r['price'];
        $itemsTotals[$pid] = $line;
        $subtotal += $line;
      }
    }

    $tax = 0.0; $shipping = 0.0;
    $grand = $subtotal + $tax + $shipping;
    $count = array_sum($_SESSION['cart'] ?? []);

    header('Content-Type: application/json');
    echo json_encode([
      'ok' => true,
      'count' => $count,
      'subtotal' => $subtotal,
      'grand' => $grand,
      'items' => $itemsTotals
    ]);
    exit;
  }

  header('Location: cart.php'); exit;
}


if ($action === 'remove') {
  $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  unset($_SESSION['cart'][$pid]);
  header('Location: cart.php'); exit;
}

if ($action === 'clear') {
  $_SESSION['cart'] = [];
  header('Location: cart.php'); exit;
}

if (isset($_REQUEST['add'])) {
  $pid = (int)$_REQUEST['add'];
  $qty = isset($_REQUEST['qty']) ? max(1, (int)$_REQUEST['qty']) : 1;
  $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + $qty;

  if ($isAjax) {
    $count = array_sum($_SESSION['cart'] ?? []);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'count' => $count]);
    exit;
  }

  header('Location: cart.php'); exit;
}


$cart = $_SESSION['cart'];
$items = [];
$subtotal = 0.0;

if (!empty($cart)) {
  $ids = array_map('intval', array_keys($cart));
  $placeholders = implode(',', array_fill(0, count($ids), '?'));

  $sql = "
    SELECT p.id, p.title, p.short_desc, p.price, p.tag,
       COALESCE(pi.url, 'assets/images/RetroByteLogo.png') AS image_url
        FROM products p
        LEFT JOIN (
          SELECT i1.product_id, i1.id
          FROM product_images i1
          JOIN (
            SELECT product_id, MIN(sort_order) AS min_sort
            FROM product_images
            GROUP BY product_id
          ) m ON m.product_id = i1.product_id AND i1.sort_order = m.min_sort
          JOIN (
            SELECT product_id, MIN(id) AS min_id
            FROM product_images
            GROUP BY product_id
          ) m2 ON m2.product_id = i1.product_id AND i1.id = m2.min_id
        ) first ON first.product_id = p.id
        LEFT JOIN product_images pi ON pi.id = first.id
        WHERE p.id IN ($placeholders)
        ORDER BY p.id DESC;
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($ids);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($rows as $row) {
    $pid = (int)$row['id'];
    $qty = (int)($cart[$pid] ?? 0);
    if ($qty <= 0) continue;
    $lineTotal = $qty * (float)$row['price'];
    $subtotal += $lineTotal;

    $items[] = [
      'id' => $pid,
      'title' => $row['title'],
      'price' => (float)$row['price'],
      'qty' => $qty,
      'image_url' => $row['image_url'],
      'line_total' => $lineTotal
    ];
  }
}

$taxRate = 0.00;
$tax = $subtotal * $taxRate;
$shipping = 0.00;
$grandTotal = $subtotal + $tax + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>

    <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/cart.css">


    <!-- icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">

    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
   
    <!-- js -->
    <script src="assets/js/app.js" defer></script>

</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <canvas id="grid"></canvas>

    <section style="height: 20px;"></section>


    <section id="cart">

        <div class="container" style="margin-top:40px;">
            <h1 class="title" style="margin-top:20px;">Your Cart</h1>
            <p class="subtitle">Review your items and proceed to checkout.</p>

            <?php if (empty($items)): ?>
            <div class="empty">
                <p>Your cart is currently empty.</p>
                <a href="shop.php" class="pixel-button btn-primary">Browse Products</a>
            </div>
            <?php else: ?>

            <form method="POST" id="cart-form">
                <input type="hidden" name="action" value="update"/>

                <div class="cart-actions" style="margin-bottom:14px;">
                <a href="shop.php" class="pixel-button btn-ghost">Continue Shopping</a>
                <button type="submit"
                        class="pixel-button btn-ghost"
                        formaction="cart.php"
                        onclick="return confirm('Clear all items from your cart?');"
                        name="action" value="clear">
                    Clear Cart
                </button>
                </div>

                <table class="cart-table">
                <thead>
                    <tr>
                    <th colspan="2">Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th style="text-align:right;">Total</th>
                    <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                    <tr class="cart-row">
                        <td style="width:84px;">
                          <img src="<?= htmlspecialchars($it['image_url']) ?>" alt="<?= htmlspecialchars($it['title']) ?>" class="thumb" loading="lazy">
                        </td>
                        <td>
                        <div class="product-title">
                            <a href="product.php?id=<?= (int)$it['id'] ?>">
                            <?= htmlspecialchars($it['title']) ?>
                            </a>
                        </div>
                        
                        </td>
                        <td class="price" data-col="Price"><?= euro($it['price']) ?></td>
                        <td data-col="Quantity">
                          <div class="qty-wrap">
                            <button class="qty-btn" data-step="-1" type="button"><i class="hn hn-minus-solid"></i></button>
                            <input class="qty-input" type="number" min="0" step="1" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>">
                            <button class="qty-btn" data-step="1" type="button"><i class="hn hn-plus-solid"></i></button>
                          </div>
                        </td>
                        <td class="line-total" data-id="<?= (int)$it['id'] ?>" data-col="Total" style="text-align:right;">
                          <?= euro($it['line_total']) ?>
                        </td>

                        <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Remove this item?');">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
                            <button type="submit" class="pixel-button btn-ghost">Remove</button>
                        </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>

                <section class="totals">
                  <div class="row sub"><span>Subtotal</span><span><?= euro($subtotal) ?></span></div>
                  <div class="row muted"><span>Shipping</span><span>Calculated at checkout</span></div>
                  <div class="row grand"><span>Grand Total</span><span><?= euro($grandTotal) ?></span></div>

                  <div style="margin-top:12px; display:flex; gap:12px; justify-content:flex-end;">
                    <a href="checkout.php" class="pixel-button btn-primary">Proceed to Checkout</a>
                  </div>
                </section>
            </form>
            <?php endif; ?>
        </div>
    </section>

  <?php include 'includes/footer.php'; ?>

  <script>
    (function () {
      const fmt = new Intl.NumberFormat('el-GR', { style: 'currency', currency: 'EUR' });
      const form = document.getElementById('cart-form');
      const cartBadge = document.getElementById('cart-count');
      const qtyInputs = document.querySelectorAll('.qty-input');

      function updateBadge(count) {
        if (!cartBadge) return;
        cartBadge.textContent = count;
        if (count > 0) {
          cartBadge.classList.remove('is-hidden');
          cartBadge.classList.add('bump');
          setTimeout(() => cartBadge.classList.remove('bump'), 500);
        } else {
          cartBadge.classList.add('is-hidden');
        }
      }

      function applyTotals(data) {

        document.querySelectorAll('.line-total[data-id]').forEach(td => {
          const pid = Number(td.dataset.id);
          if (data.items && data.items[pid] != null) td.textContent = fmt.format(data.items[pid]);
        });

        const subtotalEl = document.querySelector('.totals .row:nth-child(1) span:last-child');
        const grandEl = document.querySelector('.totals .grand span:last-child');
        if (subtotalEl) subtotalEl.textContent = fmt.format(data.subtotal || 0);
        if (grandEl) grandEl.textContent = fmt.format(data.grand || 0);

        updateBadge(data.count || 0);
      }

      let timer = null;
      async function sendUpdate() {
        const fd = new FormData(form);
        fd.set('action', 'update');
        fd.set('ajax', '1');
        const res = await fetch('cart.php', {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json().catch(() => ({}));
        if (data && data.ok) applyTotals(data);
      }

      function triggerUpdate() {
        if (timer) clearTimeout(timer);
        timer = setTimeout(sendUpdate, 250);
      }

      document.querySelectorAll('.qty-wrap').forEach(wrap => {
        const input = wrap.querySelector('.qty-input');
        wrap.addEventListener('click', e => {
          const btn = e.target.closest('.qty-btn');
          if (!btn) return;
          const step = parseInt(btn.getAttribute('data-step') || '0', 10);
          const current = parseInt(input.value || '0', 10);
          const next = Math.max(0, current + step);
          input.value = next;
          triggerUpdate();
        });
      });

      qtyInputs.forEach(inp => {
        inp.addEventListener('change', triggerUpdate);
        inp.addEventListener('blur', () => {
          const v = parseInt(inp.value || '0', 10);
          inp.value = Math.max(0, isNaN(v) ? 0 : v);
          triggerUpdate();
        });
        inp.addEventListener('keydown', e => {
          if (e.key === 'Enter') { e.preventDefault(); inp.blur(); }
        });
      });
    })();
  </script>
</body>
</html>
