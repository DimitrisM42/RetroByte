<?php
require __DIR__ . '/config/db.php';

function category_key(?string $cat): string {
    $c = strtolower(trim($cat ?? ''));

    if (strpos($c, 'console') !== false) {
        return 'consoles';
    }
    if (strpos($c, 'handheld') !== false) {
        return 'handhelds';
    }
    if (strpos($c, 'game') !== false) {
        return 'games';
    }
    if (strpos($c, 'accessor') !== false) {
        return 'accessories';
    }

    return 'other';
}

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
FROM products p;
";

$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$grouped = [
    'consoles'     => [],
    'handhelds'    => [],
    'games'        => [],
    'accessories'  => [],
];

foreach ($rows as $row) {
    $key = category_key($row['category'] ?? null);

    if (isset($grouped[$key])) {
        $grouped[$key][] = $row;
    }
}

$order    = ['consoles', 'handhelds', 'games', 'accessories'];
$featured = [];

foreach ($order as $key) {
    if (!empty($grouped[$key])) {
        $randomIndex   = array_rand($grouped[$key]);
        $featured[]    = $grouped[$key][$randomIndex];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroByte</title>

    <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">

    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">


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

    <section style="height: 200px"></section>


    <section id="hero">
        <div class="container">
            <h1 class="headline" data-reveal="right">The Past Never Looked So Good</h1>
            <p class="subline " data-reveal="right">We bring classic gaming and vintage tech back to life with a modern touch and a love for nostalgia.</p>
            <a href="shop.php" class="pixel-button btn-primary" data-reveal="up">Start Your Retro Journey</a>
        </div>
    </section>


    <section style="height: 320px"></section>


    <section id="value">
        <div class="container">
            <h2 class="title" data-reveal="up"> Our Value </h2>

            <div class="value-grid" data-reveal="up" data-stagger data-stagger-step="120">
                <div class="value-card">
                    <i class="hn hn-badge-check-solid"></i>
                    <h4>Authentic & Verified Retro Gear</h4>
                    <p>Every console and accessory is verified for top quality.</p>
                </div>

                <div class="value-card">
                <i class="hn hn-star-solid"></i>
                <h4>Collector’s Editions & Rare Finds</h4>
                <p>Unique items and limited releases for true enthusiasts.</p>
                </div>

                <div class="value-card">
                <i class="hn hn-bolt-solid"></i>
                <h4>Fast Shipping & Secure Packaging</h4>
                <p>Your retro gear is delivered quickly and safely.</p>
                </div>

                <div class="value-card">
                <i class="hn hn-heart-solid"></i>
                <h4>Built by Gamers, for Gamers</h4>
                <p>We share your passion for nostalgia.</p>
                </div>
            </div>        

        </div>
    </section>


    <section class="divider"></section>


    <section id="featured">
        <div class="container">
            <div class="featured-head">
            <h2 class="title" data-reveal="up"> Featured Classics </h2>
            </div>

            <div class="product-grid" data-reveal="up" data-stagger data-stagger-step="120">
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

                    <?php if(!empty($pr['short_desc'])): ?>
                        <p class="product-meta"><?= htmlspecialchars($pr['short_desc']) ?></p>
                    <?php endif; ?>

                    <div class="product-bottom">
                        <?php if (!is_null($pr['discount_price']) && $pr['discount_price'] < $pr['price']): ?>
                            <span class="price price-old">€<?= number_format($pr['price'], 2) ?></span>
                            <span class="price price-new">€<?= number_format($pr['discount_price'], 2) ?></span>
                        <?php else: ?>
                            <span class="price">€<?= number_format($pr['price'], 2) ?></span>
                        <?php endif; ?>
                        <a href="product.php?id=<?= (int)$pr['id'] ?>" class="pixel-button btn-primary">View</a>
                    </div>

                </div>
                </div>
            <?php endforeach; ?>
            </div>

            <div class="featured-actions">
            <a class="pixel-button btn-ghost" href="all-products.php">View All Products</a>
            </div>
        </div>
    </section>


    
    <section class="divider"></section>


    <section id="reviews" aria-labelledby="reviews-title">
        <div class="container">
            <h2 id="reviews-title" class="title" data-reveal="up">Community Reviews</h2>

            <p class="reviews-intro" data-reveal="up">
            Loved by collectors, trusted by gamers. Here’s what our community says about their RetroByte experience.
            </p>

            <div class="reviews-grid" data-reveal="up" data-stagger data-stagger-step="120">
                <!-- Review 1 -->
                <div class="review-card">
                <div class="review-top">
                    <div class="avatar" aria-hidden="true">AR</div>
                    <div class="meta">
                    <h3 class="name">Alex R.</h3>
                    <span class="loc">Germany</span>
                    </div>
                    <div class="rating" aria-label="Rating: 5 out of 5">★★★★★</div>
                </div>
                <p class="quote">
                    “Got my Game Boy restored and it works like it’s 1990 again! Perfect screen, clean buttons, and that classic click sound I missed.”
                </p>
                </div>

                <!-- Review 2 -->
                <div class="review-card">
                    <div class="review-top">
                    <div class="avatar">MT</div>
                    <div class="meta">
                        <h3 class="name">Maria T.</h3>
                        <span class="loc">Greece</span>
                    </div>
                    <div class="rating" aria-label="Rating: 5 out of 5">★★★★★</div>
                    </div>
                    <p class="quote">
                    “Finally found my childhood console in perfect condition. Fast delivery, great packaging, and that nostalgic spark!”
                    </p>
                </div>


                <!-- Review 3 -->
                <div class="review-card">
                <div class="review-top">
                    <div class="avatar">KM</div>
                    <div class="meta">
                    <h3 class="name">Kenji M.</h3>
                    <span class="loc">Japan</span>
                    </div>
                    <div class="rating" aria-label="Rating: 5 out of 5">★★★★★</div>
                </div>
                <p class="quote">
                    “RetroByte isn’t just a store. It’s a museum for gamers. Every item feels authentic and the packaging was a total throwback!”
                </p>
                </div>

                <!-- Review 4 -->
                <div class="review-card">
                <div class="review-top">
                    <div class="avatar">DS</div>
                    <div class="meta">
                    <h3 class="name">Diego S.</h3>
                    <span class="loc">Spain</span>
                    </div>
                    <div class="rating" aria-label="Rating: 4 out of 5">★★★★☆</div>
                </div>
                <p class="quote">
                    “SNES works perfectly! Only thing I’d change is faster tracking updates, but overall it’s a top-tier retro shop.”
                </p>
                </div>


            </div>
        </div>
    </section>

    <section class="divider"></section>

    <section id="final-cta">
        <div class="container" data-reveal="up" data-stagger data-stagger-step="120">
            <h2 class="title">Press Start to Enter the Retro Zone</h2>
            <p class="subtitle">Your next classic is waiting.</p>

            <div class="cta-buttons">
                <a href="shop.php" class="pixel-button btn-primary">Explore the Collection</a>

            </div>

            <p class="trust-line">
            Worldwide shipping • Verified gear • Safe packaging
            </p>
        </div>
    </section>








    <?php include 'includes/footer.php'; ?>

</body>
</html>