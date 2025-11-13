<?php

require __DIR__ . '/config/db.php';


$sql = "
SELECT
  p.id,
  p.title,
  p.short_desc,
  p.price,
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
ORDER BY p.id DESC
LIMIT 4;
";
$products = $pdo->query($sql)->fetchAll();


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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/shop.css">


    <!-- icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">

    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">
   
   
    <!-- js -->
    <script src="assets/js/app.js" defer></script>
    <script src="assets/js/shop.js" defer></script>
    

</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <canvas id="grid"></canvas>

    <section style="height: 0.001em"></section>


    <section id="shop">
        <div class="container" data-reveal="up" data-stagger data-stagger-step="120">
            <div class="featured-head">
            <h2 class="title">Shop</h2>
            </div>

            <!-- Category Filter Bar -->
            <div class="shop-filters" role="tablist" aria-label="Product categories">
            <button class="pixel-button btn-ghost is-active" data-filter="all" role="tab" aria-selected="true">All</button>
            <button class="pixel-button btn-ghost" data-filter="consoles" role="tab" aria-selected="false">Consoles</button>
            <button class="pixel-button btn-ghost" data-filter="handhelds" role="tab" aria-selected="false">Handhelds</button>
            <button class="pixel-button btn-ghost" data-filter="games" role="tab" aria-selected="false">Games</button>
            <button class="pixel-button btn-ghost" data-filter="accessories" role="tab" aria-selected="false">Accessories</button>
            </div>

            <div class="product-grid">
            <?php foreach ($products as $pr): ?>
                <?php $filterKey = cat_to_key($pr['category']); ?>
                <div class="product-card" data-category="<?= htmlspecialchars($filterKey) ?>">
                <div class="product-frame">

                    <?php if (!empty($pr['tag'])): ?>
                    <span class="<?= tag_class($pr['tag']) ?>"><?= htmlspecialchars($pr['tag']) ?></span>
                    <?php endif; ?>

                    <div class="product-thumb" aria-hidden="true">
                    <img src="<?= htmlspecialchars($pr['image_url']) ?>"
                        alt="<?= htmlspecialchars($pr['title']) ?>">
                    </div>

                    <h3 class="product-title"><?= htmlspecialchars($pr['title']) ?></h3>

                    <?php if (!empty($pr['short_desc'])): ?>
                    <p class="product-meta"><?= htmlspecialchars($pr['short_desc']) ?></p>
                    <?php endif; ?>

                    <div class="product-bottom">
                    <span class="price">€<?= number_format($pr['price'], 0) ?></span>
                    <a href="product.php?id=<?= (int)$pr['id'] ?>" class="pixel-button btn-primary">View</a>
                    </div>

                </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </section>




    <?php include 'includes/footer.php'; ?>
    
</body>
</html>