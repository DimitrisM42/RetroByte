<?php
require __DIR__ . '/config/db.php';

$baseSelect = "
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
";

// Rare Finds
$sqlFeatured = $baseSelect . "
    WHERE LOWER(p.tag) = 'rare'
    ORDER BY p.id DESC
    LIMIT 4
";
$featured = $pdo->query($sqlFeatured)->fetchAll(PDO::FETCH_ASSOC);



// New Arrivals
$sqlNew = $baseSelect . "
    ORDER BY p.id DESC
    LIMIT 8
";
$newArrivals = $pdo->query($sqlNew)->fetchAll(PDO::FETCH_ASSOC);

// On Sale
$sqlSale = $baseSelect . "
    WHERE p.discount_price IS NOT NULL
      AND p.discount_price < p.price
    ORDER BY (p.price - p.discount_price) DESC, p.id DESC
    LIMIT 8
";
$onSale = $pdo->query($sqlSale)->fetchAll(PDO::FETCH_ASSOC);

// Categories
$sqlCategories = "
    SELECT
      COALESCE(NULLIF(TRIM(category), ''), 'Other') AS category,
      COUNT(*) AS total
    FROM products
    GROUP BY COALESCE(NULLIF(TRIM(category), ''), 'Other')
    ORDER BY total DESC, category ASC
";
$categories = $pdo->query($sqlCategories)->fetchAll(PDO::FETCH_ASSOC);

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
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <canvas id="grid"></canvas>

    <section style="height: 0.001em"></section>

    <section id="shop" aria-labelledby="shop-title">
        <div class="container">

            
            <div class="shop-hero" data-reveal="up">
                <div class="shop-hero-text">
                    <h1 id="shop-title" class="shop-hero-title">
                        Curated Picks from the Retro Zone
                    </h1>
                    <p class="shop-hero-subtitle">
                        A rotating selection of consoles, handhelds, and collectibles.
                        Discover the highlights or jump straight into the full collection.
                    </p>
                    <div class="shop-hero-actions" style="margin-top: 24px; display: flex;">
                        <a href="all-products.php" class="pixel-button btn-primary">
                            Explore All Products
                        </a>
                    </div>
                </div>
            </div>

            <!-- Featured Picks -->
            <?php if (!empty($featured)): ?>
            <section class="shop-section" aria-labelledby="featured-title" data-reveal="up">
                <h2 id="featured-title" class="title">Rare Finds</h2>
                <p class="shop-section-subtitle">
                    Handpicked rare and hard-to-find pieces from the RetroByte vault.
                </p>

                <div class="product-grid" data-stagger data-stagger-step="120">
                    <?php foreach ($featured as $pr): ?>
                        <div class="product-card">
                            <div class="product-frame">
                                <?php if (!empty($pr['tag'])): ?>
                                    <span class="badge badge-<?= strtolower($pr['tag']) ?>">
                                        <?= htmlspecialchars($pr['tag']) ?>
                                    </span>
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
            </section>
            <?php endif; ?>

            <!-- New Arrivals -->
            <?php if (!empty($newArrivals)): ?>
            <section class="shop-section" aria-labelledby="new-arrivals-title" data-reveal="up">
                <div class="shop-section-head">
                    <h2 id="new-arrivals-title" class="title">New Arrivals</h2>
                    <p class="shop-section-subtitle">
                        Fresh drops and recent additions to the RetroByte vault.
                    </p>
                </div>

                <div class="product-grid" data-stagger data-stagger-step="120">
                    <?php foreach ($newArrivals as $pr): ?>
                        <div class="product-card">
                            <div class="product-frame">
                                <?php if (!empty($pr['tag'])): ?>
                                    <span class="badge badge-<?= strtolower($pr['tag']) ?>">
                                        <?= htmlspecialchars($pr['tag']) ?>
                                    </span>
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
            </section>
            <?php endif; ?>

            <!-- On Sale -->
            <?php if (!empty($onSale)): ?>
            <section class="shop-section" aria-labelledby="on-sale-title" data-reveal="up">
                <div class="shop-section-head">
                    <h2 id="on-sale-title" class="title">On Sale</h2>
                    <p class="shop-section-subtitle">
                        Special prices on selected classics. Grab them before they’re gone.
                    </p>
                </div>

                <div class="product-grid" data-stagger data-stagger-step="120">
                    <?php foreach ($onSale as $pr): ?>
                        <div class="product-card">
                            <div class="product-frame">
                                <span class="badge badge-sale">SALE</span>

                                <div class="product-thumb" aria-hidden="true">
                                    <img src="<?= htmlspecialchars($pr['image_url']) ?>"
                                         alt="<?= htmlspecialchars($pr['title']) ?>">
                                </div>

                                <h3 class="product-title"><?= htmlspecialchars($pr['title']) ?></h3>

                                <?php if (!empty($pr['short_desc'])): ?>
                                    <p class="product-meta"><?= htmlspecialchars($pr['short_desc']) ?></p>
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
            </section>
            <?php endif; ?>

            
            <?php if (!empty($categories)): ?>
            <section class="shop-section shop-categories" aria-labelledby="categories-title" data-reveal="up">
                <div class="shop-section-head">
                    <h2 id="categories-title" class="title">Browse by Category</h2>
                    <p class="shop-section-subtitle">
                        Jump straight into the type of retro gear you’re hunting for.
                    </p>
                </div>

                <div class="shop-category-grid">
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        $label = $cat['category'] ?? 'Other';
                        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $label), '-'));
                        ?>
                        <a class="shop-category-card"
                           href="all-products.php?category=<?= urlencode($slug) ?>">
                            <span class="shop-category-label">
                                <?= htmlspecialchars($label) ?>
                            </span>
                            <span class="shop-category-count">
                                <?= (int)$cat['total'] ?> items
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="shop-bottom-cta">
                    <a href="all-products.php" class="pixel-button btn-ghost" >
                        View Full Product List
                    </a>
                </div>
            </section>
            <?php endif; ?>

        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
