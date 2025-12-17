<?php
require __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

function euro($n) {
  return '€' . number_format((float)$n, 2, '.', ',');
}

$activePage = 'orders';


$statusUpdated = false;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : $id;

    if ($id > 0) {
        $action = $_POST['action'] ?? null;

        if ($action === 'update_status') {
            $newStatus = $_POST['status'] ?? 'pending';
            $allowed   = ['pending', 'completed', 'canceled'];
            if (!in_array($newStatus, $allowed, true)) {
                $newStatus = 'pending';
            }

            $stmt = $pdo->prepare("UPDATE orders SET status = :st WHERE id = :id");
            $stmt->execute([
              ':st' => $newStatus,
              ':id' => $id,
            ]);

            $statusUpdated = true;

        } elseif ($action === 'delete_order') {
            try {
                $pdo->beginTransaction();

                $stmtItems = $pdo->prepare("DELETE FROM order_items WHERE order_id = :id");
                $stmtItems->execute([':id' => $id]);

                $stmtOrder = $pdo->prepare("DELETE FROM orders WHERE id = :id");
                $stmtOrder->execute([':id' => $id]);

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            }

            header('Location: orders.php');
            exit;
        }
    }
}


$order = null;
$items = [];
$subtotal = 0.0;
$shipping = 0.0;

if ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT
          id,
          customer_name,
          email,
          phone,
          address,
          city,
          postal_code,
          notes,
          total_price,
          status,
          created_at
        FROM orders
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $stmt2 = $pdo->prepare("
          SELECT
            oi.product_id,
            oi.qty,
            oi.unit_price,
            oi.line_total,
            p.title
          FROM order_items oi
          LEFT JOIN products p ON p.id = oi.product_id
          WHERE oi.order_id = :id
        ");
        $stmt2->execute([':id' => $id]);
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $it) {
            $subtotal += (float)$it['line_total'];
        }

        $shipping = max(0, (float)$order['total_price'] - $subtotal);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details • RetroByte CMS</title>

    <link rel="icon" type="image/png" href="../assets/images/RetroByteLogo.png">

    <!-- css -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/cms.css">
    <link rel="stylesheet" href="css/view-order.css">
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
        <?php if (!$order): ?>
            <div class="order-page">
                <div class="order-topbar">
                    <h1 class="order-title">Order not found</h1>
                    <a href="orders.php" class="pixel-button btn-ghost order-back-btn">
                        ← Back to Orders
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php
              $code = 'RB-' . str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT);
              $date = $order['created_at'];
            ?>
            <div class="order-page">
                <div class="order-topbar">
                    <h1 class="order-title">ORDER <?php echo htmlspecialchars($code); ?></h1>
                    <a href="orders.php" class="pixel-button btn-ghost order-back-btn">
                        ← Back to Orders
                    </a>
                </div>

                <div class="order-sections">
                    <!-- Cust -->
                    <section class="order-section order-section-customer">
                        <div class="order-panel">
                            <h2 class="order-section-title">Customer</h2>
                            <dl class="order-meta">
                                <div class="order-meta-row">
                                    <dt>Name</dt>
                                    <dd><?php echo htmlspecialchars($order['customer_name']); ?></dd>
                                </div>
                                <div class="order-meta-row">
                                    <dt>Email</dt>
                                    <dd><?php echo htmlspecialchars($order['email']); ?></dd>
                                </div>
                                <div class="order-meta-row">
                                    <dt>Phone</dt>
                                    <dd><?php echo htmlspecialchars($order['phone'] ?: '—'); ?></dd>
                                </div>
                                <div class="order-meta-row">
                                    <dt>Address</dt>
                                    <dd>
                                        <?php echo htmlspecialchars($order['address']); ?>,
                                        <?php echo htmlspecialchars($order['city']); ?>,
                                        <?php echo htmlspecialchars($order['postal_code']); ?>
                                    </dd>
                                </div>
                                <div class="order-meta-row">
                                    <dt>Created at</dt>
                                    <dd><?php echo htmlspecialchars($date); ?></dd>
                                </div>
                                <div class="order-meta-row">
                                    <dt>Status</dt>
                                    <dd><?php echo htmlspecialchars($order['status']); ?></dd>
                                </div>
                                <?php if (!empty($order['notes'])): ?>
                                <div class="order-meta-row order-notes-row">
                                    <dt>Notes</dt>
                                    <dd><?php echo nl2br(htmlspecialchars($order['notes'])); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </section>

                    <!-- Summary -->
                    <section class="order-section order-section-summary">
                        <div class="order-panel">
                            <h2 class="order-section-title">Summary</h2>
                            <dl class="order-meta order-summary-meta">
                                <div class="order-meta-row">
                                    <dt>Subtotal</dt>
                                    <dd><?php echo euro($subtotal); ?></dd>
                                </div>
                                <div class="order-meta-row">
                                    <dt>Shipping</dt>
                                    <dd><?php echo euro($shipping); ?></dd>
                                </div>
                                <div class="order-meta-row order-total-row">
                                    <dt>Total</dt>
                                    <dd><?php echo euro($order['total_price']); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </section>
                </div>

                <!-- Poducts -->
                <section class="order-products-section">

                    <div class="table-wrapper order-items-wrapper">
                        <table class="product-table order-items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th style="text-align:right;">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($items)): ?>
                                <tr class="product-row">
                                    <td colspan="4">No items in this order.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $it): ?>
                                    <tr class="product-row">
                                        <td>
                                            <?php if (!empty($it['product_id'])): ?>
                                              <a href="../product.php?id=<?php echo (int)$it['product_id']; ?>" target="_blank">
                                                <?php echo htmlspecialchars($it['title'] ?: ('#'.$it['product_id'])); ?>
                                              </a>
                                            <?php else: ?>
                                              <?php echo htmlspecialchars($it['title'] ?: 'Unknown product'); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo (int)$it['qty']; ?></td>
                                        <td><?php echo euro($it['unit_price']); ?></td>
                                        <td style="text-align:right;"><?php echo euro($it['line_total']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Actions -->
                <section class="order-actions">
                    <form method="POST" class="order-status-form">
                        <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                        <input type="hidden" name="action" value="update_status">

                        <span class="order-status-label">Status</span>
                        <select name="status" class="order-status-select">
                            <option value="pending"   <?php echo $order['status'] === 'pending'   ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="canceled"  <?php echo $order['status'] === 'canceled'  ? 'selected' : ''; ?>>Canceled</option>
                        </select>

                        <button type="submit" class="pixel-button btn-primary small-btn">
                            Update Status
                        </button>
                    </form>

                    <form method="POST"
                          class="order-delete-form"
                          onsubmit="return confirm('Delete this order? This action cannot be undone.');">
                        <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                        <input type="hidden" name="action" value="delete_order">
                        <button type="submit" class="icon-btn danger-btn pixel-button btn-ghost">
                            <i class="hn hn-trash-solid" aria-hidden="true"></i>
                            <span>Delete Order</span>
                        </button>
                    </form>
                </section>

            </div>
        <?php endif; ?>
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
