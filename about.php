<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>

    <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/images/RetroByteLogo.png">
    
    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/about.css">
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

    <section style="height: 20px; margin-top: 60px"></section>

<section id="about" aria-labelledby="about-title" data-reveal>
  <div class="container">
    <h2 class="title" id="about-title">About RetroByte</h2>

    <div class="about-top" data-reveal data-reveal-delay="100">
      <p>
        At RetroByte, we’re more than just a retro store. We’re curators of gaming history.
        What began as a small circle of collectors has grown into a digital hub for those
        who believe the classics still matter.
      </p>
    </div>

    <div class="about-content">
      <div class="about-grid" data-stagger data-stagger-step="120">
        <div class="about-card" data-reveal>
          <i class="hn hn-bolt-solid" aria-hidden="true"></i>
          <h4>A Shared Passion</h4>
          <p>We started as friends restoring our childhood consoles, driven by one question: What if we could make these memories playable again?</p>
        </div>

        <div class="about-card" data-reveal data-reveal-delay="80">
          <i class="hn hn-message-solid " aria-hidden="true"></i>
          <h4>From Shelf to Screen</h4>
          <p>Every collection we build celebrates the beauty of gaming’s early days pixel by pixel.</p>
        </div>

        <div class="about-card" data-reveal data-reveal-delay="160">
          <i class="hn hn-bookmark-solid  " aria-hidden="true"></i>
          <h4>A Space for Collectors</h4>
          <p>RetroByte is where enthusiasts meet, share, and rediscover forgotten icons of tech culture.</p>
        </div>

        <div class="about-card" data-reveal data-reveal-delay="240">
          <i class="hn hn-globe-solid" aria-hidden="true"></i>
          <h4>Preserving the Future of the Past</h4>
          <p>We’re here to keep retro gaming alive as an experience that still inspires today.</p>
        </div>
      </div>

      <figure class="about-image" data-reveal data-reveal-delay="140">
        <img src="assets/images/RetroByteLogo.png" alt="RetroByte logo mark" loading="lazy">
      </figure>
    </div>
  </div>
</section>


    <?php include 'includes/footer.php'; ?>

    
</body>
</html>



