<?php
require __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$activePage = 'products';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    if ($id > 0) {
        try {
            $pdo->beginTransaction();

            
            $stmtSpecs = $pdo->prepare("DELETE FROM product_specs WHERE product_id = :id");
            $stmtSpecs->execute([':id' => $id]);

            
            $stmtImgs = $pdo->prepare("DELETE FROM product_images WHERE product_id = :id");
            $stmtImgs->execute([':id' => $id]);

            
            $stmtProd = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmtProd->execute([':id' => $id]);

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
        }
    }

    
    header('Location: products.php');
    exit;
}


$sql = "
SELECT
  p.id,
  p.title,
  p.price,
  p.discount_price,
  COALESCE(
    (
      SELECT url
      FROM product_images pi
      WHERE pi.product_id = p.id
      ORDER BY pi.sort_order ASC, pi.id ASC
      LIMIT 1
    ),
    'assets/images/placeholder.png'
  ) AS image_url
FROM products p
ORDER BY p.id DESC
";
$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroByte CMS - Products</title>

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
        <div id="products">
            <div class="products-header">
                <h2>Products</h2>
                <a href="add-product.php" class="pixel-button btn-primary">+ Add Product</a>
            </div>

            <div class="table-wrapper">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th style="width: 190px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr class="product-row">
                            <td colspan="4">No products yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            $imgSrc = $product['image_url'] ?? '';

                            if ($imgSrc && strpos($imgSrc, 'assets/') === 0) {
                                $imgSrc = '../' . $imgSrc;
                            }

                            $basePrice     = (float)$product['price'];
                            $discountPrice = isset($product['discount_price']) ? $product['discount_price'] : null;
                            $hasDiscount   = $discountPrice !== null && $discountPrice !== '' && (float)$discountPrice < $basePrice;
                            ?>
                            <tr class="product-row">
                                <td>
                                    <div class="product-thumb">
                                        <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                             alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                <td>
                                    <?php if ($hasDiscount): ?>
                                        <span class="price price-old">
                                            €<?php echo number_format($basePrice, 2); ?>
                                        </span>
                                        <span class="price price-new">
                                            €<?php echo number_format((float)$discountPrice, 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="price">
                                            €<?php echo number_format($basePrice, 2); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <a href="edit-product.php?id=<?php echo (int)$product['id']; ?>"
                                       class="pixel-button btn-ghost small-btn" style="display:inline-flex;">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          style="display:inline-block; margin-left:6px;"
                                          onsubmit="return confirm('Delete this product? This action cannot be undone.');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$product['id']; ?>">
                                        <button type="submit"
                                                class="pixel-button btn-ghost small-btn danger-btn"
                                                aria-label="Delete product">
                                            <i class="hn hn-trash-solid" aria-hidden="true"></i>
                                        </button>
                                    </form>
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
