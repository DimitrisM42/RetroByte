<?php
require __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';


$sql = "
SELECT
  o.id,
  o.customer_name,
  o.email,
  o.city,
  o.total_price,
  o.status,
  o.created_at,
  COUNT(oi.id) AS items_count
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id
ORDER BY o.id DESC
";
$orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$activePage = 'orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroByte CMS - Orders</title>

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
    <script src="../js/app.js" defer></script>
</head>
<body>
<canvas id="grid"></canvas>

<section id="cms">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-container">
        <div id="orders">
            <div class="products-header">
                <h2>Orders</h2>
            </div>

            <div class="table-wrapper">
                <table class="product-table order-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>City</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th style="width: 140px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($orders)): ?>
                        <tr class="product-row order-row">
                            <td colspan="8">No orders yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                              $code = 'RB-' . str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT);
                              $date = $order['created_at'];
                            ?>
                            <tr class="product-row order-row">
                                <td><?php echo htmlspecialchars($code); ?></td>
                                <td><?php echo htmlspecialchars($date); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['city']); ?></td>
                                <td><?php echo (int)$order['items_count']; ?></td>
                                <td>€<?php echo number_format($order['total_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td style="text-align:right;">
                                    <a href="view-order.php?id=<?php echo (int)$order['id']; ?>"
                                       class="pixel-button btn-ghost small-btn">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
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
