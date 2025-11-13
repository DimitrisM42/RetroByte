<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>


<nav class="nav">
  <div class="nav-inner">
    <a href="index.php" class="logo">
      <img src="assets/images/RetroByteLogo.png" alt="RetroByte">
      <span>RetroByte</span>
    </a>

    <button id="nav-toggle" aria-expanded="false" aria-label="Menu">
      <i class="hn hn-bars-solid"></i>
    </button>

    <ul class="nav-list">
      <li><a class="nav-item" href="index.php"><i class="hn hn-home-solid"></i> Home</a></li>
      <li><a class="nav-item" href="shop.php"><i class="hn hn-save-solid"></i> Shop</a></li>
      <li><a class="nav-item" href="about.php"><i class="hn hn-user-solid"></i> About</a></li>
      <li><a class="nav-item" href="contact.php"><i class="hn hn-envelope-solid"></i> Contact</a></li>
      <br>
      <li>
        <a id="cart" class="nav-item" href="cart.php">
          <i id="cart-icon" class="hn hn-shopping-cart-solid"></i>
          <span id="cart-count" class="cart-badge<?php
            $n = array_sum($_SESSION['cart'] ?? []);
            echo $n > 0 ? '' : ' is-hidden';?>">
            <?php echo $n; ?></span>
        </a>
      </li>

    </ul>
  </div>
</nav>


<script>

  window.addEventListener("scroll", function () {
  document
    .querySelector(".nav")
    .classList.toggle("scrolled-nav", window.scrollY > 0);
  });

  const nav = document.querySelector(".nav");
  const btn = document.getElementById("nav-toggle");

  btn.addEventListener("click", () => {
    const open = nav.classList.toggle("open");
    btn.setAttribute("aria-expanded", open);
  });

</script>
  


