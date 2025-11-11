<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

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
            <!-- Card 1 -->
            <div class="product-card" data-category="consoles">
                <div class="product-frame">
                <span class="badge badge-hot">HOT</span>
                <div class="product-thumb" aria-hidden="true">
                    <img src="assets/images/index/SNESClassic.png" alt="SNES Classic Edition">
                </div>
                <h3 class="product-title">Super Nintendo Classic Edition</h3>
                <p class="product-meta">Refurbished • Controller x2</p>
                <div class="product-bottom">
                    <span class="price">€149</span>
                    <button class="pixel-button btn-primary">View</button>
                </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="product-card" data-category="handhelds">
                <div class="product-frame">
                <span class="badge">RESTORED</span>
                <div class="product-thumb">
                    <img src="assets/images/index/GameBoydmg01.png" alt="Game Boy DMG-01">
                </div>
                <h3 class="product-title">Game Boy DMG-01</h3>
                <p class="product-meta">IPS mod • New shell</p>
                <div class="product-bottom">
                    <span class="price">€189</span>
                    <button class="pixel-button btn-primary">View</button>
                </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="product-card" data-category="consoles">
                <div class="product-frame">
                <span class="badge badge-rare">RARE</span>
                <div class="product-thumb">
                    <img src="assets/images/index/ps1.png" alt="PS1">
                </div>
                <h3 class="product-title">PlayStation (PS1)</h3>
                <p class="product-meta">Tested • AV/SCART</p>
                <div class="product-bottom">
                    <span class="price">€129</span>
                    <button class="pixel-button btn-primary">View</button>
                </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="product-card" data-category="games">
                <div class="product-frame">
                <span class="badge">SEALED</span>
                <div class="product-thumb">
                    <img src="assets/images/index/NES_Cartridge.png" alt="NES CARTRIDGE">
                </div>
                <h3 class="product-title">NES Cartridge (Assorted)</h3>
                <p class="product-meta">Cleaned • Tested</p>
                <div class="product-bottom">
                    <span class="price">From €29</span>
                    <button class="pixel-button btn-primary">View</button>
                </div>
                </div>
            </div>
            </div>

        </div>
    </section>




    <?php include 'includes/footer.php'; ?>
    
</body>
</html>