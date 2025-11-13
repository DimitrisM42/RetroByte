<?php
require __DIR__ . '/config/db.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
if (!$id && !$slug) {
  http_response_code(404);
  exit('Product not found');
}

// Product
$sql = "SELECT id, title, category, short_desc, description, price, year_made, tag
        FROM products
        WHERE " . ($id ? "id = :val" : "REPLACE(LOWER(title),' ','-') = :val") . "
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':val', $id ?: strtolower(str_replace(' ', '-', $slug)), $id ? PDO::PARAM_INT : PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch();

if (!$row) {
  http_response_code(404);
  exit('Product not found');
}

// Images
$imgStmt = $pdo->prepare("SELECT url, alt_text FROM product_images WHERE product_id = :pid ORDER BY sort_order ASC, id ASC");
$imgStmt->execute([':pid' => $row['id']]);
$imgRows = $imgStmt->fetchAll();

$images = [];
foreach ($imgRows as $r) {
  $images[] = $r['url'];
}
if (!$images) {
  $images = ['assets/images/placeholder.png'];
}

// Specs
$specStmt = $pdo->prepare("SELECT spec_key, spec_value FROM product_specs WHERE product_id = :pid ORDER BY sort_order ASC, id ASC");
$specStmt->execute([':pid' => $row['id']]);
$specRows = $specStmt->fetchAll();

// meta
$metaParts = [];
if (!empty($row['category']))   $metaParts[] = $row['category'];
if (!empty($row['year_made']))  $metaParts[] = 'Year: ' . (int)$row['year_made'];
$meta = $metaParts ? implode(' • ', $metaParts) : '';

$product = [
  'id'          => (int)$row['id'],
  'title'       => $row['title'],
  'meta'        => $meta,
  'short'       => $row['short_desc'] ?? '',
  'description' => $row['description'] ?? '',
  'price'       => (float)$row['price'],
  'images'      => $images,
  'specs'       => $specRows,     
  'tag'         => $row['tag'] ?? null,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($product['title']); ?> • RetroByte</title>

  <!-- favicon -->
  <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

  <!-- css -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/product.css">

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
  <section style="height: 10px;"></section>

    <div class="container">
        <a href="#" class="pixel-button btn-ghost back-btn" onclick="history.back(); return false;">
            <i class="hn hn-arrow-left"></i> Back
        </a>
    </div>


  <section class="container product-grid">
    <!-- Gallery -->
    <div class="product-start">
      <div class="product-image">
        <img id="main-img" src="<?php echo htmlspecialchars($product['images'][0]); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
      </div>

      <div class="thumbs">
        <?php foreach ($product['images'] as $idx => $img): ?>
          <button class="thumb <?php echo $idx===0 ? 'is-active' : ''; ?>" data-src="<?php echo htmlspecialchars($img); ?>" aria-label="Image <?php echo $idx+1; ?>">
            <img src="<?php echo htmlspecialchars($img); ?>" alt="">
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Info -->
    <div class="product-info">
      <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>

      <?php if (!empty($product['meta'])): ?>
        <p class="product-meta"><?php echo htmlspecialchars($product['meta']); ?></p>
      <?php endif; ?>

      <?php if (!empty($product['short'])): ?>
        <p class="product-description"><?php echo htmlspecialchars($product['short']); ?></p>
      <?php endif; ?>

      <!-- Tabs -->
      <div class="tabs">
        <button class="pixel-button tab is-active" data-tab="desc">Description</button>
        <button class="pixel-button tab" data-tab="specs">Specifications</button>
      </div>

      <div class="tab-panels">
        <div class="tab-panel is-active" id="desc">
          <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        <div class="tab-panel" id="specs">
          <ul class="specs-list">
            <?php if ($product['specs']): ?>
              <?php foreach ($product['specs'] as $s): ?>
                <li><strong><?php echo htmlspecialchars($s['spec_key']); ?>:</strong> <?php echo htmlspecialchars($s['spec_value']); ?></li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>No specifications available.</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <!-- Buy Box -->
      <div class="buy-box">
        <div class="price">
            <span class="amount">€<?php echo number_format($product['price'], 2); ?></span>
        </div>

        <form action="cart.php" method="post" class="product-pqa" id="add-form">
            
            <input type="hidden" name="add" value="<?php echo (int)$product['id']; ?>">

            <div class="qty">
            <span>Qty</span>
            <div class="qty-wrapper">
                <button type="button" class="qty-btn minus" aria-label="Decrease quantity">
                <i class="hn hn-minus-solid"></i>
                </button>

                <input type="number" min="1" max="8" value="1"
                    class="pixel-input" id="qty" name="qty"
                    inputmode="numeric" pattern="[0-9]*">

                <button type="button" class="qty-btn plus" aria-label="Increase quantity">
                <i class="hn hn-plus-solid"></i>
                </button>
            </div>
            </div>

            <button type="submit" id="add-to-cart" class="pixel-button btn-primary">
            <i class="hn hn-shopping-cart-solid"></i> Add to cart
            </button>
        </form>
        </div>

    </div>
  </section>

  <section style="height: 30px"></section>
  <?php include 'includes/footer.php'; ?>

  <script>

    (function () {
      const main = document.getElementById('main-img');
      const thumbs = document.querySelectorAll('.thumb');
      thumbs.forEach(btn => {
        btn.addEventListener('click', () => {
          thumbs.forEach(b => b.classList.remove('is-active'));
          btn.classList.add('is-active');
          main.src = btn.dataset.src;
        });
      });
    })();

    (function () {
      const tabs = document.querySelectorAll('.tab');
      const panels = document.querySelectorAll('.tab-panel');
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('is-active'));
          panels.forEach(p => p.classList.remove('is-active'));
          tab.classList.add('is-active');
          document.getElementById(tab.dataset.tab).classList.add('is-active');
        });
      });
    })();

    (function () {
        const form = document.getElementById('add-form');
        const qtyInput = document.getElementById('qty');
        const plus = document.querySelector('.qty-btn.plus');
        const minus = document.querySelector('.qty-btn.minus');
        const cartBadge = document.getElementById('cart-count');

        const clamp = (val, min, max) => Math.max(min, Math.min(max, val));
        qtyInput.addEventListener('input', () => {
            const min = parseInt(qtyInput.min) || 1;
            const max = parseInt(qtyInput.max) || 99;
            let v = parseInt(String(qtyInput.value).replace(/\D/g, '')) || min;
            qtyInput.value = clamp(v, min, max);
        });
        plus.addEventListener('click', () => {
            const max = parseInt(qtyInput.max) || 99;
            let v = parseInt(qtyInput.value) || 1;
            if (v < max) qtyInput.value = v + 1;
        });
        minus.addEventListener('click', () => {
            const min = parseInt(qtyInput.min) || 1;
            let v = parseInt(qtyInput.value) || 1;
            if (v > min) qtyInput.value = v - 1;
        });
        qtyInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
            e.preventDefault();
            form.requestSubmit();
            }
        });

        function showToast(text) {
            let toast = document.getElementById('rb-toast');
            if (!toast) {
            toast = document.createElement('div');
            toast.id = 'rb-toast';
            document.body.appendChild(toast);
            }
            toast.textContent = text;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 1500);
        }

        function updateCartBadge(count) {
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

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            fd.append('ajax', '1');

            try {
            const res = await fetch('cart.php', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data?.ok) {
                // toast 1.5s
                const qty = Number(qtyInput.value || 1);
                showToast(`Added ${qty} × <?php echo addslashes($product['title']); ?>`);
                
                updateCartBadge(data.count);
            } else {
                showToast('Something went wrong');
            }
            } catch (err) {
            showToast('Network error');
            }
        });
    })();

  </script>
</body>
</html>
