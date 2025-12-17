<?php
session_start();

require_once __DIR__ . '/config/db.php';

function euro($n) {
  return '€' . number_format((float)$n, 2, '.', ',');
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
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
      'id'         => $pid,
      'title'      => $row['title'],
      'price'      => (float)$row['price'],
      'qty'        => $qty,
      'image_url'  => $row['image_url'],
      'line_total' => $lineTotal
    ];
  }
}

$shipping  = $subtotal > 0 ? 4.90 : 0.00;
$grandTotal = $subtotal + $shipping;

$errors = [];
$success = false;
$orderNumber = null;


$full_name = '';
$email = '';
$phone = '';
$country = '';
$city = '';
$address = '';
$zip = '';
$notes = '';
$payment_method = 'cod';

$card_name   = '';
$card_number = '';
$card_expiry = '';
$card_cvv    = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($items)) {
    $errors['cart'] = 'Your cart is empty.';
  } else {
    $full_name      = trim($_POST['full_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $country        = trim($_POST['country'] ?? '');
    $city           = trim($_POST['city'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $zip            = trim($_POST['zip'] ?? '');
    $notes          = trim($_POST['notes'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';


    if ($payment_method === 'card') {
      $card_name   = trim($_POST['card_name'] ?? '');
      $card_number = preg_replace('/\D+/', '', $_POST['card_number'] ?? '');
      $card_expiry = trim($_POST['card_expiry'] ?? '');
      $card_cvv    = trim($_POST['card_cvv'] ?? '');
    }

    if ($full_name === '') {
      $errors['full_name'] = 'Full name is required.';
    }

    if ($email === '') {
      $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Please enter a valid email address.';
    }

    if ($address === '') {
      $errors['address'] = 'Address is required.';
    }

    if ($city === '') {
      $errors['city'] = 'City is required.';
    }

    if ($zip === '') {
      $errors['zip'] = 'ZIP / Postal code is required.';
    }

    if ($payment_method === 'card') {
      if ($card_name === '') {
        $errors['card_name'] = 'Name on card is required.';
      }
      if ($card_number === '' || strlen($card_number) < 12) {
        $errors['card_number'] = 'Enter a valid card number.';
      }
      if ($card_expiry === '') {
        $errors['card_expiry'] = 'Expiry date is required.';
      }
      if ($card_cvv === '' || strlen($card_cvv) < 3) {
        $errors['card_cvv'] = 'CVV is required.';
      }
    }

    if (empty($errors)) {
      try {
        $pdo->beginTransaction();

        $orderStmt = $pdo->prepare("
          INSERT INTO orders (
            customer_name,
            email,
            phone,
            country,
            city,
            address,
            postal_code,
            notes,
            total_price
          ) VALUES (
            :full_name,
            :email,
            :phone,
            :country,
            :city,
            :address,
            :postal_code,
            :notes,
            :total_price
          )
        ");

        $orderStmt->execute([
          ':full_name'      => $full_name,
          ':email'          => $email,
          ':phone'          => $phone,
          ':country'        => $country,
          ':city'           => $city,
          ':address'        => $address,
          ':postal_code'    => $zip,
          ':notes'          => $notes,
          ':total_price'    => $grandTotal,
        ]);

        $orderId = $pdo->lastInsertId();

        $orderNumber = 'RB-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);

        $itemStmt = $pdo->prepare("
          INSERT INTO order_items (
            order_id,
            product_id,
            qty,
            unit_price,
            line_total
          ) VALUES (
            :order_id,
            :product_id,
            :qty,
            :unit_price,
            :line_total
          )
        ");

        foreach ($items as $it) {
          $itemStmt->execute([
            ':order_id'   => $orderId,
            ':product_id' => $it['id'],
            ':qty'        => $it['qty'],
            ':unit_price' => $it['price'],
            ':line_total' => $it['line_total'],
          ]);
        }

        $successName = $full_name;

        $pdo->commit();

        $_SESSION['cart'] = [];
        $success = true;

        $items      = [];
        $subtotal   = 0.0;
        $shipping   = 0.0;
        $grandTotal = 0.0;

        $full_name = $email = $phone = $country = $city = $address = $zip = $notes = '';
        $payment_method = 'cod';
        $card_name = $card_number = $card_expiry = $card_cvv = '';

      } catch (Exception $e) {
        if ($pdo->inTransaction()) {
          $pdo->rollBack();
        }
        $errors['general'] = 'Something went wrong while saving your order. Please try again.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout • RetroByte</title>

    <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/checkout.css">

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

  <section id="checkout">
    <div class="container" style="margin-top:40px;">
      <h1 class="title" style="margin-top:20px;">Checkout</h1>
      <p class="subtitle">Almost there! Fill in your details to complete your order.</p>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <strong>Order placed!</strong><br>
          Thank you, <?php echo htmlspecialchars($successName); ?>.<br>
          Your reference number is <strong><?php echo htmlspecialchars($orderNumber); ?></strong>.<br>
          <br>
          <div style="display:flex;">
            <a href="shop.php" class="pixel-button btn-primary">Browse more products</a>
          </div>
        </div>

      <?php elseif (!empty($errors['cart'])): ?>
        <div class="alert alert-error">
          <?php echo htmlspecialchars($errors['cart']); ?>
          <div style="display:flex;">
            <a href="cart.php" class="pixel-button btn-ghost">Back to Cart</a>
          </div>
        </div>

      <?php elseif (empty($items)): ?>
        <div class="empty-checkout">
          <p>Your cart is currently empty. Add some retro goodies before checking out.</p>
          <div style="margin-top:10px;">
            <a href="shop.php" class="pixel-button btn-primary">Browse Products</a>
          </div>
        </div>

      <?php else: ?>

        <?php if (!empty($errors['general'])): ?>
          <div class="alert alert-error">
            <?php echo htmlspecialchars($errors['general']); ?>
          </div>
        <?php endif; ?>

        <div class="checkout-layout">
          <div class="checkout-card">
            <h2>Billing &amp; Shipping</h2>

            <form method="POST" novalidate>
              <div class="form-group">
                <label for="full_name">Full Name</label>
                <input
                  type="text"
                  id="full_name"
                  name="full_name"
                  class="pixel-input"
                  required
                  placeholder="ex. John Doe"
                  value="<?php echo htmlspecialchars($full_name); ?>"
                >
                <?php if (!empty($errors['full_name'])): ?>
                  <div class="form-error"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                <?php endif; ?>
              </div>

              <div class="field-row">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    class="pixel-input"
                    required
                    placeholder="you@example.com"
                    value="<?php echo htmlspecialchars($email); ?>"
                  >
                  <?php if (!empty($errors['email'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['email']); ?></div>
                  <?php endif; ?>
                </div>

                <div class="form-group">
                  <label for="phone">Phone (optional)</label>
                  <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="pixel-input"
                    placeholder="+xx xxx xxx..."
                    value="<?php echo htmlspecialchars($phone); ?>"
                  >
                </div>
              </div>

              <div class="field-row">
                <div class="form-group">
                  <label for="country">Country</label>
                  <input
                    type="text"
                    id="country"
                    name="country"
                    class="pixel-input"
                    placeholder="country"
                    value="<?php echo htmlspecialchars($country); ?>"
                  >
                </div>

                <div class="form-group">
                  <label for="city">City</label>
                  <input
                    type="text"
                    id="city"
                    name="city"
                    class="pixel-input"
                    required
                    placeholder="city"
                    value="<?php echo htmlspecialchars($city); ?>"
                  >
                  <?php if (!empty($errors['city'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['city']); ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="form-group">
                <label for="address">Address</label>
                <input
                  type="text"
                  id="address"
                  name="address"
                  class="pixel-input"
                  required
                  placeholder="Street, number, floor"
                  value="<?php echo htmlspecialchars($address); ?>"
                >
                <?php if (!empty($errors['address'])): ?>
                  <div class="form-error"><?php echo htmlspecialchars($errors['address']); ?></div>
                <?php endif; ?>
              </div>

              <div class="field-row">
                <div class="form-group">
                  <label for="zip">ZIP / Postal Code</label>
                  <input
                    type="text"
                    id="zip"
                    name="zip"
                    class="pixel-input"
                    required
                    placeholder="12345"
                    value="<?php echo htmlspecialchars($zip); ?>"
                  >
                  <?php if (!empty($errors['zip'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['zip']); ?></div>
                  <?php endif; ?>
                </div>

                <div class="form-group">
                  <label for="notes">Order Notes</label>
                  <textarea
                    id="notes"
                    name="notes"
                    class="pixel-input"
                    rows="3"
                    placeholder="Any special instructions?"
                  ><?php echo htmlspecialchars($notes); ?></textarea>
                </div>
              </div>

              <div class="form-group">
                <label>Payment Method</label>
                <div class="payment-options">
                  <label>
                    <input
                      type="radio"
                      name="payment_method"
                      value="cod"
                      class="radio-btn"
                      <?php echo $payment_method === 'cod' ? 'checked' : ''; ?>
                    >
                    Cash on Delivery
                  </label>
                  <label>
                    <input
                      type="radio"
                      name="payment_method"
                      value="card"
                      class="radio-btn"
                      <?php echo $payment_method === 'card' ? 'checked' : ''; ?>
                    >
                    Card Payment
                  </label>
                </div>
              </div>

              
              <div class="rb-card-wrapper" id="rb-card-wrapper">
                <div class="rb-card-preview">
                  <div class="rb-card-row rb-card-row-top">
                    <span>RETROCARD</span>
                    <div class="rb-card-chip" aria-hidden="true"></div>
                  </div>

                  <div class="rb-card-row rb-card-number-row">
                    <span class="rb-card-number-value" id="rb-card-number-display">
                      •••• •••• •••• ••••
                    </span>
                  </div>

                  <div class="rb-card-row rb-card-row-bottom">
                    <div class="rb-card-block">
                      <span class="rb-card-label">Cardholder</span>
                      <span class="rb-card-value" id="rb-card-name-display">YOUR NAME</span>
                    </div>
                    <div class="rb-card-block">
                      <span class="rb-card-label">Expires</span>
                      <span class="rb-card-value" id="rb-card-exp-display">MM/YY</span>
                    </div>
                  </div>
                </div>

                <div class="rb-card-fields">
                  <div class="form-group">
                    <label for="card_name">Name on card</label>
                    <input
                      type="text"
                      id="card_name"
                      name="card_name"
                      class="pixel-input"
                      placeholder="ex. John Doe"
                    >
                  </div>

                  <div class="form-group">
                    <label for="card_number">Card number</label>
                    <input
                      type="text"
                      id="card_number"
                      name="card_number"
                      class="pixel-input"
                      inputmode="numeric"
                      autocomplete="off"
                      placeholder="•••• •••• •••• ••••"
                    >
                  </div>

                  <div class="field-row">
                    <div class="form-group">
                      <label for="card_expiry">Expiry</label>
                      <input
                        type="text"
                        id="card_expiry"
                        name="card_expiry"
                        class="pixel-input"
                        placeholder="MM/YY"
                        inputmode="numeric"
                        autocomplete="off"
                      >
                    </div>

                    <div class="form-group">
                      <label for="card_cvv">CVV</label>
                      <input
                        type="password"
                        id="card_cvv"
                        name="card_cvv"
                        class="pixel-input"
                        maxlength="3"
                        inputmode="numeric"
                        autocomplete="off"
                        placeholder="•••"
                      >
                    </div>
                  </div>
                  
                </div>
              </div>

              

              <div class="checkout-actions">
                <a href="cart.php" class="pixel-button btn-ghost">Back to Cart</a>
                <button type="submit" class="pixel-button btn-primary">
                  Place Order
                </button>
              </div>
            </form>
          </div>

          <div class="summary-card">
            <h2>Order Summary</h2>

            <div class="order-items">
              <?php foreach ($items as $it): ?>
                <div class="order-item">
                  <div class="order-item-thumb">
                    <img src="<?php echo htmlspecialchars($it['image_url']); ?>"
                         alt="<?php echo htmlspecialchars($it['title']); ?>"
                         loading="lazy">
                  </div>
                  <div>
                    <div class="order-item-title">
                      <a href="product.php?id=<?php echo (int)$it['id']; ?>">
                        <?php echo htmlspecialchars($it['title']); ?>
                      </a>
                    </div>
                    <div class="order-item-meta">
                      Qty: <?php echo (int)$it['qty']; ?>
                      • <?php echo euro($it['price']); ?>
                    </div>
                  </div>
                  <div class="order-item-price">
                    <?php echo euro($it['line_total']); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="summary-totals">
              <div class="summary-row">
                <span>Subtotal</span>
                <span><?php echo euro($subtotal); ?></span>
              </div>
              <div class="summary-row">
                <span>Shipping</span>
                <span><?php echo euro($shipping); ?></span>
              </div>
              <div class="summary-row">
                <strong>Total</strong>
                <strong><?php echo euro($grandTotal); ?></strong>
              </div>
            </div>
          </div>
        </div>

      <?php endif; ?>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

    <script>
    (function () {
      const radios = document.querySelectorAll('input[name="payment_method"]');
      const wrapper = document.getElementById('rb-card-wrapper');
      if (!radios.length || !wrapper) return;

      function updateVisibility() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (selected && selected.value === 'card') {
          wrapper.classList.add('is-visible');
        } else {
          wrapper.classList.remove('is-visible');
        }
      }

      radios.forEach(r => r.addEventListener('change', updateVisibility));
      updateVisibility();

      const nameInput = document.getElementById('card_name');
      const numInput  = document.getElementById('card_number');
      const expInput  = document.getElementById('card_expiry');

      const nameDisp = document.getElementById('rb-card-name-display');
      const numDisp  = document.getElementById('rb-card-number-display');
      const expDisp  = document.getElementById('rb-card-exp-display');

      function formatNumber(value) {
        const digits = value.replace(/\D/g, '').slice(0, 16);
        return digits.replace(/(.{4})/g, '$1 ').trim();
      }

      if (numInput && numDisp) {
        numInput.addEventListener('input', () => {
          const formatted = formatNumber(numInput.value);
          numInput.value = formatted;
          numDisp.textContent = formatted || '•••• •••• •••• ••••';
        });
      }

      if (nameInput && nameDisp) {
        nameInput.addEventListener('input', () => {
          const v = nameInput.value.trim();
          nameDisp.textContent = v || 'YOUR NAME';
        });
      }

      if (expInput && expDisp) {
        expInput.addEventListener('input', () => {
          let v = expInput.value.replace(/\D/g, '').slice(0, 4);
          if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
          expInput.value = v;
          expDisp.textContent = v || 'MM/YY';
        });
      }
    })();
  </script>

</body>
</html>
