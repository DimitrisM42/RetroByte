<?php
require __DIR__ . '/config/db.php';

$sql = "
SELECT
  p.id,
  p.title,
  p.short_desc,
  p.price,
  p.discount_price,
  p.tag,
  p.category,
  COALESCE(
    (SELECT url
     FROM product_images pi
     WHERE pi.product_id = p.id
     ORDER BY pi.sort_order ASC, pi.id ASC
     LIMIT 1),
    'assets/images/placeholder.png'
  ) AS image_url
FROM products p
ORDER BY p.id DESC;
";

$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function cat_to_key($cat) {
  $c = strtolower(trim($cat ?? ''));
  if (strpos($c, 'console') !== false) return 'consoles';
  if (strpos($c, 'handheld') !== false) return 'handhelds';
  if (strpos($c, 'game') !== false) return 'games';
  if (strpos($c, 'accessor') !== false) return 'accessories';
  return 'accessories';
}

function tag_class($tag) {
  $t = strtolower(trim($tag ?? ''));
  if ($t === 'hot') return 'badge badge-hot';
  if ($t === 'rare') return 'badge badge-rare';
  if ($t === 'restored' || $t === 'sealed') return 'badge';
  return 'badge';
}


function render_price_html(array $pr): string {
    $price = (float)($pr['price'] ?? 0);
    $hasDiscount = isset($pr['discount_price']) && $pr['discount_price'] !== null && (float)$pr['discount_price'] < $price;

    if ($hasDiscount) {
        $discount = (float)$pr['discount_price'];
        ob_start();
        ?>
        <span class="price price-old">€<?= number_format($price, 2) ?></span>
        <span class="price price-new">€<?= number_format($discount, 2) ?></span>
        <?php
        return trim(ob_get_clean());
    }

    return '<span class="price">€' . number_format($price, 2) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products | RetroByte</title>

    <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/all-products.css">

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

    <section style="height: 0.001em"></section>

    <section id="all-products">
        <div class="container" data-reveal="up" data-stagger data-stagger-step="120">
            <div class="all-products-head">
                <h2 class="title">All Products</h2>
                <p class="all-products-subtitle">
                    Browse the full RetroByte collection by category using the filters below.
                </p>
            </div>

            
            <div class="shop-filters" role="tablist" aria-label="Product categories">
                <button class="pixel-button btn-ghost is-active"
                        data-filter="all" role="tab" aria-selected="true">
                    All
                </button>
                <button class="pixel-button btn-ghost"
                        data-filter="consoles" role="tab" aria-selected="false">
                    Consoles
                </button>
                <button class="pixel-button btn-ghost"
                        data-filter="handhelds" role="tab" aria-selected="false">
                    Handhelds
                </button>
                <button class="pixel-button btn-ghost"
                        data-filter="games" role="tab" aria-selected="false">
                    Games
                </button>
                <button class="pixel-button btn-ghost"
                        data-filter="accessories" role="tab" aria-selected="false">
                    Accessories
                </button>
            </div>

            <div class="product-grid">
                <?php foreach ($products as $pr): ?>
                    <?php $filterKey = cat_to_key($pr['category']); ?>
                    <div class="product-card" data-category="<?= htmlspecialchars($filterKey) ?>">
                        <div class="product-frame">
                            <?php if (!empty($pr['tag'])): ?>
                                <span class="<?= tag_class($pr['tag']) ?>">
                                    <?= htmlspecialchars($pr['tag']) ?>
                                </span>
                            <?php endif; ?>

                            <div class="product-thumb" aria-hidden="true">
                                <img src="<?= htmlspecialchars($pr['image_url']) ?>"
                                     alt="<?= htmlspecialchars($pr['title']) ?>">
                            </div>

                            <h3 class="product-title">
                                <?= htmlspecialchars($pr['title']) ?>
                            </h3>

                            <?php if (!empty($pr['short_desc'])): ?>
                                <p class="product-meta">
                                    <?= htmlspecialchars($pr['short_desc']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="product-bottom">
                                <?= render_price_html($pr) ?>
                                <a href="product.php?id=<?= (int)$pr['id'] ?>"
                                   class="pixel-button btn-primary">
                                   View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.shop-filters [data-filter]');
        const cards = document.querySelectorAll('.product-card[data-category]');

        function normalizeCategory(value) {
          if (!value) return 'all';
          value = value.toLowerCase();
          if (value.includes('console')) return 'consoles';
          if (value.includes('handheld')) return 'handhelds';
          if (value.includes('game')) return 'games';
          if (value.includes('accessor')) return 'accessories';
          if (['all', 'consoles', 'handhelds', 'games', 'accessories'].includes(value)) {
            return value;
          }
          return 'all';
        }

        const params = new URLSearchParams(window.location.search);
        let activeFilter = normalizeCategory(params.get('category') || 'all');

        function applyFilter(filterKey) {
          const key = normalizeCategory(filterKey);

          cards.forEach(card => {
            const cat = card.getAttribute('data-category');
            if (key === 'all' || cat === key) {
              card.style.display = '';
            } else {
              card.style.display = 'none';
            }
          });

          filterButtons.forEach(btn => {
            const btnKey = btn.getAttribute('data-filter');
            const isActive = btnKey === key || (key === 'all' && btnKey === 'all');
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
          });
        }

        filterButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            const key = btn.getAttribute('data-filter') || 'all';

            if (key === 'all') {
              history.replaceState(null, '', window.location.pathname);
            } else {
              const p = new URLSearchParams(window.location.search);
              p.set('category', key);
              history.replaceState(null, '', window.location.pathname + '?' + p.toString());
            }

            applyFilter(key);
          });
        });

        applyFilter(activeFilter);
      });
    </script>
</body>
</html>
