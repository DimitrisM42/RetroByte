<?php

declare(strict_types=1);
require __DIR__ . '/auth.php';

require_once __DIR__ . '/../config/db.php';


$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders   = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();

$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroByte CMS - Dashboard</title>

    <link rel="icon" type="image/png" href="../assets/images/RetroByteLogo.png">

    <!-- styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/cms.css">
    <link rel="stylesheet" href="css/sidebar.css">

    <!-- icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">

    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">

    <!-- scripts -->
    <script src="../assets/js/app.js" defer></script>
</head>
<body>
<canvas id="grid"></canvas>

<section id="cms">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-container">
        <div id="overview">
            <h2>Overview</h2>

            <div class="overview-container">
                <div class="card-container">
                    <div class="card">
                        <h3>Total Products</h3>
                        <span><?php echo $totalProducts; ?></span>
                    </div>
                    <div class="card">
                        <h3>Total Orders</h3>
                        <span><?php echo $totalOrders; ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
function toggleTheme() {
    const r = document.documentElement;
    r.dataset.theme = r.dataset.theme === 'dark' ? '' : 'dark';
    if (r.dataset.theme) localStorage.setItem('theme','dark');
    else localStorage.removeItem('theme');
}
(function () {
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.dataset.theme = 'dark';
    }
})();
</script>
</body>
</html>
