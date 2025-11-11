<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroByte</title>
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
                <i class="hn hn-check-solid"></i>
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
            <!-- Card 1 -->
            <div class="product-card">
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
            <div class="product-card">
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
            <div class="product-card">
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
            <div class="product-card">
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

            <div class="featured-actions">
            <a class="pixel-button btn-ghost" href="shop.php">View All Products</a>
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