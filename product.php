<?php
require __DIR__ . '/config/db.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;

if (!$id && !$slug) {
  http_response_code(404);
  exit('Product not found');
}

$sql = "SELECT id, title, category, short_desc, description, price, discount_price, year_made, tag
        FROM products
        WHERE " . ($id ? "id = :val" : "REPLACE(LOWER(title),' ','-') = :val") . "
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(
  ':val',
  $id ?: strtolower(str_replace(' ', '-', $slug)),
  $id ? PDO::PARAM_INT : PDO::PARAM_STR
);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  http_response_code(404);
  exit('Product not found');
}

// Images
$imgStmt = $pdo->prepare(
  "SELECT url, alt_text
   FROM product_images
   WHERE product_id = :pid
   ORDER BY sort_order ASC, id ASC"
);
$imgStmt->execute([':pid' => $row['id']]);
$imgRows = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

$images = [];
foreach ($imgRows as $r) {
  $images[] = $r['url'];
}
if (!$images) {
  $images = ['assets/images/placeholder.png'];
}

// Specs
$specStmt = $pdo->prepare(
  "SELECT spec_key, spec_value
   FROM product_specs
   WHERE product_id = :pid
   ORDER BY sort_order ASC, id ASC"
);
$specStmt->execute([':pid' => $row['id']]);
$specRows = $specStmt->fetchAll(PDO::FETCH_ASSOC);

// Meta
$metaParts = [];
if (!empty($row['category']))  {
  $metaParts[] = $row['category'];
}
if (!empty($row['year_made'])) {
  $metaParts[] = 'Year: ' . (int)$row['year_made'];
}
$meta = $metaParts ? implode(' • ', $metaParts) : '';

$product = [
  'id'          => (int)$row['id'],
  'title'       => $row['title'],
  'meta'        => $meta,
  'short'       => $row['short_desc'] ?? '',
  'description' => $row['description'] ?? '',
  'price'       => (float)$row['price'],
  'discount'    => $row['discount_price'],
  'images'      => $images,
  'specs'       => $specRows,
  'tag'         => $row['tag'] ?? null,
];

// --------------------------------------------------
// Related / Recommended
// --------------------------------------------------
$productId = (int)$row['id'];
$category  = $row['category'] ?? null;

$relatedProducts     = [];
$recommendedProducts = [];

// ---------- Related products ----------
if (!empty($category)) {
  $relSql = "
    SELECT
      p.id,
      p.title,
      p.short_desc,
      p.price,
      p.discount_price,
      p.tag,
      COALESCE(
        (
          SELECT url
          FROM product_images pi
          WHERE pi.product_id = p.id
          ORDER BY pi.sort_order ASC, pi.id ASC
          LIMIT 1
        ),
        'assets/images/placeholder.png'
      ) AS image_url
    FROM products p
    WHERE p.category = :category
      AND p.id <> :pid
    ORDER BY p.id DESC
    LIMIT 4
  ";

  $relStmt = $pdo->prepare($relSql);
  $relStmt->execute([
    ':category' => $category,
    ':pid'      => $productId,
  ]);
  $relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fallback αν δεν έχει ίδιας κατηγορίας
if (!$relatedProducts) {
  $relSql = "
    SELECT
      p.id,
      p.title,
      p.short_desc,
      p.price,
      p.discount_price,
      p.tag,
      COALESCE(
        (
          SELECT url
          FROM product_images pi
          WHERE pi.product_id = p.id
          ORDER BY pi.sort_order ASC, pi.id ASC
          LIMIT 1
        ),
        'assets/images/placeholder.png'
      ) AS image_url
    FROM products p
    WHERE p.id <> :pid
    ORDER BY p.id DESC
    LIMIT 4
  ";

  $relStmt = $pdo->prepare($relSql);
  $relStmt->execute([':pid' => $productId]);
  $relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);
}

// ---------- Customers also bought ----------
$recommendedProducts = [];

$pid = (int)$row['id'];

$recSql = "
  SELECT
    p.id,
    p.title,
    p.short_desc,
    p.price,
    p.discount_price,
    p.tag,
    COALESCE(
      (
        SELECT url
        FROM product_images pi
        WHERE pi.product_id = p.id
        ORDER BY pi.sort_order ASC, pi.id ASC
        LIMIT 1
      ),
      'assets/images/placeholder.png'
    ) AS image_url,
    t.total_qty
  FROM (
    SELECT
      oi.product_id,
      SUM(oi.qty) AS total_qty
    FROM order_items oi
    WHERE oi.product_id <> $pid
      AND oi.order_id IN (
        SELECT order_id
        FROM order_items
        WHERE product_id = $pid
      )
    GROUP BY oi.product_id
  ) AS t
  JOIN products p ON p.id = t.product_id
  ORDER BY t.total_qty DESC, p.id DESC
  LIMIT 4
";

try {
    $recStmt = $pdo->query($recSql);
    $recommendedProducts = $recStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recommendedProducts = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($product['title']); ?> • RetroByte</title>

  <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/product.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">
  <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">

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
    <div class="product-start">
      <div class="product-image">
        <img
          id="main-img"
          src="<?php echo htmlspecialchars($product['images'][0]); ?>"
          alt="<?php echo htmlspecialchars($product['title']); ?>"
        >
      </div>

      <div class="thumbs">
        <?php foreach ($product['images'] as $idx => $img): ?>
          <button
            class="thumb <?php echo $idx === 0 ? 'is-active' : ''; ?>"
            data-src="<?php echo htmlspecialchars($img); ?>"
            aria-label="Image <?php echo $idx + 1; ?>"
          >
            <img src="<?php echo htmlspecialchars($img); ?>" alt="">
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="product-info">
      <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>

      <?php if (!empty($product['meta'])): ?>
        <p class="product-meta"><?php echo htmlspecialchars($product['meta']); ?></p>
      <?php endif; ?>

      <?php if (!empty($product['short'])): ?>
        <p class="product-description"><?php echo htmlspecialchars($product['short']); ?></p>
      <?php endif; ?>

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
                <li>
                  <strong><?php echo htmlspecialchars($s['spec_key']); ?></strong>
                  <?php echo htmlspecialchars($s['spec_value']); ?>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>No specifications available.</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <div class="buy-box">
        <div class="price">
          <?php if (!is_null($product['discount']) && $product['discount'] < $product['price']): ?>
            <span class="price price-old">€<?= number_format($product['price'], 2) ?></span>
            <span class="price price-new">€<?= number_format($product['discount'], 2) ?></span>
          <?php else: ?>
            <span class="price">€<?= number_format($product['price'], 2) ?></span>
          <?php endif; ?>
        </div>

        <form action="cart.php" method="post" class="product-pqa" id="add-form">
          <input type="hidden" name="add" value="<?php echo (int)$product['id']; ?>">

          <div class="qty">
            <span>Qty</span>
            <div class="qty-wrapper">
              <button type="button" class="qty-btn minus" aria-label="Decrease quantity">
                <i class="hn hn-minus-solid"></i>
              </button>

              <input
                type="number"
                min="1"
                max="8"
                value="1"
                class="pixel-input"
                id="qty"
                name="qty"
                inputmode="numeric"
                pattern="[0-9]*"
              >

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

  <?php if (!empty($relatedProducts)): ?>
    <section class="related-section">
      <div class="container">
        <h2 class="title">Related Products</h2>
        <div class="product-grid">
          <?php foreach ($relatedProducts as $item): ?>
            <div class="product-card">
              <div class="product-frame">
                <?php if (!empty($item['tag'])): ?>
                  <span class="badge badge-<?php echo strtolower($item['tag']); ?>">
                    <?php echo htmlspecialchars($item['tag']); ?>
                  </span>
                <?php endif; ?>

                <div class="product-thumb" aria-hidden="true">
                  <img
                    src="<?php echo htmlspecialchars($item['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($item['title']); ?>"
                  >
                </div>

                <h3 class="product-title"><?php echo htmlspecialchars($item['title']); ?></h3>

                <?php if (!empty($item['short_desc'])): ?>
                  <p class="product-meta"><?php echo htmlspecialchars($item['short_desc']); ?></p>
                <?php endif; ?>

                <div class="product-bottom">
                  <?php
                    $price    = (float)$item['price'];
                    $discount = isset($item['discount_price']) ? (float)$item['discount_price'] : null;
                    $hasDiscount = $discount !== null && $discount > 0 && $discount < $price;
                  ?>
                  <?php if ($hasDiscount): ?>
                    <span class="price price-old">€<?php echo number_format($price, 2); ?></span>
                    <span class="price price-new">€<?php echo number_format($discount, 2); ?></span>
                  <?php else: ?>
                    <span class="price">€<?php echo number_format($price, 2); ?></span>
                  <?php endif; ?>
                  <a
                    href="product.php?id=<?php echo (int)$item['id']; ?>"
                    class="pixel-button btn-primary"
                  >
                    View
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($recommendedProducts)): ?>
    <section class="related-section">
      <div class="container">
        <h2 class="title">Customers Also Bought</h2>
        <div class="product-grid">
          <?php foreach ($recommendedProducts as $item): ?>
            <div class="product-card">
              <div class="product-frame">
                <?php if (!empty($item['tag'])): ?>
                  <span class="badge badge-<?php echo strtolower($item['tag']); ?>">
                    <?php echo htmlspecialchars($item['tag']); ?>
                  </span>
                <?php endif; ?>

                <div class="product-thumb" aria-hidden="true">
                  <img
                    src="<?php echo htmlspecialchars($item['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($item['title']); ?>"
                  >
                </div>

                <h3 class="product-title"><?php echo htmlspecialchars($item['title']); ?></h3>

                <?php if (!empty($item['short_desc'])): ?>
                  <p class="product-meta"><?php echo htmlspecialchars($item['short_desc']); ?></p>
                <?php endif; ?>

                <div class="product-bottom">
                  <?php
                    $price    = (float)$item['price'];
                    $discount = isset($item['discount_price']) ? (float)$item['discount_price'] : null;
                    $hasDiscount = $discount !== null && $discount > 0 && $discount < $price;
                  ?>
                  <?php if ($hasDiscount): ?>
                    <span class="price price-old">€<?php echo number_format($price, 2); ?></span>
                    <span class="price price-new">€<?php echo number_format($discount, 2); ?></span>
                  <?php else: ?>
                    <span class="price">€<?php echo number_format($price, 2); ?></span>
                  <?php endif; ?>
                  <a
                    href="product.php?id=<?php echo (int)$item['id']; ?>"
                    class="pixel-button btn-primary"
                  >
                    View
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section style="height: 30px"></section>
  <?php include 'includes/footer.php'; ?>

  <script>
    (function () {
      const main = document.getElementById('main-img');
      const thumbs = document.querySelectorAll('.thumb');

      if (main && thumbs.length) {
        thumbs.forEach(btn => {
          btn.addEventListener('click', () => {
            thumbs.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            main.src = btn.dataset.src;
          });
        });
      }
    })();

    (function () {
      const tabs = document.querySelectorAll('.tab');
      const panels = document.querySelectorAll('.tab-panel');

      if (!tabs.length || !panels.length) return;

      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('is-active'));
          panels.forEach(p => p.classList.remove('is-active'));

          tab.classList.add('is-active');
          const panel = document.getElementById(tab.dataset.tab);
          if (panel) panel.classList.add('is-active');
        });
      });
    })();

    (function () {
      const form = document.getElementById('add-form');
      const qtyInput = document.getElementById('qty');
      const plus = document.querySelector('.qty-btn.plus');
      const minus = document.querySelector('.qty-btn.minus');
      const cartBadge = document.getElementById('cart-count');

      if (!form || !qtyInput) return;

      const clamp = (val, min, max) => Math.max(min, Math.min(max, val));

      qtyInput.addEventListener('input', () => {
        const min = parseInt(qtyInput.min) || 1;
        const max = parseInt(qtyInput.max) || 99;
        let v = parseInt(String(qtyInput.value).replace(/\D/g, '')) || min;
        qtyInput.value = clamp(v, min, max);
      });

      if (plus) {
        plus.addEventListener('click', () => {
          const max = parseInt(qtyInput.max) || 99;
          let v = parseInt(qtyInput.value) || 1;
          if (v < max) qtyInput.value = v + 1;
        });
      }

      if (minus) {
        minus.addEventListener('click', () => {
          const min = parseInt(qtyInput.min) || 1;
          let v = parseInt(qtyInput.value) || 1;
          if (v > min) qtyInput.value = v - 1;
        });
      }

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
          if (data && data.ok) {
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
