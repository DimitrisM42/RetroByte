<?php
if (!isset($activePage)) {
  $activePage = '';
}

function is_active($activePage, array $pages): string {
  return in_array($activePage, $pages, true) ? 'active' : '';
}
?>

<div class="sidebar-container">
  <div class="sidebar-top">
    <img src="../assets/images/RetroByteLogo.png" alt="RetroByte Logo">
    <h1>RetroByte CMS</h1>
  </div>

  <nav class="menu" aria-label="CMS navigation">
    <a href="dashboard.php" class="menu-button <?= is_active($activePage, ['dashboard']); ?>">
      <i class="icon hn hn-home-solid"></i>
      <span class="menu-label">Overview</span>
    </a>

    <a href="products.php" class="menu-button <?= is_active($activePage, ['products','add-product','edit-product']); ?>">
      <i class="icon hn hn-save-solid"></i>
      <span class="menu-label">Products</span>
    </a>

    <a href="orders.php" class="menu-button <?= is_active($activePage, ['orders','view-order']); ?>">
      <i class="icon hn hn-user-solid"></i>
      <span class="menu-label">Orders</span>
    </a>

    <a href="logout.php" class="menu-button">
      <i class="icon hn hn-lock-alt-solid" style="color:red;"></i>
      <span class="menu-label" style="color:red;">Logout</span>
    </a>
  </nav>

  <div class="sidebar-bottom">
    <button type="button" onclick="toggleTheme()" class="menu-button pixel-button" style="margin-left:8px;">
      <i class="icon hn hn-moon-solid"></i>
      <span class="menu-label">Theme</span>
    </button>
  </div>
</div>
